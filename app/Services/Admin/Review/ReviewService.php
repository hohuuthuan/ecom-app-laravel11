<?php

namespace App\Services\Admin\Review;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService
{
  // hàm này bạn đã có cho màn index, giữ lại hoặc chỉnh theo DB thực tế
  public function getProductReviewSummary(array $filters = []): LengthAwarePaginator
  {
    $perPage = (int)($filters['per_page'] ?? 10);

    $query = Review::query()
      ->selectRaw(
        'product_id,
         COUNT(*) as total_reviews,
         SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_reviews,
         AVG(rating) as avg_rating'
      )
      ->with([
        'product:id,title,image',
      ])
      ->groupBy('product_id');

    if (!empty($filters['keyword'])) {
      $kw = trim((string)$filters['keyword']);
      $query->whereHas('product', function ($q) use ($kw) {
        $q->where('title', 'LIKE', "%{$kw}%");
      });
    }

    if ($perPage <= 0) {
      $perPage = 10;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    return $query
      ->orderByDesc('inactive_reviews')
      ->orderByDesc('total_reviews')
      ->paginate($perPage)
      ->withQueryString();
  }

  public function getProductWithReviews(string $productId, array $filters = []): array
  {
    $product = Product::query()
      ->select('id', 'title', 'image')
      ->find($productId);

    if (!$product) {
      return [
        'product' => null,
        'stats'   => null,
        'reviews' => null,
      ];
    }

    $stats = Review::query()
      ->selectRaw(
        'COUNT(*) as total_reviews,
         SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_reviews,
         SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_reviews,
         AVG(rating) as avg_rating'
      )
      ->where('product_id', $productId)
      ->first();

    $reviewQuery = Review::query()
      ->where('product_id', $productId)
      ->with(['user:id,name,email']);

    if (!empty($filters['status']) && in_array($filters['status'], ['ACTIVE', 'INACTIVE'], true)) {
      $reviewQuery->where('is_active', $filters['status'] === 'ACTIVE');
    }

    $perPage = (int)($filters['per_page'] ?? 12);
    if ($perPage <= 0) {
      $perPage = 12;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    $reviews = $reviewQuery
      ->orderByDesc('created_at') // mới nhất lên đầu
      ->paginate($perPage)
      ->withQueryString();

    return [
      'product' => $product,
      'stats'   => $stats,
      'reviews' => $reviews,
    ];
  }

  public function updateReviewFromAdmin(string $reviewId, array $data): ?Review
  {
    $review = Review::query()->find($reviewId);
    if (!$review) {
      return null;
    }

    $payload = [];

    if (array_key_exists('reply', $data)) {
      $payload['reply'] = $data['reply'] !== null ? (string)$data['reply'] : null;
    }

    if (!empty($data['status']) && in_array($data['status'], ['ACTIVE', 'INACTIVE'], true)) {
      $payload['is_active'] = $data['status'] === 'ACTIVE';
    }

    if (!$payload) {
      return $review;
    }

    $review->fill($payload);
    $review->save();

    return $review->fresh(['user']);
  }
}
