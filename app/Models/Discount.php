<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'code',
        'type',
        'value',
        'min_order_value_vnd',
        'usage_limit',
        'per_user_limit',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'min_order_value_vnd' => 'integer',
        'value' => 'integer',
        'usage_limit' => 'integer',
        'per_user_limit' => 'integer',
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
