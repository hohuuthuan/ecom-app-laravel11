<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderStatusHistory extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'order_id',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrderStatusHistory $history): void {
            if (!$history->id) {
                $history->id = (string) Str::uuid();
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
