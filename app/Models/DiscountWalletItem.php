<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DiscountWalletItem extends Model
{
    use HasFactory;

    protected $table = 'discount_wallet_items';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'discount_id',
        'user_id',
        'status',
        'saved_at',
        'used_at',
        'removed_at',
    ];

    protected $casts = [
        'saved_at'   => 'datetime',
        'used_at'    => 'datetime',
        'removed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m): void {
            if (!$m->id) {
                $m->id = (string) Str::uuid();
            }
        });
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
