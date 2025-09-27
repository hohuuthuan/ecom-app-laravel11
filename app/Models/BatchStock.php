<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchStock extends Model
{
    protected $guarded = [];
    public $incrementing = false; // PK batch_id
    protected $primaryKey = 'batch_id';
    protected $keyType = 'string';
    protected $table = 'batch_stocks';

    public function batch()     { return $this->belongsTo(Batch::class); }
    public function product()   { return $this->belongsTo(Product::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
}
