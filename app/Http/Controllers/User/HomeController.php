<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\HomeService;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class HomeController extends Controller
{
  private readonly HomeService $homeService;

  public function __construct(HomeService $homeService)
  {
    $this->homeService = $homeService;
  }

  public function addFavoriteProduct(Request $request): JsonResponse|RedirectResponse
  {
    try {
      $productId = (string)$request->input('product_id');
      $ok = $this->homeService->addFavoriteProduct($productId);

      if ($request->expectsJson()) {
        $count = 0;
        if (Auth::check()) {
          $count = Favorite::where('user_id', Auth::id())->count();
        }
        if ($ok === true) {
          return response()->json([
            'ok'      => true,
            'message' => 'Đã thêm vào yêu thích',
            'count'   => $count,
          ]);
        } else {
          return response()->json([
            'ok'      => false,
            'message' => 'Thêm vào yêu thích thất bại',
            'count'   => $count,
          ], 422);
        }
      }
      if ($ok === true) {
        return back()->with('toast_success', 'Đã thêm vào danh sách yêu thích');
      } else {
        return back()->with('toast_error', 'Thêm vào yêu thích thất bại');
      }
    } catch (Throwable $e) {
      if ($request->expectsJson()) {
        $count = 0;
        if (Auth::check()) {
          $count = Favorite::where('user_id', Auth::id())->count();
        }

        return response()->json([
          'ok'      => false,
          'message' => 'Có lỗi xảy ra',
          'count'   => $count,
        ], 500);
      }

      return back()->with('toast_error', 'Có lỗi xảy ra');
    }
  }

  public function destroyFavoriteProduct(string $productId, Request $request): JsonResponse|RedirectResponse
  {
    try {
      $deleted = $this->homeService->destroyFavoriteProduct($productId);

      if ($request->expectsJson()) {
        $count = 0;
        if (Auth::check()) {
          $count = Favorite::where('user_id', Auth::id())->count();
        }

        if ($deleted > 0) {
          return response()->json([
            'ok'      => true,
            'message' => 'Đã bỏ thích',
            'count'   => $count,
          ]);
        } else {
          return response()->json([
            'ok'      => false,
            'message' => 'Bỏ thích thất bại',
            'count'   => $count,
          ], 422);
        }
      }

      if ($deleted > 0) {
        return back()->with('toast_success', 'Đã xóa khỏi danh sách yêu thích');
      } else {
        return back()->with('toast_error', 'Xóa yêu thích thất bại');
      }
    } catch (Throwable $e) {
      if ($request->expectsJson()) {
        $count = 0;
        if (Auth::check()) {
          $count = Favorite::where('user_id', Auth::id())->count();
        }

        return response()->json([
          'ok'      => false,
          'message' => 'Có lỗi xảy ra',
          'count'   => $count,
        ], 500);
      }

      return back()->with('toast_error', 'Có lỗi xảy ra');
    }
  }
}
