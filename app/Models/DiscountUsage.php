<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DiscountUsage extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'discount_id',
        'user_id',
        'order_id',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $usage): void {
            if (!$usage->id) {
                $usage->id = (string) Str::uuid();
            }
        });
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
}
