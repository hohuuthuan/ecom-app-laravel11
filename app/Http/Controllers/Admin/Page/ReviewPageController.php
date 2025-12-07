<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Admin\Review\ReviewService;
use Symfony\Component\HttpFoundation\Response;

class ReviewPageController extends Controller
{
  public function __construct(
    private ReviewService $reviewService,
  ) {}

  public function index(Request $r)
  {
    $filters = [
      'per_page' => (int)$r->query('per_page_product', 10),
      'keyword'  => $r->query('keyword'),
    ];

    $productReviewSummary = $this->reviewService->getProductReviewSummary($filters);

    return view('admin.review.index', [
      'productReviewSummary' => $productReviewSummary,
      'filters'              => $filters,
    ]);
  }

  public function show(string $productId, Request $r)
  {
    $filters = [
      'per_page' => (int)$r->query('per_page_review', 12),
      'status'   => $r->query('status'),
    ];

    $data = $this->reviewService->getProductWithReviews($productId, $filters);

    if (!$data['product']) {
      return redirect()
        ->route('admin.review.index')
        ->with('toast_error', 'Sản phẩm không tồn tại hoặc chưa có đánh giá.');
    }

    return view('admin.review.show', [
      'product' => $data['product'],
      'stats'   => $data['stats'],
      'reviews' => $data['reviews'],
      'filters' => $filters,
    ]);
  }

  public function updateReview(Request $r, string $id): JsonResponse
  {
    $validated = $r->validate([
      'reply'  => ['nullable', 'string'],
      'status' => ['required', 'in:ACTIVE,INACTIVE'],
    ]);

    $review = $this->reviewService->updateReviewFromAdmin($id, $validated);
    if (!$review) {
      return response()->json([
        'success' => false,
        'message' => 'Đánh giá không tồn tại.',
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'success' => true,
      'message' => 'Cập nhật đánh giá thành công.',
      'data'    => [
        'id'         => $review->id,
        'status'     => $review->is_active ? 'ACTIVE' : 'INACTIVE',
        'reply'      => $review->reply,
        'updated_at' => optional($review->updated_at)->toDateTimeString(),
      ],
    ]);
  }
}
