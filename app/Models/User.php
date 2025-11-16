<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
  use Notifiable, HasUuids;

  protected $guarded = [];
  public $incrementing = false;
  protected $keyType = 'string';

  protected $hidden = ['password', 'remember_token'];
  protected $casts  = ['email_verified_at' => 'datetime'];

  public function roles(): BelongsToMany
  {
    return $this->belongsToMany(Role::class, 'role_user');
  }
  public function addresses(): HasMany
  {
    return $this->hasMany(Address::class, 'user_id');
  }
  public function reviews()
  {
    return $this->hasMany(Review::class);
  }
  public function shipments()
  {
    return $this->hasMany(Shipment::class, 'courier_id');
  }
  public function purchaseReceipts()
  {
    return $this->hasMany(PurchaseReceipt::class, 'created_by');
  }
  public function stockMovements()
  {
    return $this->hasMany(StockMovement::class, 'created_by');
  }

  public function hasRole(string|array $roles): bool
  {
    $q = $this->roles();
    return is_array($roles)
      ? $q->whereIn('name', $roles)->exists()
      : $q->where('name', $roles)->exists();
  }

  public function assignRole(string|Role $role): void
  {
    $roleId = $role instanceof Role ? $role->id : Role::where('name', $role)->value('id');
    if ($roleId) {
      $this->roles()->syncWithoutDetaching([$roleId]);
    }
  }

  public function favorites()
  {
    return $this->belongsToMany(\App\Models\Product::class, 'favorites', 'user_id', 'product_id')
      ->withTimestamps();
  }
}
