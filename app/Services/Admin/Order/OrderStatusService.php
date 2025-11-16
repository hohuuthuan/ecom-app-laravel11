<?php

namespace App\Services\Admin\Order;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;

class OrderStatusService
{
  public function changeStatus(Order $order, string $newStatus): void
  {
    DB::transaction(function () use ($order, $newStatus): void {
      $order->status = $newStatus;
      $order->save();

      OrderStatusHistory::create([
        'order_id' => $order->id,
        'status'   => $newStatus,
      ]);
    });
  }
}
