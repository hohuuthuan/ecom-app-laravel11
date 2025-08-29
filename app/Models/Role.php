<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
  use HasUuids;

  public $incrementing = false;
  protected $keyType = 'string';
  protected $fillable = ['name','description'];

  public function users() {
    return $this->belongsToMany(User::class, 'user_role', 'role_id', 'user_id')->withTimestamps();
  }
}
