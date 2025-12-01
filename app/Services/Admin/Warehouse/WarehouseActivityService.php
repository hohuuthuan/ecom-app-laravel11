<?php

namespace App\Services\Admin\Warehouse;

use App\Models\WarehouseActivity;
use Illuminate\Support\Carbon;

class WarehouseActivityService
{
  public static function log(string $title, ?Carbon $occurredAt = null): void
  {
    WarehouseActivity::create([
      'title' => $title,
      'occurred_at' => $occurredAt ?? now(),
    ]);
  }

  public function getTodayActivityList(array $filters = [])
  {
    $query = WarehouseActivity::query()
      ->select([
        'id',
        'title',
        'occurred_at',
        'created_at',
      ])
      ->whereDate('occurred_at', now()->toDateString());

    $perPage = (int)($filters['per_page'] ?? 10);
    if ($perPage <= 0) {
      $perPage = 10;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    $activities = $query
      ->orderByDesc('occurred_at')
      ->orderByDesc('created_at')
      ->paginate($perPage);

    return \App\Helpers\PaginationHelper::appendQuery($activities);
  }
}
