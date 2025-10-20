<?php

namespace App\Services\Admin\Order;

use App\Models\Order;
use Carbon\Carbon;
use App\Helpers\PaginationHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService
{
  public function getList(array $filters = []): LengthAwarePaginator
  {
    $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');

    $query = Order::query()
      ->select([
        'id',
        'code',
        'user_id',
        'status',
        'payment_method',
        'payment_status',
        'items_count',
        'grand_total_vnd',
        'placed_at',
        'created_at'
      ])
      ->with([
        'shipment:id,order_id,name,phone'
      ]);

    if (!empty($filters['keyword'])) {
      $kw = trim((string)$filters['keyword']);
      $query->where(function ($q) use ($kw) {
        $q->where('code', 'LIKE', "%{$kw}%");
      });
    }
    if (!empty($filters['payment_method'])) {
      $query->where('payment_method', (string)$filters['payment_method']);
    }
    if (!empty($filters['payment_status'])) {
      $query->where('payment_status', (string)$filters['payment_status']);
    }
    if (!empty($filters['status'])) {
      $query->where('status', (string)$filters['status']);
    }
    if (!empty($filters['created_from'])) {
      $fromUtc = \Carbon\Carbon::createFromFormat('Y-m-d', (string)$filters['created_from'], $tz)
        ->startOfDay()->utc();
      $query->where('placed_at', '>=', $fromUtc);
    }
    if (!empty($filters['created_to'])) {
      $toUtc = \Carbon\Carbon::createFromFormat('Y-m-d', (string)$filters['created_to'], $tz)
        ->endOfDay()->utc();
      $query->where('placed_at', '<=', $toUtc);
    }

    $perPage = (int)($filters['per_page'] ?? 10);
    if ($perPage <= 0) {
      $perPage = 10;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    $orders = $query->orderByDesc('placed_at')->paginate($perPage);

    return PaginationHelper::appendQuery($orders);
  }
}
