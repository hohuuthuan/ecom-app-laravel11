<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Discount\StoreDiscountRequest;
use App\Http\Requests\Admin\Discount\UpdateDiscountRequest;
use App\Models\Discount;
use App\Services\Admin\Discount\DiscountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscountController extends Controller
{
  public function index(Request $request): View
  {
    $perPage = (int) $request->input('per_page_discount', 20);

    $query = Discount::query()
      ->withCount([
        'usages as total_used' => function ($q) {
          $q->whereNotNull('used_at');
        },
      ])
      ->latest('created_at');

    if ($request->filled('keyword')) {
      $keyword = trim($request->input('keyword'));
      $query->where('code', 'LIKE', '%' . $keyword . '%');
    }

    if ($request->filled('type')) {
      $query->where('type', $request->input('type'));
    }

    if ($request->filled('status')) {
      $query->where('status', $request->input('status'));
    }

    if ($request->filled('start_from')) {
      $query->whereDate('start_date', '>=', $request->input('start_from'));
    }

    if ($request->filled('start_to')) {
      $query->whereDate('start_date', '<=', $request->input('start_to'));
    }

    $discounts = $query->paginate($perPage);

    return view('admin.discount.index', compact('discounts'));
  }

  public function store(StoreDiscountRequest $request, DiscountService $service): RedirectResponse
  {
    $ok = $service->create($request->validated());

    if (!$ok) {
      return back()
        ->withInput()
        ->with('toast_error', 'Không thể tạo mã giảm giá. Vui lòng thử lại.');
    }

    return redirect()
      ->route('admin.discount.index')
      ->with('toast_success', 'Tạo mã giảm giá thành công.');
  }

  public function update(string $id, UpdateDiscountRequest $request, DiscountService $service): RedirectResponse
  {
    $ok = $service->update($id, $request->validated());

    if (!$ok) {
      return back()
        ->withInput()
        ->with('toast_error', 'Không thể cập nhật mã giảm giá. Vui lòng thử lại.');
    }

    return redirect()
      ->route('admin.discount.index')
      ->with('toast_success', 'Cập nhật mã giảm giá thành công.');
  }

  public function destroy(string $id): RedirectResponse
  {
    $discount = Discount::query()->find($id);

    if (!$discount) {
      return back()->with('toast_error', 'Mã giảm giá không tồn tại.');
    }

    $discount->delete();

    return back()->with('toast_success', 'Đã xoá mã giảm giá.');
  }
}
