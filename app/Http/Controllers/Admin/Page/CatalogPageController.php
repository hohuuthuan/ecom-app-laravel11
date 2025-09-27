<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\Catalog\CategoryService;
use App\Services\Admin\Catalog\AuthorService;
use App\Services\Admin\Catalog\PublisherService;

class CatalogPageController extends Controller
{
  public function __construct(
    private CategoryService $categoryService,
    private AuthorService $authorService,
    private PublisherService $publisherService
  ) {}

  public function index(Request $r)
  {
    $catFilters = [
      'keyword'  => (string)$r->query('cat_keyword', ''),
      'status'   => (string)$r->query('cat_status', ''),
      'per_page' => (int)$r->query('per_page_cat', 10),
    ];
    $authorFilters = [
      'keyword'  => (string)$r->query('author_keyword', ''),
      'status'   => (string)$r->query('author_status', ''),
      'per_page' => (int)$r->query('per_page_author', 10),
    ];

    $publisherFilters = [
      'keyword'  => (string)$r->query('publisher_keyword', ''),
      'status'   => (string)$r->query('publisher_status', ''),
      'per_page' => (int)$r->query('per_page_publisher', 10),
    ];

    $categories = $this->categoryService->getList($catFilters);
    $authors    = $this->authorService->getList($authorFilters);
    $publishers = $this->publisherService->getList($publisherFilters);

    return view('admin.catalog.index', compact('categories', 'authors', 'publishers'));
  }
}
