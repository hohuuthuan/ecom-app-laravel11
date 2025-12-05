<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Discount extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'code',
        'type',
        'value',
        'min_order_value_vnd',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'min_order_value_vnd' => 'integer',
        'value' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $discount): void {
            if (!$discount->id) {
                $discount->id = (string) Str::uuid();
            }
            $discount->code = strtoupper($discount->code);
        });
    }

    public function usages()
    {
        return $this->hasMany(DiscountUsage::class);
    }
}
