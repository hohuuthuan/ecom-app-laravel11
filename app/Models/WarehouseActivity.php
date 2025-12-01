<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseActivity extends Model
{
  protected $table = 'warehouse_activities';

  protected $fillable = [
    'title',
    'occurred_at',
  ];

  protected $casts = [
    'occurred_at' => 'datetime',
  ];
}
