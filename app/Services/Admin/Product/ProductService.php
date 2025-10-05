<?php

namespace App\Services\Admin\Product;

use App\Models\Product;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Helpers\PaginationHelper;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

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
      ->select(['id', 'title', 'slug', 'isbn', 'selling_price_vnd', 'status', 'publisher_id', 'created_at'])
      ->with([
        'categories:id,name',
        'authors:id,name',
        'publisher:id,name'
      ]);

    // keyword: tìm theo name, slug, isbn
    if (!empty($filters['keyword'])) {
      $kw = trim((string)$filters['keyword']);
      $query->where(function ($q) use ($kw) {
        $q->where('title', 'LIKE', "%{$kw}%")
          ->orWhere('slug', 'LIKE', "%{$kw}%")
          ->orWhere('isbn', 'LIKE', "%{$kw}%");
      });
    }

    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }
    if (!empty($filters['category_id'])) {
      $categoryId = (string)$filters['category_id'];
      $query->whereHas('categories', function ($q) use ($categoryId) {
        $q->where('categories.id', $categoryId);
      });
    }
    if (!empty($filters['author_id'])) {
      $authorId = (string)$filters['author_id'];
      $query->whereHas('authors', function ($q) use ($authorId) {
        $q->where('authors.id', $authorId);
      });
    }
    if (!empty($filters['publisher_id'])) {
      $query->where('publisher_id', (string)$filters['publisher_id']);
    }
    if ($filters['price_min'] !== null && $filters['price_min'] !== '') {
      $query->where('price', '>=', (int)$filters['price_min']);
    }
    if ($filters['price_max'] !== null && $filters['price_max'] !== '') {
      $query->where('price', '<=', (int)$filters['price_max']);
    }
    if ($filters['stock_min'] !== null && $filters['stock_min'] !== '') {
      $query->where('stock', '>=', (int)$filters['stock_min']);
    }
    if ($filters['stock_max'] !== null && $filters['stock_max'] !== '') {
      $query->where('stock', '<=', (int)$filters['stock_max']);
    }

    $perPage = (int)($filters['per_page'] ?? 10);
    if ($perPage <= 0) {
      $perPage = 10;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    $products = $query
      ->orderByDesc('created_at')
      ->paginate($perPage);

    return PaginationHelper::appendQuery($products);
  }

  public function create(array $data, UploadedFile $image): bool
  {
    $savedPath = null;
    try {
      $fileName  = $image->hashName();
      $savedPath = 'products/' . $fileName;
      $image->storeAs('products', $fileName, 'public');

      DB::transaction(function () use ($data, $fileName) {
        $product = Product::create([
          'title'             => $data['title'],
          'slug'              => $data['slug'],
          'code'              => $data['code'],
          'isbn'              => $data['isbn'],
          'description'       => $data['description'],
          'selling_price_vnd' => (int)$data['selling_price_vnd'],
          'unit'              => $data['unit'],
          'status'            => $data['status'],
          'publisher_id'      => $data['publisher_id'],
          'image'             => $fileName,
        ]);
        $product->categories()->sync(array_values(array_unique((array)$data['categoriesInput'])));
        $product->authors()->sync(array_values(array_unique((array)$data['authorsInput'])));
      });

      return true;
    } catch (Throwable $e) {
      if ($savedPath && Storage::disk('public')->exists($savedPath)) {
        Storage::disk('public')->delete($savedPath);
      }
      Log::error('Product create failed', ['msg' => $e->getMessage()]);
      return false;
    }
  }
}
