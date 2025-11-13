<?php

namespace App\Http\Controllers\User;

use App\Models\Address;
use App\Models\Province;
use App\Models\Ward;
use App\Models\Order;

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

      return view('user.profileOverview', [
        'user'         => $user,
        'addresses'    => $addresses,
        'recentOrders' => $recentOrders,
        'provinces'    => $provinces,
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

  public function store(StoreRequest $request): RedirectResponse
  {
    try {
      $userId  = Auth::id();
      $address = $request->input('address');

      $checkAddress = Address::where('user_id', $userId)
        ->where('address', $address)
        ->exists();
      if ($checkAddress) {
        return back()
          ->withInput()
          ->with('toast_error', 'Tên địa chỉ đã tồn tại');
      }

      $provinceId = $request->input('address_province_id');
      $wardId     = $request->input('address_ward_id');
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

      $newUserAddress = $this->addressService->create($request->validated());
      if (!$newUserAddress) {
        return back()->with('toast_error', 'Thêm địa chỉ thất bại');
      }

      return back()->with('toast_success', 'Thêm địa chỉ thành công');
    } catch (Throwable $e) {
      return back()
        ->withInput()
        ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }
  }

  public function update(string $id, UpdateRequest $request): RedirectResponse
  {
    try {
      return back()->with('toast_success', 'Cập nhật địa chỉ thành công');
    } catch (Throwable $e) {
      return back()
        ->withInput()
        ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }
  }

  public function destroy(string $id, DestroyRequest $request): RedirectResponse
  {
    return back()->with('toast_success', 'Xóa địa chỉ thành công');
    try {
    } catch (Throwable $e) {
      return back()
        ->withInput()
        ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }
  }

  public function setDefault(string $id, Request $request): RedirectResponse
  {
    return back()->with('toast_success', 'Đặt mặc định địa chỉ thành công');
    try {
    } catch (Throwable $e) {
      return back()
        ->withInput()
        ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }
  }
}
