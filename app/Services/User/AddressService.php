<?php

namespace App\Services\User;

use App\Models\Address;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddressService
{
  public function getList(): Collection
  {
    $user = Auth::user();
    if ($user === null) {
      return collect();
    }

    return Address::query()
      ->where('user_id', $user->id)
      ->with(['province:id,name', 'ward:id,province_id,name'])
      ->orderByDesc('default')
      ->orderByDesc('created_at')
      ->get();
  }

  public function create(array $data): bool
  {
    try {
      $userId = Auth::id();
      $isDefault = !empty($data['default']);

      if ($isDefault) {
        Address::where('user_id', $userId)->update(['default' => false]);
      }

      $newAddress = Address::create([
        'user_id'             => $userId,
        'address'             => (string) $data['address'],
        'address_ward_id'     => (int) $data['address_ward_id'],
        'address_province_id' => (int) $data['address_province_id'],
        'note'                => $data['note'] ?? '',
        'default'             => $isDefault,
      ]);

      return $newAddress instanceof Address;
    } catch (\Throwable $e) {
      Log::error('Address create failed', ['msg' => $e->getMessage()]);
      return false;
    }
  }

  public function update(string $id, array $data): bool
  {
    try {
      $userId = Auth::id();

      $address = Address::where('user_id', $userId)
        ->where('id', $id)
        ->first();

      if ($address === null) {
        return false;
      }

      $isDefault = !empty($data['default']);

      if ($isDefault) {
        Address::where('user_id', $userId)
          ->where('id', '!=', $address->id)
          ->update(['default' => false]);
      }

      $address->address             = (string) $data['address'];
      $address->address_ward_id     = (int) $data['address_ward_id'];
      $address->address_province_id = (int) $data['address_province_id'];
      $address->note                = $data['note'] ?? '';
      $address->default             = $isDefault;

      return $address->save();
    } catch (\Throwable $e) {
      Log::error('Address update failed', ['msg' => $e->getMessage()]);
      return false;
    }
  }

  public function destroy(string $id): bool
  {
    try {
      $userId = Auth::id();

      $deleted = Address::where('user_id', $userId)
        ->where('id', $id)
        ->delete();

      return $deleted > 0;
    } catch (\Throwable $e) {
      Log::error('Address destroy failed', ['msg' => $e->getMessage()]);
      return false;
    }
  }

  public function setDefault(string $id): bool
  {
    try {
      $userId = Auth::id();

      $address = Address::where('user_id', $userId)
        ->where('id', $id)
        ->first();

      if ($address === null) {
        return false;
      }

      DB::transaction(function () use ($userId, $address) {
        Address::where('user_id', $userId)->update(['default' => false]);
        $address->default = true;
        $address->save();
      });

      return true;
    } catch (\Throwable $e) {
      Log::error('Address setDefault failed', ['msg' => $e->getMessage()]);
      return false;
    }
  }
}
