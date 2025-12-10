<?php

namespace App\Services\Admin\Order;

use App\Models\Order;
use App\Models\OrderDeliveryIssue;
use Carbon\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderDeliveryIssueService
{
    public function createIssueForOrder(
        Order $order,
        string $issueType,
        string $reason
    ): OrderDeliveryIssue {
        if (!in_array($issueType, [
            OrderDeliveryIssue::ISSUE_TYPE_DELIVERY_FAILED,
            OrderDeliveryIssue::ISSUE_TYPE_RETURNED,
        ], true)) {
            throw new InvalidArgumentException('Loại sự cố không hợp lệ.');
        }

        $status = strtoupper((string) $order->status);

        if (!in_array($status, ['DELIVERY_FAILED', 'RETURNED'], true)) {
            throw new InvalidArgumentException('Trạng thái đơn không hợp lệ để ghi nhận sự cố giao hàng / hoàn trả.');
        }

        $paymentMethod = (string) $order->payment_method;

        $grandTotal = (int) $order->grand_total_vnd;
        if ($grandTotal < 0) {
            $grandTotal = 0;
        }

        $shippingFee = (int) ($order->shipping_fee_vnd ?? 30000);
        if ($shippingFee < 0) {
            $shippingFee = 0;
        }

        $refundAmount = 0;
        $lostShippingFee = 0;

        if ($paymentMethod === 'cod') {
            $lostShippingFee = $shippingFee;
        } elseif (in_array($paymentMethod, ['momo', 'vnpay'], true)) {
            $refundAmount = $grandTotal - $shippingFee;
            if ($refundAmount < 0) {
                $refundAmount = 0;
            }
        }

        return OrderDeliveryIssue::create([
            'id'                       => (string) Str::uuid(),
            'order_id'                 => $order->id,
            'issue_type'               => $issueType,
            'reason'                   => $reason,
            'order_payment_method'     => $paymentMethod,
            'order_grand_total_vnd'    => $grandTotal,
            'order_shipping_fee_vnd'   => $shippingFee,
            'refund_amount_vnd'        => $refundAmount,
            'lost_shipping_fee_vnd'    => $lostShippingFee,
            'is_refunded'              => false,
            'refunded_at'              => null,
            'issued_at'                => Carbon::now(config('app.timezone', 'Asia/Ho_Chi_Minh')),
        ]);
    }

    public function markAsRefunded(OrderDeliveryIssue $issue): void
    {
        if ($issue->is_refunded) {
            return;
        }

        if ($issue->refund_amount_vnd <= 0) {
            return;
        }

        $issue->is_refunded = true;
        $issue->refunded_at = Carbon::now(config('app.timezone', 'Asia/Ho_Chi_Minh'));
        $issue->save();
    }
}
