<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',   
        'product_id',
        'user_id',
        'order_id', 
        'rating',
        'comment',
        'reply',
        'is_active',
        'image',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    protected $appends = [
        'image_url',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(): ?string
    {
      if (!$this->image) {
        return null;
      }

      return Storage::disk('public')->url($this->image);
    }
}
