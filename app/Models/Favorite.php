<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $table = 'favorites';
    public $incrementing = false;
    protected $primaryKey = null;
    protected $keyType = 'string';
    protected $fillable = ['user_id','product_id'];
    public $timestamps = true;

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}