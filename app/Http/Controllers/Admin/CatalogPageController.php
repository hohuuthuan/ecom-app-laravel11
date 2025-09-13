<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\CategoryService;
use App\Services\Admin\BrandService;

class CatalogPageController extends Controller
{
  public function __construct(
    private CategoryService $categoryService,
    private BrandService $brandService
  ) {}

  public function index(Request $r)
  {
    $catFilters = [
      'keyword'  => (string)$r->query('cat_keyword', ''),
      'status'   => (string)$r->query('cat_status', ''),
      'per_page' => (int)$r->query('per_page_cat', 10),
    ];
    $brandFilters = [
      'keyword'  => (string)$r->query('brand_keyword', ''),
      'status'   => (string)$r->query('brand_status', ''),
      'per_page' => (int)$r->query('per_page_brand', 10),
    ];

    $categories = $this->categoryService->getList($catFilters);
    $brands     = $this->brandService->getList($brandFilters);

    return view('admin.catalog.index', compact('categories', 'brands'));
  }
}
