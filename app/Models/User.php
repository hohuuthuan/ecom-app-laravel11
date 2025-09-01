<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
  use Notifiable, HasUuids;

  public $incrementing = false;
  protected $keyType = 'string';

  protected $fillable = [
    'email',
    'password',
    'full_name',
    'phone',
    'address',
    'avatar',
    'status'
  ];

  protected $hidden = ['password', 'remember_token'];

  public function roles()
  {
    return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id')->withTimestamps();
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
