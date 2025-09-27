<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\Catalog\AuthorService;
use App\Http\Requests\Admin\Author\StoreRequest;
use App\Http\Requests\Admin\Author\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Throwable;

class AuthorController extends Controller
{
  public function __construct(private AuthorService $AuthorService) {}
  
  public function store(StoreRequest $request): RedirectResponse
  {
    try {
      $newAuthor= $this->AuthorService->create($request->validated(), $request->file('image'));
      if (!$newAuthor) {
        return back()->with('toast_error', 'Thêm tác giả thất bại');
      }
      
      return back()->with('toast_success', 'Thêm tác giả thành công');
    } catch (Throwable $e) {
      return back()->with('toast_error', 'Có lỗi xảy ra');
    }
  }

  public function update(UpdateRequest $request, string $id): RedirectResponse
  {
    try {
      $updatedAuthor = $this->AuthorService->update($id, $request->validated());
      if (!$updatedAuthor) {
        return back()->with('toast_error', 'Cập nhật tác giả thất bại');
      }

      return back()->with('toast_success', 'Cập nhật tác giả thành công');
    } catch (Throwable $e) {
      return back()->with('toast_error', 'Có lỗi xảy ra');
    }
  }

  public function destroy(string $id): RedirectResponse
  {
    $ok = $this->AuthorService->delete($id);
    if (!$ok) {
        return back()->with('toast_error', 'Xóa nhật tác giả thất bại');
    }

    return back()->with('toast_success', 'Xóa tác giả thành công');
  }

  public function bulkDelete(Request $request): RedirectResponse
  {
    $ids = (array) $request->input('ids', []);
    $deleted = $this->AuthorService->bulkDelete($ids);
    return back()->with('toast_success', "Đã xoá {$deleted} tác giả");
  }
}
