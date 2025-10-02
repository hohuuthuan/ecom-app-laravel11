<?php

namespace App\Services\Admin\Product;

use App\Models\Product;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Helpers\PaginationHelper;
use Illuminate\Support\Collection;

class ProductService
{
  public function getListCategory(): Collection
  {
    return Category::query()
      ->select('id', 'name')
      ->where('status', 'ACTIVE')
      ->orderBy('name', 'asc')
      ->get();
  }

  public function getListAuthor(): Collection
  {
    return Author::query()
      ->select('id', 'name')
      ->where('status', 'ACTIVE')
      ->orderBy('name', 'asc')
      ->get();
  }

  public function getListPublisher(): Collection
  {
    return Publisher::query()
      ->select('id', 'name')
      ->where('status', 'ACTIVE')
      ->orderBy('name', 'asc')
      ->get();
  }

  public function getList(array $filters = [])
  {
    $query = Product::query()
      ->select(['id','name','slug','isbn','price','stock','status','publisher_id','created_at'])
      ->with([
        'categories:id,name',
        'authors:id,name',
        'publisher:id,name'
      ]);

    // keyword: tìm theo name, slug, isbn
    if (!empty($filters['keyword'])) {
      $kw = trim((string)$filters['keyword']);
      $query->where(function ($q) use ($kw) {
        $q->where('name', 'LIKE', "%{$kw}%")
          ->orWhere('slug', 'LIKE', "%{$kw}%")
          ->orWhere('isbn', 'LIKE', "%{$kw}%");
      });
    }

    // status
    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    // category_id: 1-nhiều
    if (!empty($filters['category_id'])) {
      $categoryId = (string)$filters['category_id'];
      $query->whereHas('categories', function ($q) use ($categoryId) {
        $q->where('categories.id', $categoryId);
      });
    }

    // author_id: 1-nhiều
    if (!empty($filters['author_id'])) {
      $authorId = (string)$filters['author_id'];
      $query->whereHas('authors', function ($q) use ($authorId) {
        $q->where('authors.id', $authorId);
      });
    }

    // publisher_id: 1-1
    if (!empty($filters['publisher_id'])) {
      $query->where('publisher_id', (string)$filters['publisher_id']);
    }

    // price range
    if ($filters['price_min'] !== null && $filters['price_min'] !== '') {
      $query->where('price', '>=', (int)$filters['price_min']);
    }
    if ($filters['price_max'] !== null && $filters['price_max'] !== '') {
      $query->where('price', '<=', (int)$filters['price_max']);
    }

    // stock range
    if ($filters['stock_min'] !== null && $filters['stock_min'] !== '') {
      $query->where('stock', '>=', (int)$filters['stock_min']);
    }
    if ($filters['stock_max'] !== null && $filters['stock_max'] !== '') {
      $query->where('stock', '<=', (int)$filters['stock_max']);
    }

    // phân trang
    $perPage = (int)($filters['per_page'] ?? 10);
    if ($perPage <= 0) { $perPage = 10; }
    if ($perPage > 200) { $perPage = 200; }

    $products = $query
      ->orderByDesc('created_at')
      ->paginate($perPage);

    return PaginationHelper::appendQuery($products);
  }
}
