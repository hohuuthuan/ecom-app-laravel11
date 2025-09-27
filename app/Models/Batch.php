<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Batch extends Model
{
    use HasUuids;

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function product()        { return $this->belongsTo(Product::class); }
    public function warehouse()      { return $this->belongsTo(Warehouse::class); }
    public function receiptItem()    { return $this->belongsTo(PurchaseReceiptItem::class, 'purchase_receipt_item_id'); }
    public function batchStock()     { return $this->hasOne(BatchStock::class); }
    public function orderBatches()   { return $this->hasMany(OrderBatch::class); }
    public function stockMovements() { return $this->hasMany(StockMovement::class); }
}
