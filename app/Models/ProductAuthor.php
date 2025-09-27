<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductAuthor extends Pivot
{
    protected $table = 'product_authors';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ['product_id','author_id','role'];
}
