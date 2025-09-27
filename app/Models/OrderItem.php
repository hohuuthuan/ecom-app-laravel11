<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OrderItem extends Model
{
    use HasUuids;

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function order()     { return $this->belongsTo(Order::class); }
    public function product()   { return $this->belongsTo(Product::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'order_batches')
            ->using(OrderBatch::class)
            ->withPivot(['quantity','unit_cost_vnd']);
    }
}
