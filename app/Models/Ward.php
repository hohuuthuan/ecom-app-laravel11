<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
  protected $table = 'wards';
  public $timestamps = false;
  protected $guarded = [];

  public function province(): BelongsTo
  {
    return $this->belongsTo(Province::class, 'province_id', 'id');
  }

  public function addresses(): HasMany
  {
    return $this->hasMany(Address::class, 'address_ward_id', 'id');
  }
}
