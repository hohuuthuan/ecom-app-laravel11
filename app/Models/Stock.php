<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $guarded = [];
    public $incrementing = false; // PK composite
    public $timestamps = true;

    protected $primaryKey = null;
    protected $table = 'stocks';
    protected $keyType = 'string';

    public function product()   { return $this->belongsTo(Product::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
}
