<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class Product extends Model
{
    use HasUuids;

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'product_authors')->using(ProductAuthor::class)->withPivot('role');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories')->using(ProductCategory::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
    public function batchStocks()
    {
        return $this->hasMany(BatchStock::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function favoredBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id')
            ->withTimestamps();
    }
}
