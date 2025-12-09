<?php

namespace App\Http\Controllers\User;

use App\Models\Address;
use App\Models\Province;
use App\Models\Ward;
use App\Models\Order;
use App\Models\Review;
use App\Models\OrderItem;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\User\AddressService;
use App\Services\User\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Requests\User\Address\StoreRequest;
use App\Http\Requests\User\Address\UpdateRequest;
use App\Http\Requests\User\Address\DestroyRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use Carbon\Carbon;
use Throwable;

class UserAddressController extends Controller
{
  protected AddressService $addressService;
  protected ProfileService $profileService;

  public function __construct(AddressService $addressService, ProfileService $profileService)
  {
    $this->addressService = $addressService;
    $this->profileService = $profileService;
  }

  public function index(Request $request): View|RedirectResponse
  {
    try {
      $user = Auth::user();
      if ($user === null) {
        return redirect()->route('login');
      }

      $recentOrders = Order::where('user_id', $user->id)
        ->latest('placed_at')
        ->limit(5)
        ->get();

      $addresses = $this->addressService->getList();
      $provinces = Province::orderBy('name')->get();

      // ================== THỐNG KÊ TỔNG QUAN ==================
      $ordersBase = Order::where('user_id', $user->id);

      $totalOrders = (clone $ordersBase)->count();

      $deliveredOrders = (clone $ordersBase)
        ->whereIn('status', ['DELIVERED', 'COMPLETED'])
        ->count();

      $cancelledOrders = (clone $ordersBase)
        ->whereIn('status', ['CANCELLED', 'RETURNED', 'DELIVERY_FAILED'])
        ->count();

      $totalSpentVnd = (int) (clone $ordersBase)->sum('grand_total_vnd');

      $avgOrderValueVnd = $totalOrders > 0
        ? (int) round($totalSpentVnd / $totalOrders)
        : 0;

      $recentFrom = now()->subDays(30);

      $recentOrdersQuery = (clone $ordersBase)->where('placed_at', '>=', $recentFrom);

      $recentOrdersCount = (clone $recentOrdersQuery)->count();
      $recentSpentVnd    = (int) (clone $recentOrdersQuery)->sum('grand_total_vnd');

      // Đếm số đơn theo status
      $rawStatusCounts = (clone $ordersBase)
        ->selectRaw('UPPER(status) as status, COUNT(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status')
        ->toArray();

      $statusCounts = [];
      foreach ($rawStatusCounts as $status => $total) {
        $statusCounts[strtoupper($status)] = (int) $total;
      }

      $topProductsRows = OrderItem::query()
        ->join('orders', 'orders.id', '=', 'order_items.order_id')
        ->join('products', 'products.id', '=', 'order_items.product_id')
        ->where('orders.user_id', $user->id)
        ->whereIn('orders.status', ['DELIVERED', 'COMPLETED'])
        ->groupBy('order_items.product_id', 'products.title', 'products.slug')
        ->selectRaw('
              order_items.product_id,
              products.title,
              products.slug,
              SUM(order_items.quantity) as total_qty,
              MAX(orders.placed_at) as last_order_at
          ')
        ->orderByDesc('total_qty')
        ->limit(5)
        ->get();

      $topProducts = [];
      foreach ($topProductsRows as $row) {
        $topProducts[] = [
          'id'            => (string) $row->product_id,
          'slug'          => (string) $row->slug,
          'title'         => (string) $row->title,
          'total_qty'     => (int) $row->total_qty,
          'last_order_at' => $row->last_order_at,
        ];
      }

      $stats = [
        'total_orders'        => $totalOrders,
        'delivered_orders'    => $deliveredOrders,
        'cancelled_orders'    => $cancelledOrders,
        'total_spent_vnd'     => $totalSpentVnd,
        'avg_order_value_vnd' => $avgOrderValueVnd,
        'recent_orders_count' => $recentOrdersCount,
        'recent_spent_vnd'    => $recentSpentVnd,
        'status_counts'       => $statusCounts,
        'top_products'        => $topProducts,
      ];

      // ========= LỌC + PHÂN TRANG ĐƠN HÀNG (TAB LỊCH SỬ) =========
      $statusGroup = $request->query('status_group');
      $createdFrom = $request->query('created_from');
      $createdTo   = $request->query('created_to');

      $perPage = (int) $request->query('per_page_order', 10);
      if ($perPage <= 0) {
        $perPage = 10;
      }
      if ($perPage > 50) {
        $perPage = 50;
      }

      $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');

      // cần items để tính review theo từng order
      $ordersQuery = Order::where('user_id', $user->id)
        ->with('items');

      if ($statusGroup === 'processing') {
        $ordersQuery->whereIn('status', [
          'PENDING',
          'PROCESSING',
          'PICKING',
          'SHIPPING',
          'CONFIRMED',
          'SHIPPED',
        ]);
      } elseif ($statusGroup === 'completed') {
        $ordersQuery->whereIn('status', [
          'COMPLETED',
          'DELIVERED',
        ]);
      } elseif ($statusGroup === 'cancelled') {
        $ordersQuery->whereIn('status', [
          'CANCELLED',
          'RETURNED',
          'DELIVERY_FAILED',
        ]);
      }


      if (!empty($createdFrom)) {
        $fromUtc = Carbon::createFromFormat('Y-m-d', (string) $createdFrom, $tz)
          ->startOfDay()
          ->utc();
        $ordersQuery->where('placed_at', '>=', $fromUtc);
      }

      if (!empty($createdTo)) {
        $toUtc = Carbon::createFromFormat('Y-m-d', (string) $createdTo, $tz)
          ->endOfDay()
          ->utc();
        $ordersQuery->where('placed_at', '<=', $toUtc);
      }

      $orders = $ordersQuery
        ->orderByDesc('placed_at')
        ->paginate($perPage)
        ->appends($request->query());

      // ========= TÍNH THỐNG KÊ REVIEW THEO ĐƠN =========
      $orderReviewStats = [];

      if ($orders->count() > 0) {
        $orderIds   = [];
        $productIds = [];

        foreach ($orders as $order) {
          $orderIds[] = $order->id;
          foreach ($order->items as $item) {
            if ($item->product_id !== null) {
              $productIds[] = $item->product_id;
            }
          }
        }

        $orderIds   = array_values(array_unique($orderIds));
        $productIds = array_values(array_unique($productIds));

        if (!empty($orderIds) && !empty($productIds)) {
          $reviews = Review::where('user_id', $user->id)
            ->whereIn('order_id', $orderIds)
            ->whereIn('product_id', $productIds)
            ->get(['order_id', 'product_id']);

          $reviewMap = [];
          foreach ($reviews as $review) {
            $reviewMap[$review->order_id . ':' . $review->product_id] = true;
          }

          foreach ($orders as $order) {
            $totalItems    = 0;
            $reviewedItems = 0;

            foreach ($order->items as $item) {
              if ($item->product_id === null) {
                continue;
              }

              $totalItems++;

              $key = $order->id . ':' . $item->product_id;
              if (isset($reviewMap[$key])) {
                $reviewedItems++;
              }
            }

            $orderReviewStats[$order->id] = [
              'total_items'    => $totalItems,
              'reviewed_items' => $reviewedItems,
            ];
          }
        }
      }

      if ($request->ajax() && $request->query('tab') === 'orders') {
        return view('user.profile.partials.ordersTable', [
          'orders'           => $orders,
          'orderReviewStats' => $orderReviewStats,
        ]);
      }

      return view('user.profileOverview', [
        'user'             => $user,
        'addresses'        => $addresses,
        'recentOrders'     => $recentOrders,
        'provinces'        => $provinces,
        'orders'           => $orders,
        'statusGroup'      => $statusGroup,
        'createdFrom'      => $createdFrom,
        'createdTo'        => $createdTo,
        'orderReviewStats' => $orderReviewStats,
        'stats'            => $stats,
      ]);
    } catch (Throwable $e) {
      return back()->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }
  }

  public function updateInfo(UpdateProfileRequest $request): RedirectResponse
  {
    $authUser = Auth::user();
    if ($authUser === null) {
      return redirect()->route('login');
    }

    try {
      $data = $request->validated();
      $updated = $this->profileService->updateInfo($authUser->id, $data);

      if (!$updated) {
        return redirect()
          ->route('user.profile.index', ['tab' => 'info'])
          ->withInput()
          ->withErrors(
            ['general' => 'Không tìm thấy tài khoản, vui lòng đăng nhập lại.'],
            'profile'
          );
      }

      return redirect()
        ->route('user.profile.index', ['tab' => 'info'])
        ->with('toast_success', 'Cập nhật thành công');
    } catch (Throwable $e) {
      report($e);

      return redirect()
        ->route('user.profile.index', ['tab' => 'info'])
        ->withInput()
        ->withErrors(
          ['general' => 'Có lỗi xảy ra, vui lòng thử lại sau'],
          'profile'
        );
    }
  }

  public function getWards(Request $request): JsonResponse
  {
    $provinceId = (int) $request->query('province_id');

    if ($provinceId <= 0) {
      return response()->json([
        'success' => false,
        'message' => 'Thiếu hoặc sai Tỉnh/Thành phố',
        'wards'   => [],
      ], 400);
    }

    $wards = Ward::where('province_id', $provinceId)
      ->orderBy('name_with_type')
      ->get([
        'id',
        'name',
        'name_with_type',
      ]);

    return response()->json([
      'success' => true,
      'wards'   => $wards,
    ]);
  }

  public function storeNewAddress(StoreRequest $request): RedirectResponse
  {
    try {
      $userId = Auth::id();
      $address = $request->input('address');

      $isExists = Address::where('user_id', $userId)
        ->where('address', $address)
        ->exists();

      if ($isExists) {
        return back()
          ->withInput()
          ->with('toast_error', 'Tên địa chỉ đã tồn tại');
      }

      $provinceId = (int) $request->input('address_province_id');
      $wardId = (int) $request->input('address_ward_id');

      if (!Province::where('id', $provinceId)->exists()) {
        return back()
          ->withInput()
          ->with('toast_error', 'Tỉnh/Thành phố không hợp lệ');
      }

      if (!Ward::where('id', $wardId)->where('province_id', $provinceId)->exists()) {
        return back()
          ->withInput()
          ->with('toast_error', 'Phường/Xã không hợp lệ hoặc không thuộc Tỉnh/Thành đã chọn');
      }

      $created = $this->addressService->create($request->validated());
      if (!$created) {
        return back()->with('toast_error', 'Thêm địa chỉ thất bại');
      }

      return back()->with('toast_success', 'Thêm địa chỉ thành công');
    } catch (Throwable $e) {
      return back()
        ->withInput()
        ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }
  }

  public function updateAddress(string $id, UpdateRequest $request): RedirectResponse
  {
    try {
      $userId = Auth::id();
      $address = $request->input('address');

      $isExists = Address::where('user_id', $userId)
        ->where('address', $address)
        ->where('id', '!=', $id)
        ->exists();

      if ($isExists) {
        return back()
          ->withInput()
          ->with('toast_error', 'Tên địa chỉ đã tồn tại');
      }

      $provinceId = (int) $request->input('address_province_id');
      $wardId = (int) $request->input('address_ward_id');

      if (!Province::where('id', $provinceId)->exists()) {
        return back()
          ->withInput()
          ->with('toast_error', 'Tỉnh/Thành phố không hợp lệ');
      }

      if (!Ward::where('id', $wardId)->where('province_id', $provinceId)->exists()) {
        return back()
          ->withInput()
          ->with('toast_error', 'Phường/Xã không hợp lệ hoặc không thuộc Tỉnh/Thành đã chọn');
      }

      $updated = $this->addressService->update($id, $request->validated());
      if (!$updated) {
        return back()
          ->withInput()
          ->with('toast_error', 'Cập nhật địa chỉ thất bại');
      }

      return back()->with('toast_success', 'Cập nhật địa chỉ thành công');
    } catch (Throwable $e) {
      return back()
        ->withInput()
        ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }
  }

  public function destroyAddress(string $id, DestroyRequest $request): RedirectResponse
  {
    try {
      $deleted = $this->addressService->destroy($id);

      if (!$deleted) {
        return redirect()
          ->route('user.profile.index', ['tab' => 'addresses'])
          ->with('toast_error', 'Xóa địa chỉ thất bại');
      }

      return redirect()
        ->route('user.profile.index', ['tab' => 'addresses'])
        ->with('toast_success', 'Xóa địa chỉ thành công');
    } catch (Throwable $e) {
      return redirect()
        ->route('user.profile.index', ['tab' => 'addresses'])
        ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }
  }

  public function setAddressDefault(string $id): RedirectResponse
  {
    try {
      $ok = $this->addressService->setDefault($id);
      if (!$ok) {
        return back()
          ->with('toast_error', 'Đặt mặc định địa chỉ thất bại');
      }

      return back()->with('toast_success', 'Đặt mặc định địa chỉ thành công');
    } catch (Throwable $e) {
      return back()
        ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }
  }
}
