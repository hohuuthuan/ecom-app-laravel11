<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PurchaseReceipt extends Model
{
    use HasUuids;

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function items()     { return $this->hasMany(PurchaseReceiptItem::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function publisher() { return $this->belongsTo(Publisher::class); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
}
