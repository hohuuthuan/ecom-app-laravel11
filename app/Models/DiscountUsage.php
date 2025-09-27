<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DiscountUsage extends Model
{
    use HasUuids;

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function discount() { return $this->belongsTo(Discount::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function order()    { return $this->belongsTo(Order::class); }
}
