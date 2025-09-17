<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\BrandService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Brand\StoreRequest;
use App\Http\Requests\Admin\Brand\UpdateRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class BrandController extends Controller
{
  public function __construct(private BrandService $brandService) {}

  public function index(Request $request)
  {
    $filters = $request->only(['keyword', 'status', 'per_page']);
    $brands  = $this->brandService->getList($filters);
    return view('admin.brands', compact('brands'));
  }

  public function store(StoreRequest $request): RedirectResponse
  {
    try {
      $newBrand = $this->brandService->create($request->validated(), $request->file('image'));
      if (!$newBrand) {
        return back()->withInput()->with('error', 'Tạo brand thất bại.');
      }
      return back()->with('success', 'Tạo brand thành công.');
    } catch (Throwable $e) {
      return back()->withInput()->with('error', 'Có lỗi xảy ra.');
    }
  }

  public function update(UpdateRequest $request, string $id): RedirectResponse
  {
    try {
      $updatedBrand = $this->brandService->update($id, $request->validated(), $request->file('image'));
      if (!$updatedBrand) {
        return back()->withInput()->with('error', 'Cập nhật brand thất bại.');
      }
      return back()->with('success', 'Cập nhật brand thành công.');
    } catch (Throwable $e) {

      return back()->withInput()->with('error', 'Có lỗi xảy ra.');
    }
  }

  public function destroy(string $id): RedirectResponse
  {
    $ok = $this->brandService->delete($id);
    return back()->with($ok ? 'success' : 'error', $ok ? 'Đã xoá brand.' : 'Xoá brand thất bại.');
  }

  public function bulkDelete(Request $request): RedirectResponse
  {
    $ids = (array) $request->input('ids', []);
    $deleted = $this->brandService->bulkDelete($ids);
    return back()->with('success', "Đã xoá {$deleted} brand.");
  }
}
