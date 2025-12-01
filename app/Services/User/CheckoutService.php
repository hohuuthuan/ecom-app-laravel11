<?php

namespace App\Services\User;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
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

            return $order;
        });
    }
}
