<?php

namespace App\Services\Admin\Order;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;

class OrderStatusService
{
  public function changeStatus(Order $order, string $newStatus): void
  {
    $status = $this->normalizeStatus($newStatus);
    $now = now(config('app.timezone', 'Asia/Ho_Chi_Minh'));

    DB::transaction(function () use ($order, $status, $now): void {
      $order->status = $status;

      if ($status === 'CANCELLED' && $order->cancelled_at === null) {
        $order->cancelled_at = $now;
      }

      $order->save();

      OrderStatusHistory::create([
        'order_id' => $order->id,
        'status'   => $status,
      ]);
    });
  }

  /**
   * @param array<int, string> $orderIds
   * @return array{updated:int, skipped:int, total:int}
   */
  public function bulkChangeStatus(array $orderIds, string $newStatus): array
  {
    $status = $this->normalizeStatus($newStatus);
    $ids = array_values(array_unique(array_filter($orderIds, function ($value) {
      return is_string($value) && trim($value) !== '';
    })));
    $now = now(config('app.timezone', 'Asia/Ho_Chi_Minh'));

    $updated = 0;
    $skipped = 0;

    $orders = Order::query()
      ->whereIn('id', $ids)
      ->get(['id', 'status', 'cancelled_at']);

    DB::transaction(function () use ($orders, $status, $now, &$updated, &$skipped): void {
      foreach ($orders as $order) {
        $current = $this->normalizeStatus((string) $order->status);

        if ($current === $status) {
          $skipped++;
          continue;
        }

        if (!$this->canTransition($current, $status)) {
          $skipped++;
          continue;
        }

        $order->status = $status;

        if ($status === 'CANCELLED' && $order->cancelled_at === null) {
          $order->cancelled_at = $now;
        }

        $order->save();

        OrderStatusHistory::create([
          'order_id' => $order->id,
          'status'   => $status,
        ]);

        $updated++;
      }
    });

    return [
      'updated' => $updated,
      'skipped' => $skipped + (count($ids) - $orders->count()),
      'total'   => count($ids),
    ];
  }

  private function normalizeStatus(string $status): string
  {
    return strtoupper(trim($status));
  }

  private function canTransition(string $current, string $target): bool
  {
    if ($current === $target) {
      return true;
    }

    if ($current === 'PENDING') {
      return in_array($target, ['PROCESSING', 'CANCELLED'], true);
    }

    if ($current === 'PROCESSING') {
      return $target === 'CANCELLED';
    }

    return false;
  }
}
