<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\Product\ProductService;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Http\Requests\Admin\Product\UpdateRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Product;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use Throwable;

class ProductController extends Controller
{
  public function __construct(private ProductService $productService) {}

  public function store(StoreRequest $request): RedirectResponse
  {
    try {
      $data = $request->validated();

      $errors = [];
      if (Product::where('title', $data['title'])->exists()) {
        $errors['title'] = 'Tiêu đề đã tồn tại';
      }
      if (Product::where('slug',  $data['slug'])->exists()) {
        $errors['slug']  = 'Slug đã tồn tại';
      }
      if (Product::where('code',  $data['code'])->exists()) {
        $errors['code']  = 'Mã sản phẩm đã tồn tại';
      }
      if (Product::where('isbn',  $data['isbn'])->exists()) {
        $errors['isbn']  = 'ISBN đã tồn tại';
      }
      if (!Publisher::whereKey($data['publisher_id'])->exists()) {
        $errors['publisher_id'] = 'Nhà xuất bản không tồn tại';
      }

      $catIds  = array_values(array_unique($data['categoriesInput']));
      if (Category::whereIn('id', $catIds)->count() !== count($catIds)) {
        $errors['categoriesInput'] = 'Có danh mục không tồn tại';
      }

      $authIds = array_values(array_unique($data['authorsInput']));
      if (Author::whereIn('id', $authIds)->count() !== count($authIds)) {
        $errors['authorsInput'] = 'Có tác giả không tồn tại';
      }
      if (!empty($errors)) {
        return back()->withErrors($errors)->withInput()->with('toast_error', 'Vui lòng kiểm tra các trường');
      }

      $newProduct = $this->productService->create($data, $request->file('image'));
      if (!$newProduct) {
        return back()->with('toast_error', 'Thêm sản phẩm thất bại');
      }

      return back()->with('toast_success', 'Thêm sản phẩm thành công');
    } catch (Throwable $e) {
      return back()->with('toast_error', 'Có lỗi xảy ra');
    }
  }

  // public function update(UpdateRequest $request, string $id): RedirectResponse
  // {
  //   try {
  //     $updatedAuthor = $this->productService->update($id, $request->validated());
  //     if (!$updatedAuthor) {
  //       return back()->with('toast_error', 'Cập nhật nhà xuất bản thất bại');
  //     }

  //     return back()->with('toast_success', 'Cập nhật nhà xuất bản thành công');
  //   } catch (Throwable $e) {
  //     return back()->with('toast_error', 'Có lỗi xảy ra');
  //   }
  // }

  // public function destroy(string $id): RedirectResponse
  // {
  //   $ok = $this->productService->delete($id);
  //   if (!$ok) {
  //     return back()->with('toast_error', 'Xóa nhà xuất bản thất bại');
  //   }

  //   return back()->with('toast_success', 'Xóa nhà xuất bản thành công');
  // }

  // public function bulkDelete(Request $request): RedirectResponse
  // {
  //   $ids = (array) $request->input('ids', []);
  //   $deleted = $this->productService->bulkDelete($ids);
  //   return back()->with('toast_success', "Đã xoá {$deleted} nhà xuất bản");
  // }
}
