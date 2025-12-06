<?php

namespace App\Services\User;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\DiscountUsage;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function placeCodOrder(
        array $orderData,
        array $orderItemsData,
        array $shipmentData
    ): ?Order {
        return DB::transaction(function () use ($orderData, $orderItemsData, $shipmentData) {
            /** @var Order $order */
            $order = Order::query()->create($orderData);

            if (!empty($orderItemsData)) {
                OrderItem::query()->insert($orderItemsData);
            }

            Shipment::query()->create($shipmentData);

            if (!empty($orderData['discount_id']) && !empty($orderData['user_id'])) {
                DiscountUsage::query()->create([
                    'discount_id' => $orderData['discount_id'],
                    'user_id'     => $orderData['user_id'],
                    'order_id'    => $order->id,
                    'used_at'     => now(),
                ]);
            }

            return $order;
        });
    }
}
