<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
  use HasUuids;

  protected $table = 'user_addresses';
  protected $guarded = [];
  public $incrementing = false;
  protected $keyType = 'string';
  protected $casts = [
    'default' => 'boolean',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function province(): BelongsTo
  {
    return $this->belongsTo(Province::class, 'address_province_id', 'id');
  }

  public function ward(): BelongsTo
  {
    return $this->belongsTo(Ward::class, 'address_ward_id', 'id');
  }
}
