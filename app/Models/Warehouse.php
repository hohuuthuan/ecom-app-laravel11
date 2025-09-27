<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Warehouse extends Model
{
    use HasUuids;

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function stocks()      { return $this->hasMany(Stock::class); }
    public function batchStocks() { return $this->hasMany(BatchStock::class); }
    public function batches()     { return $this->hasMany(Batch::class); }
    public function stockMovements() { return $this->hasMany(StockMovement::class); }
}
