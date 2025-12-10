<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeliveryIssue extends Model
{
    public const ISSUE_TYPE_DELIVERY_FAILED = 'DELIVERY_FAILED';
    public const ISSUE_TYPE_RETURNED = 'RETURNED';

    protected $table = 'order_delivery_issues';

    protected $fillable = [
        'id',
        'order_id',
        'issue_type',
        'reason',
        'order_payment_method',
        'order_grand_total_vnd',
        'order_shipping_fee_vnd',
        'refund_amount_vnd',
        'lost_shipping_fee_vnd',
        'is_refunded',
        'refunded_at',
        'issued_at',
    ];

    protected $casts = [
        'order_grand_total_vnd' => 'integer',
        'order_shipping_fee_vnd' => 'integer',
        'refund_amount_vnd' => 'integer',
        'lost_shipping_fee_vnd' => 'integer',
        'is_refunded' => 'boolean',
        'refunded_at' => 'datetime',
        'issued_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
