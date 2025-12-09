<?php

namespace App\Http\Controllers\Shipper\Page;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class ShipperPageController extends Controller
{
  public function dashboard(Request $request): View
  {
    $shipper = $request->user();

    $status  = strtoupper((string) $request->query('status', 'SHIPPING'));
    $keyword = $request->query('keyword');
    $perPage = (int) $request->query('per_page', 15);

    if ($perPage <= 0 || $perPage > 100) {
      $perPage = 15;
    }

    $baseQuery = Order::query()
      ->with(['user:id,name,phone,email', 'shipment:id,order_id,address,phone'])
      ->where('shipping_type', 'INTERNAL')
      ->where('shipper_id', $shipper->id);

    $validStatuses = ['SHIPPING', 'COMPLETED', 'DELIVERY_FAILED', 'RETURNED'];

    $orderQuery = clone $baseQuery;

    if (in_array($status, $validStatuses, true)) {
      $orderQuery->where('status', $status);
    } else {
      $status = 'SHIPPING';
      $orderQuery->where('status', 'SHIPPING');
    }

    if (is_string($keyword) && $keyword !== '') {
      $kw = '%' . $keyword . '%';

      $orderQuery->where(function ($q) use ($kw) {
        $q->where('code', 'LIKE', $kw)
          ->orWhereHas('user', function ($uq) use ($kw) {
            $uq->where('name', 'LIKE', $kw)
              ->orWhere('phone', 'LIKE', $kw)
              ->orWhere('email', 'LIKE', $kw);
          })
          ->orWhereHas('shipment', function ($sq) use ($kw) {
            $sq->where('address', 'LIKE', $kw)
              ->orWhere('phone', 'LIKE', $kw);
          });
      });
    }

    $orders = $orderQuery
      ->orderByDesc('placed_at')
      ->orderByDesc('created_at')
      ->paginate($perPage)
      ->withQueryString();

    $statsBase = clone $baseQuery;

    $stats = [
      'shipping'  => (clone $statsBase)->where('status', 'SHIPPING')->count(),
      'completed' => (clone $statsBase)->where('status', 'COMPLETED')->count(),
      'failed'    => (clone $statsBase)->whereIn('status', ['DELIVERY_FAILED', 'RETURNED'])->count(),
    ];

    $filters = [
      'status'   => $status,
      'keyword'  => $keyword,
      'per_page' => $perPage,
    ];

    return view('shipper.dashboard', compact('orders', 'stats', 'filters'));
  }

  public function detail(string $id): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
  {
    /** @var \App\Models\User $user */
    $user = auth()->user();

    $order = \App\Models\Order::query()
      ->with(['items.product', 'shipment', 'user'])
      ->where('id', $id)
      ->when($user->hasRole('Shipper'), function ($q) use ($user) {
        $q->where('shipper_id', $user->id)
          ->where('shipping_type', 'INTERNAL');
      })
      ->first();

    if (!$order) {
      return redirect()
        ->route('shipper.dashboard')
        ->with('toast_error', 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem.');
    }

    return view('shipper.detail', compact('order'));
  }

  public function changeStatus(Request $request, string $id): RedirectResponse
  {
    $shipper = $request->user();
    $validated = $request->validate([
      'status' => [
        'required',
        'string',
        Rule::in(['SHIPPING', 'COMPLETED', 'DELIVERY_FAILED', 'RETURNED']),
      ],
    ]);

    $newStatus = strtoupper($validated['status']);

    $order = Order::with(['items'])->findOrFail($id);

    // Chỉ cho phép shipper nội bộ được gán vào đơn này cập nhật
    if (
      strtoupper((string) $order->shipping_type) !== 'INTERNAL'
      || (string) $order->shipper_id !== (string) $shipper->id
    ) {
      return back()->with('toast_error', 'Bạn không được phép cập nhật đơn hàng này.');
    }

    $currentStatus = strtoupper((string) $order->status);
    if ($currentStatus !== 'SHIPPING') {
      return back()->with('toast_error', 'Chỉ đơn đang giao mới được cập nhật kết quả');
    }
    if ($newStatus === 'SHIPPING') {
      return back()->with('toast_info', 'Trạng thái đơn hàng không thay đổi.');
    }

    try {
      DB::transaction(function () use ($order, $currentStatus, $newStatus): void {
        $itemIds = $order->items->pluck('id')->all();

        if (
          in_array($newStatus, ['DELIVERY_FAILED', 'RETURNED'], true)
          && !empty($itemIds)
        ) {
          $batches = DB::table('order_batches as ob')
            ->join('order_items as oi', 'oi.id', '=', 'ob.order_item_id')
            ->select([
              'ob.order_item_id',
              'ob.batch_id',
              'ob.quantity',
              'oi.product_id',
              'oi.warehouse_id',
            ])
            ->whereIn('ob.order_item_id', $itemIds)
            ->lockForUpdate()
            ->get();

          if ($batches->isNotEmpty()) {
            $totalPerStock = [];

            foreach ($batches as $row) {
              $pid = (string) $row->product_id;
              $wid = (string) $row->warehouse_id;
              $qty = (int) $row->quantity;
              DB::table('batch_stocks')
                ->where('product_id', $pid)
                ->where('warehouse_id', $wid)
                ->where('batch_id', $row->batch_id)
                ->lockForUpdate()
                ->increment('on_hand', $qty);

              $key = $pid . '|' . $wid;

              if (!isset($totalPerStock[$key])) {
                $totalPerStock[$key] = [
                  'product_id'  => $pid,
                  'warehouse_id' => $wid,
                  'qty'         => 0,
                ];
              }

              $totalPerStock[$key]['qty'] += $qty;
            }
            foreach ($totalPerStock as $row) {
              Stock::where('product_id', $row['product_id'])
                ->where('warehouse_id', $row['warehouse_id'])
                ->lockForUpdate()
                ->increment('on_hand', $row['qty']);
            }
            DB::table('order_batches')
              ->whereIn('order_item_id', $itemIds)
              ->delete();
          }
        }
        $order->status = $newStatus;
        if (
          $newStatus === 'COMPLETED'
          && strtoupper((string) $order->payment_method) === 'COD'
          && strtolower((string) $order->payment_status) !== 'paid'
        ) {
          $order->payment_status = 'paid';
        }

        $order->save();
      });
    } catch (\Throwable $e) {
      return back()->with('toast_error', 'Cập nhật kết quả giao hàng thất bại. Vui lòng thử lại.');
    }

    return back()->with('toast_success', 'Cập nhật kết quả giao hàng thành công.');
  }
}
