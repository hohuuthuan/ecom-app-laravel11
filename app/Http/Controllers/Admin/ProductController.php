<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\Product\ProductService;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Http\Requests\Admin\Product\UpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

  public function update(UpdateRequest $request, string $id): RedirectResponse
  {
    try {
      $data = $request->validated();
      $errors = [];
      if (Product::where('title', $data['title'])->where('id', '!=', $id)->exists()) {
        $errors['title'] = 'Tiêu đề đã tồn tại';
      }
      if (Product::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
        $errors['slug'] = 'Slug đã tồn tại';
      }
      if (Product::where('code', $data['code'])->where('id', '!=', $id)->exists()) {
        $errors['code'] = 'Mã sản phẩm đã tồn tại';
      }
      if (Product::where('isbn', $data['isbn'])->where('id', '!=', $id)->exists()) {
        $errors['isbn'] = 'ISBN đã tồn tại';
      }
      if (!Publisher::whereKey($data['publisher_id'])->exists()) {
        $errors['publisher_id'] = 'Nhà xuất bản không tồn tại';
      }
      if (Category::whereIn('id', $data['categoriesInput'])->count() !== count($data['categoriesInput'])) {
        $errors['categoriesInput'] = 'Có danh mục không tồn tại';
      }
      if (Author::whereIn('id', $data['authorsInput'])->count() !== count($data['authorsInput'])) {
        $errors['authorsInput'] = 'Có tác giả không tồn tại';
      }
      if ($errors) {
        return back()->withErrors($errors)->withInput()->with('toast_error', 'Vui lòng kiểm tra các trường bị lỗi');
      }

      $product = Product::find($id);
      if (!$product) {
        return back()->with('toast_error', 'Sản phẩm không tồn tại');
      }

      $ok = $this->productService->update($product, $data, $request->file('image'));
      if (!$ok) {
        return back()->withInput()->with('toast_error', 'Cập nhật sản phẩm thất bại');
      }

      return back()->with('toast_success', 'Cập nhật sản phẩm thành công');
    } catch (Throwable $e) {
      return back()->with('toast_error', 'Có lỗi xảy ra');
    }
  }

  public function bulkUpdate(Request $request): RedirectResponse
  {
    $action = strtoupper((string)$request->input('action'));

    $rules = [
      'action' => ['required', 'string', Rule::in(['DISCOUNT', 'STATUS'])],
      'ids' => ['required', 'array', 'min:1'],
      'ids.*' => ['required', 'uuid', Rule::exists('products', 'id')],
    ];

    if ($action === 'DISCOUNT') {
      $rules['discount_percent'] = ['required', 'integer', 'min:0', 'max:100'];
    }

    if ($action === 'STATUS') {
      $rules['status'] = ['required', 'string', Rule::in(['ACTIVE', 'INACTIVE'])];
    }

    $validated = $request->validate($rules);
    $ids = array_values(array_unique((array)$validated['ids']));

    try {
      if ($action === 'DISCOUNT') {
        $percent = (int)$validated['discount_percent'];
        $affected = $this->productService->bulkUpdateDiscountPercent($ids, $percent);

        return back()->with('toast_success', "Đã cập nhật giảm giá {$percent}% cho {$affected} sản phẩm.");
      }

      if ($action === 'STATUS') {
        $status = strtoupper((string)$validated['status']);
        $affected = $this->productService->bulkUpdateStatus($ids, $status);

        $label = $status === 'ACTIVE' ? 'Đang bán' : 'Ẩn';
        return back()->with('toast_success', "Đã cập nhật trạng thái '{$label}' cho {$affected} sản phẩm.");
      }

      return back()->with('toast_error', 'Hành động không hợp lệ.');
    } catch (Throwable $e) {
      return back()->with('toast_error', 'Có lỗi xảy ra.');
    }
  }
}
