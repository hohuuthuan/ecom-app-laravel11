<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderBatch extends Pivot
{
    protected $table = 'order_batches';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ['order_item_id','batch_id','quantity','unit_cost_vnd'];
}
