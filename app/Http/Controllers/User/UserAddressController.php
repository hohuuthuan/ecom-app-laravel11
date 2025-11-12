<?php

namespace App\Http\Controllers\User;

use App\Models\Address;
use App\Models\Province;
use App\Models\Ward;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\User\AddressService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use App\Http\Requests\User\Address\StoreRequest;
use App\Http\Requests\User\Address\UpdateRequest;
use App\Http\Requests\User\Address\DestroyRequest;
use Throwable;

class UserAddressController extends Controller
{
  protected AddressService $addressService;

  public function __construct(AddressService $addressService)
  {
    $this->addressService = $addressService;
  }

  public function index(Request $request): View|RedirectResponse
  {
    try {
      $userAddress = $this->addressService->getList();
      return view('user.profile.userAddress', compact('userAddress'));
    } catch (Throwable $e) {
      return back()->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }
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
