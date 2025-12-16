<?php

namespace App\Services\User;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HomeService
{
  public function getListFavoriteProduct(): Collection
  {
    /** @var User|null $user */
    $user = Auth::user();
    if ($user === null) {
      return collect();
    }

    return $user->favorites()
      ->select([
        'products.id',
        'products.title',
        'products.image',
        'products.slug',
        'products.discount_percent',
        'products.selling_price_vnd',
        'favorites.created_at as favored_at',
      ])
      ->with([
        'authors:id,name',
        'publisher:id,name',
      ])
      ->orderByDesc('favorites.created_at')
      ->get();
  }

  public function addFavoriteProduct(string $productId): bool
  {
    $user = Auth::user();
    if (!$user || !$productId) return false;

    try {
      Favorite::firstOrCreate([
        'user_id'    => $user->id,
        'product_id' => $productId,
      ]);

      return true;
    } catch (\Throwable $e) {
      Log::warning('addFavoriteProduct failed', [
        'user_id' => $user->id ?? null,
        'product_id' => $productId,
        'error' => $e->getMessage(),
      ]);
      return false;
    }
  }

  public function destroyFavoriteProduct(string $productId): int
  {
    $userId = Auth::id();
    if (!$userId || !$productId) return 0;
    return Favorite::where('user_id', $userId)->where('product_id', $productId)->delete();
  }
}
