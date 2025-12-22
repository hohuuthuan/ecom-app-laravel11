<?php

namespace App\Services\User;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\DiscountUsage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function getBuyerInfo(User $user): array
    {
        $name = trim((string) $user->name);
        $phone = $this->normalizePhone((string) ($user->phone ?? ''));

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Vui lòng cập nhật họ và tên để đặt hàng.';
        }
        if ($phone === null) {
            $errors['phone'] = 'Vui lòng cập nhật số điện thoại hợp lệ để đặt hàng.';
        }

        return [
            'name'   => $name,
            'phone'  => $phone,
            'errors' => $errors,
        ];
    }

    protected function normalizePhone(string $phone): ?string
    {
        $raw = trim($phone);
        if ($raw === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9+]/', '', $raw);
        if ($normalized === null || $normalized === '') {
            return null;
        }

        if (preg_match('/^84\d{9,10}$/', $normalized) === 1) {
            $normalized = '+' . $normalized;
        }

        if (
            preg_match('/^0\d{9,10}$/', $normalized) === 1
            || preg_match('/^\+84\d{9,10}$/', $normalized) === 1
        ) {
            return $normalized;
        }

        return null;
    }

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
