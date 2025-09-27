<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Author extends Model
{
    use HasUuids;

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_authors')->using(ProductAuthor::class)->withPivot('role');
    }
}
