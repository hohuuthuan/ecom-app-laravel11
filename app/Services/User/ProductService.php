<?php

namespace App\Services\User;

use App\Models\Product;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Review;
use App\Helpers\PaginationHelper;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
      ->select([
        'id',
        'title',
        'image',
        'slug',
        'isbn',
        'selling_price_vnd',
        'discount_percent',
        'status',
        'publisher_id',
        'created_at',
      ])
      ->with([
        'categories:id,name',
        'authors:id,name',
        'publisher:id,name',
        'stocks:product_id,on_hand,reserved',
      ]);

    if (Auth::check()) {
      $userId = Auth::id();
      $query->withExists([
        'favoredBy as is_favorited' => fn($q) => $q->where('users.id', $userId),
      ]);
    }

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
    if (!empty($filters['price_min']) && ($filters['price_min'] !== null && $filters['price_min'] !== '')) {
      $query->where('price', '>=', (int)$filters['price_min']);
    }
    if (!empty($filters['price_max']) && ($filters['price_max'] !== null && $filters['price_max'] !== '')) {
      $query->where('price', '<=', (int)$filters['price_max']);
    }
    if (!empty($filters['stock_min']) && ($filters['stock_min'] !== null && $filters['stock_min'] !== '')) {
      $query->where('stock', '>=', (int)$filters['stock_min']);
    }
    if (!empty($filters['stock_max']) && ($filters['stock_max'] !== null && $filters['stock_max'] !== '')) {
      $query->where('stock', '<=', (int)$filters['stock_max']);
    }

    $perPage = (int)($filters['per_page'] ?? 10);
    if ($perPage <= 0) {
      $perPage = 10;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    if (!empty($filters['sort']) && $filters['sort'] === 'best_seller') {
      $validStatuses = ['confirmed', 'processing', 'shipping', 'delivered', 'completed'];

      $query->withSum([
        'orderItems as sold_qty' => function ($q) use ($validStatuses) {
          $q->whereHas('order', function ($orderQuery) use ($validStatuses) {
            $orderQuery->where('payment_status', 'paid')
              ->whereIn('status', $validStatuses);
          });
        },
      ], 'quantity')
        ->orderByDesc('sold_qty')
        ->orderByDesc('created_at');
    } else {
      $query->orderByDesc('created_at');
    }

    $products = $query->paginate($perPage);

    return PaginationHelper::appendQuery($products);
  }

  public function listProduct(array $filters = [])
  {
    $query = Product::query()
      ->select(['id', 'title', 'image', 'slug', 'isbn', 'selling_price_vnd', 'status', 'publisher_id', 'created_at'])
      ->with([
        'categories:id,name',
        'authors:id,name',
        'publisher:id,name',
        'stocks:product_id,on_hand,reserved',
      ]);

    if (!empty($filters['keyword'])) {
      $kw = trim((string) $filters['keyword']);
      $query->where(function ($q) use ($kw) {
        $like = "%{$kw}%";

        $q->where('title', 'LIKE', $like)
          ->orWhere('slug', 'LIKE', $like)
          ->orWhere('isbn', 'LIKE', $like)
          ->orWhereHas('authors', function ($qa) use ($like) {
            $qa->where('name', 'LIKE', $like);
          })
          ->orWhereHas('categories', function ($qc) use ($like) {
            $qc->where('name', 'LIKE', $like);
          });
      });
    }

    if (!empty($filters['category_id'])) {
      $categoryId = (string) $filters['category_id'];
      $query->whereHas('categories', function ($q) use ($categoryId) {
        $q->where('categories.id', $categoryId);
      });
    }

    if (!empty($filters['author_id'])) {
      $authorId = (string) $filters['author_id'];
      $query->whereHas('authors', function ($q) use ($authorId) {
        $q->where('authors.id', $authorId);
      });
    }

    if (!empty($filters['publisher_id'])) {
      $query->where('publisher_id', (string) $filters['publisher_id']);
    }

    $priceMin = $filters['price_min'] ?? null;
    if ($priceMin !== null && $priceMin !== '') {
      $query->where('selling_price_vnd', '>=', (int) $priceMin);
    }

    $priceMax = $filters['price_max'] ?? null;
    if ($priceMax !== null && $priceMax !== '') {
      $query->where('selling_price_vnd', '<=', (int) $priceMax);
    }

    $perPage = (int) ($filters['per_page'] ?? 9);
    if ($perPage <= 0) {
      $perPage = 9;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    $sortBy = $filters['sort_by'] ?? 'latest';
    switch ($sortBy) {
      case 'price_asc':
        $query->orderBy('selling_price_vnd', 'asc');
        break;
      case 'price_desc':
        $query->orderBy('selling_price_vnd', 'desc');
        break;
      case 'title_asc':
        $query->orderBy('title', 'asc');
        break;
      case 'title_desc':
        $query->orderBy('title', 'desc');
        break;
      case 'latest':
      default:
        $query->orderByDesc('created_at');
        break;
    }

    $products = $query->paginate($perPage);

    return PaginationHelper::appendQuery($products);
  }

  public function getMaxSellingPrice(): int
  {
    return (int) Product::query()->max('selling_price_vnd');
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

  public function findById(string $id): ?Product
  {
    return Product::with(['categories:id', 'authors:id'])->find($id);
  }

  public function update(Product $product, array $data, ?UploadedFile $image): bool
  {
    $oldImage = $product->image;
    try {
      DB::transaction(function () use ($product, $data, $image, $oldImage) {
        if ($image instanceof UploadedFile) {
          $newName = $image->hashName();
          $image->storeAs('products', $newName, 'public');
          $product->image = $newName;
        }

        $product->title             = $data['title'];
        $product->slug              = $data['slug'];
        $product->code              = $data['code'];
        $product->isbn              = $data['isbn'];
        $product->description       = $data['description'];
        $product->selling_price_vnd = $data['selling_price_vnd'];
        $product->unit              = $data['unit'];
        $product->status            = $data['status'];
        $product->publisher_id      = $data['publisher_id'];
        $product->save();

        $product->categories()->sync($data['categoriesInput']);
        $product->authors()->sync($data['authorsInput']);
        if ($image instanceof UploadedFile && $oldImage && $oldImage !== $product->image) {
          $path = 'products/' . $oldImage;
          if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
          }
        }
      });

      return true;
    } catch (\Throwable $e) {
      if (isset($newName)) {
        $path = 'products/' . $newName;
        if (Storage::disk('public')->exists($path)) {
          Storage::disk('public')->delete($path);
        }
      }
      Log::error('Product update failed', ['msg' => $e->getMessage()]);
      return false;
    }
  }

  public function getProductDetail(string $id): ?Product
  {
    $userId = Auth::id();

    return Product::query()
      ->select('products.*')
      ->selectSub(function ($q) {
        $q->from('reviews')
          ->selectRaw('COUNT(*)')
          ->whereColumn('reviews.product_id', 'products.id')
          ->where('is_active', true);
      }, 'reviews_count')
      ->selectSub(function ($q) {
        $q->from('reviews')
          ->selectRaw('AVG(rating)')
          ->whereColumn('reviews.product_id', 'products.id')
          ->where('is_active', true);
      }, 'rating_avg')
      ->when($userId, function ($q) use ($userId) {
        $q->selectSub(function ($sub) use ($userId) {
          $sub->from('favorites')
            ->selectRaw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END')
            ->whereColumn('favorites.product_id', 'products.id')
            ->where('user_id', $userId);
        }, 'is_favorite');
      }, function ($q) {
        $q->selectRaw('0 as is_favorite');
      })
      ->selectSub(function ($q) {
        $q->from('stocks')
          ->selectRaw('COALESCE(SUM(on_hand - reserved), 0)')
          ->whereColumn('stocks.product_id', 'products.id');
      }, 'quantity_available')
      ->with([
        'categories:id,name,slug',
        'authors:id,name,slug',
        'publisher:id,name,slug',
      ])
      ->find($id);
  }

  public function getRelatedProducts(Product $product, int $perPage = 8): LengthAwarePaginator
  {
    if ($perPage <= 0) {
      $perPage = 4;
    }
    if ($perPage > 200) {
      $perPage = 50;
    }

    $query = Product::query()
      ->select([
        'id',
        'title',
        'image',
        'slug',
        'isbn',
        'selling_price_vnd',
        'status',
        'publisher_id',
        'created_at',
      ])
      ->with([
        'categories:id,name',
        'authors:id,name',
        'publisher:id,name',
        'stocks:product_id,on_hand,reserved',
      ]);

    if (Auth::check()) {
      $userId = Auth::id();
      $query->withExists([
        'favoredBy as is_favorited' => function ($q) use ($userId) {
          $q->where('users.id', $userId);
        },
      ]);
    }

    $query->where('id', '!=', $product->id);

    $product->loadMissing('categories:id,name');

    $categoryIds = $product->categories?->pluck('id')->all() ?? [];
    if (!empty($categoryIds)) {
      $query->whereHas('categories', function ($q) use ($categoryIds) {
        $q->whereIn('categories.id', $categoryIds);
      });
    }

    $query->where('status', 'ACTIVE');
    $validStatuses = ['confirmed', 'processing', 'shipping', 'delivered', 'completed'];

    $query->withSum([
      'orderItems as sold_qty' => function ($q) use ($validStatuses) {
        $q->whereHas('order', function ($orderQuery) use ($validStatuses) {
          $orderQuery->where('payment_status', 'paid')
            ->whereIn('status', $validStatuses);
        });
      },
    ], 'quantity')
      ->orderByDesc('sold_qty')
      ->orderByDesc('created_at');

    $products = $query->paginate($perPage);

    return PaginationHelper::appendQuery($products);
  }

  public function getProductReviews(string $productId, int $perPage = 5): LengthAwarePaginator
  {
    if ($perPage <= 0 || $perPage > 50) {
      $perPage = 4;
    }

    $query = Review::query()
      ->where('product_id', $productId)
      ->where('is_active', true)
      ->with(['user:id,name'])
      ->orderByDesc('created_at');
    $reviews = $query->paginate($perPage)->withQueryString();

    return $reviews;
  }
  public function getRecentlyViewedProducts(array $ids, int $perPage = 9): LengthAwarePaginator
  {
    $ids = array_values(array_filter($ids, function ($id) {
      return is_string($id) && $id !== '';
    }));

    if ($perPage <= 0) {
      $perPage = 9;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    $query = Product::query()
      ->select([
        'id',
        'title',
        'image',
        'slug',
        'isbn',
        'selling_price_vnd',
        'status',
        'publisher_id',
        'created_at',
      ])
      ->with([
        'categories:id,name',
        'authors:id,name',
        'publisher:id,name',
        'stocks:product_id,on_hand,reserved',
      ])
      ->where('status', 'ACTIVE');
    if (empty($ids)) {
      $query->whereRaw('1 = 0');
    } else {
      $query->whereIn('id', $ids);
      $orderExpr = 'CASE';
      $bindings  = [];

      foreach ($ids as $index => $id) {
        $orderExpr .= ' WHEN id = ? THEN ' . (int) $index;
        $bindings[] = $id;
      }

      $orderExpr .= ' END';

      $query->orderByRaw($orderExpr, $bindings);
    }

    $products = $query->paginate($perPage);

    return PaginationHelper::appendQuery($products);
  }
}
