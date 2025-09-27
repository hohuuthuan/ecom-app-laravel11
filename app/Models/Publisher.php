<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Publisher extends Model
{
    use HasUuids;

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function products() { return $this->hasMany(Product::class); }
    public function purchaseReceipts() { return $this->hasMany(PurchaseReceipt::class); }
}
