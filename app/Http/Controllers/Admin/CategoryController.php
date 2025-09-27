<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\Catalog\CategoryService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Category\StoreRequest;
use App\Http\Requests\Admin\Category\UpdateRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class CategoryController extends Controller
{
  public function __construct(private CategoryService $categoryService) {}

  public function store(StoreRequest $request): RedirectResponse
  {
    try {
      $newCategory = $this->categoryService->create($request->validated());
      if (!$newCategory) {
        return back()->withInput()->with('toast_error', 'Tạo danh mục thất bại');
      }
      return back()->with('toast_success', 'Tạo danh mục thành công');
    } catch (Throwable $e) {
      return back()->withInput()->with('toast_error', 'Có lỗi xảy ra');
    }
  }

  public function update(UpdateRequest $request, string $id): RedirectResponse
  {
    try {
      $ok = $this->categoryService->update($id, $request->validated(), $request->file('image'));
      if (!$ok) {
        return back()->withInput()->with('toast_error', 'Cập nhật danh mục thất bại');
      }

      return back()->with('toast_success', 'Cập nhật danh mục thành công');
    } catch (Throwable $e) {
      return back()->withInput()->with('toast_error', 'Có lỗi xảy ra');
    }
  }

  public function destroy(string $id): RedirectResponse
  {
    $ok = $this->categoryService->delete($id);
    if (!$ok) {
        return back()->withInput()->with('toast_error', 'Xoá danh mục thất bại');
    }
    return back()->with('toast_success', 'Xóa danh mục thành công');
  }

  public function bulkDelete(Request $request): RedirectResponse
  {
    $ids = (array)$request->input('ids', []);
    $deleted = $this->categoryService->bulkDelete($ids);
    return back()->with('toast_success', "Đã xoá {$deleted} danh mục");
  }
}
