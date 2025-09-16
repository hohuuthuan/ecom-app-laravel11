<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
  public $incrementing = false;
  protected $keyType = 'string';

  protected $fillable = [
    'id',
    'email',
    'password',
    'name',
    'phone',
    'avatar',
    'status'
  ];

  protected $hidden = ['password', 'remember_token'];

  public function roles()
  {
    return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
  }

  public function hasRole(string $roleName): bool
  {
    return $this->roles()->where('name', $roleName)->exists();
  }

  public function hasAnyRole(string ...$names): bool
  {
    return $this->roles()->whereIn('name', $names)->exists();
  }
}
