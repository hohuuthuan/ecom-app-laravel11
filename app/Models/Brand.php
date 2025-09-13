<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Brand extends Model {
  use HasUuids;

  protected $table = 'brands';
  protected $fillable = ['name','description','image','slug','status'];
}
