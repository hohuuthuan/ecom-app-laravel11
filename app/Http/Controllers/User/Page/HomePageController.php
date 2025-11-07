<?php

namespace App\Http\Controllers\User\Page;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\ProductService;
use App\Services\User\HomeService;

class HomePageController extends Controller
{
  public function __construct(
    private ProductService $productService,
    private HomeService $homeService,
  ) {}

  public function index(Request $r)
  {
    $filters = [
      'per_page'      => (int)$r->query('per_page_product', 10),
      'keyword'       => $r->query('keyword'),
      'status'        => $r->query('status'),
      'category_id'   => $r->query('category_id'),
      'author_id'     => $r->query('author_id'),
      'publisher_id'  => $r->query('publisher_id'),
      'price_min'     => $r->query('price_min'),
      'price_max'     => $r->query('price_max'),
      'stock_min'     => $r->query('stock_min'),
      'stock_max'     => $r->query('stock_max'),
    ];

    $categories = $this->productService->getListCategory();
    $authors    = $this->productService->getListAuthor();
    $publishers = $this->productService->getListPublisher();

    $products   = $this->productService->getList($filters);

    return view('user.home', compact('categories', 'authors', 'publishers', 'products'));
  }

  public function listFavoriteProduct()
  {
    $categories = $this->productService->getListCategory();
    $authors    = $this->productService->getListAuthor();
    $publishers = $this->productService->getListPublisher();
    $products = $this->homeService->getListFavoriteProduct();

    return view('user.favoriteProduct', compact('categories', 'authors', 'publishers', 'products'));
  }

  public function productDetail(Request $request)
  {
    $id = (string) $request->route('id');
    $product = $this->productService->getProductDetail($id);
    if (!$product) {
      return back()->with('toast_error', 'Không tìm thấy sản phẩm');
    }

    return view('user.productDetail', compact('product'));
  }
}
