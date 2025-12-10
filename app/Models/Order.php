<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Order extends Model
{
    use HasUuids;

    protected $casts = [
        'placed_at'    => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function deliveryIssues()
    {
        return $this->hasMany(OrderDeliveryIssue::class);
    }
}
