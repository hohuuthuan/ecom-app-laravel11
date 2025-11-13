<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
  protected $table = 'provinces';
  public $timestamps = false;
  protected $guarded = [];

  public function wards(): HasMany
  {
    return $this->hasMany(Ward::class, 'province_id', 'id');
  }

  public function addresses(): HasMany
  {
    return $this->hasMany(Address::class, 'address_province_id', 'id');
  }
}
