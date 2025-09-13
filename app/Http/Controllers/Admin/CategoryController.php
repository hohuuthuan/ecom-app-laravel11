<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CategoryService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Category\StoreRequest;
use App\Http\Requests\Admin\Category\UpdateRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class CategoryController extends Controller
{
  public function __construct(private CategoryService $categoryService) {}

  public function index(Request $request)
  {
    $filters    = $request->only(['keyword', 'status', 'per_page']);
    $categories = $this->categoryService->getList($filters);
    $parents    = $this->categoryService->listParents();
    return view('admin.categories', compact('categories', 'parents'));
  }

  public function store(StoreRequest $request): RedirectResponse
  {
    try {
      $newCategory = $this->categoryService->create($request->validated(), $request->file('image'));
      if (!$newCategory) {
        return back()->withInput()->with('toast_error', 'Tạo category thất bại.');
      }
      return back()->with('toast_success', 'Tạo category thành công.');
    } catch (Throwable $e) {
      return back()->withInput()->with('toast_error', 'Có lỗi xảy ra.');
    }
  }

  public function update(UpdateRequest $request, string $id): RedirectResponse
  {
    try {
      $ok = $this->categoryService->update($id, $request->validated(), $request->file('image'));
      if (!$ok) return back()->withInput()->with('toast_error', 'Cập nhật category thất bại.');
      return back()->with('toast_success', 'Cập nhật category thành công.');
    } catch (Throwable $e) {
      return back()->withInput()->with('toast_error', 'Có lỗi xảy ra.');
    }
  }

  public function destroy(string $id): RedirectResponse
  {
    $ok = $this->categoryService->delete($id);
    return back()->with($ok ? 'toast_success' : 'toast_error', $ok ? 'Đã xoá category.' : 'Xoá category thất bại.');
  }

  public function bulkDelete(Request $request): RedirectResponse
  {
    $ids = (array) $request->input('ids', []);
    $deleted = $this->categoryService->bulkDelete($ids);
    return back()->with('toast_success', "Đã xoá {$deleted} category.");
  }
}
