<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Order extends Model
{
    use HasUuids;

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function user()      { return $this->belongsTo(User::class); }
    public function items()     { return $this->hasMany(OrderItem::class); }
    public function shipment()  { return $this->hasOne(Shipment::class); } // 1 đơn = 1 shipment
    public function payments()  { return $this->hasMany(Payment::class); }
    public function discount()  { return $this->belongsTo(Discount::class); }
}
