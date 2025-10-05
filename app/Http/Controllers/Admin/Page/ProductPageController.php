<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\Product\ProductService;

class ProductPageController extends Controller
{
  public function __construct(
    private ProductService $productService,
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

    return view('admin.product.index', compact('categories', 'authors', 'publishers', 'products'));
  }

  public function create()
  {
    $categories = $this->productService->getListCategory();
    $authors    = $this->productService->getListAuthor();
    $publishers = $this->productService->getListPublisher();

    return view('admin.product.create', compact('categories', 'authors', 'publishers'));
  }
}
