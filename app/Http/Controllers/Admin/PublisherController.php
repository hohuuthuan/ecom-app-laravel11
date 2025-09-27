<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\Catalog\PublisherService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Publisher\StoreRequest;
use App\Http\Requests\Admin\Publisher\UpdateRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class PublisherController extends Controller
{
  public function __construct(private PublisherService $publisherService) {}

  public function store(StoreRequest $request): RedirectResponse
  {
    try {
      $newPublisher = $this->publisherService->create($request->validated(), $request->file('logo'));
      if (!$newPublisher) {
        return back()->with('toast_error', 'Thêm nhà xuất bản thất bại');
      }

      return back()->with('toast_success', 'Thêm nhà xuất bản thành công');
    } catch (Throwable $e) {
      return back()->with('toast_error', 'Có lỗi xảy ra');
    }
  }

  public function update(UpdateRequest $request, string $id): RedirectResponse
  {
    try {
      $updatedAuthor = $this->publisherService->update($id, $request->validated());
      if (!$updatedAuthor) {
        return back()->with('toast_error', 'Cập nhật nhà xuất bản thất bại');
      }

      return back()->with('toast_success', 'Cập nhật nhà xuất bản thành công');
    } catch (Throwable $e) {
      return back()->with('toast_error', 'Có lỗi xảy ra');
    }
  }

  public function destroy(string $id): RedirectResponse
  {
    $ok = $this->publisherService->delete($id);
    if (!$ok) {
      return back()->with('toast_error', 'Xóa nhà xuất bản thất bại');
    }

    return back()->with('toast_success', 'Xóa nhà xuất bản thành công');
  }

  public function bulkDelete(Request $request): RedirectResponse
  {
    $ids = (array) $request->input('ids', []);
    $deleted = $this->publisherService->bulkDelete($ids);
    return back()->with('toast_success', "Đã xoá {$deleted} nhà xuất bản");
  }
}
