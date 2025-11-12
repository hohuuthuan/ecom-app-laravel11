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
      if ($data['default']) {
        Address::where('user_id', $userId)->update(['default' => false]);
      }

      $newAddress = Address::create([
        'user_id'              => $userId,
        'address'              => (string) $data['address'],
        'address_ward_id'      => (int) $data['address_ward_id'],
        'address_province_id'  => (int) $data['address_province_id'],
        'note'                 => $data['note'] ?? '',
        'default'              => !empty($data['default']),
      ]);

      return (bool) $newAddress;
    } catch (\Throwable $e) {
      Log::error('Address create failed', ['msg' => $e->getMessage()]);
      return false;
    }
  }

  public function update(string $id, array $data): ?Address
  {
    $user = Auth::user();
    if ($user === null) {
      return null;
    }

    try {
      return DB::transaction(function () use ($user, $id, $data) {
        $row = Address::where('user_id', $user->id)->where('id', $id)->first();
        if ($row === null) {
          return null;
        }

        if (Address::where('user_id', $user->id)
          ->where('address', $data['address'])
          ->where('id', '!=', $id)
          ->exists()
        ) {
          return null;
        }

        if (!empty($data['default'])) {
          Address::where('user_id', $user->id)->update(['default' => false]);
          $row->default = true;
        } elseif (array_key_exists('default', $data)) {
          $row->default = (bool) $data['default'];
        }

        $row->address = $data['address'];
        $row->address_ward_id = (int) $data['address_ward_id'];
        $row->address_province_id = (int) $data['address_province_id'];
        $row->note = $data['note'] ?? null;
        $row->save();

        return $row->refresh();
      });
    } catch (\Throwable $e) {
      Log::warning('AddressService.update failed', [
        'user_id' => $user->id ?? null,
        'id'      => $id,
        'payload' => $data,
        'error'   => $e->getMessage(),
      ]);
      return null;
    }
  }

  public function destroy(string $id): int
  {
    $userId = Auth::id();
    if (!$userId) {
      return 0;
    }

    try {
      return Address::where('user_id', $userId)->where('id', $id)->delete();
    } catch (\Throwable $e) {
      Log::warning('AddressService.destroy failed', [
        'user_id' => $userId,
        'id'      => $id,
        'error'   => $e->getMessage(),
      ]);
      return 0;
    }
  }

  public function setDefault(string $id): bool
  {
    $userId = Auth::id();
    if (!$userId) {
      return false;
    }

    try {
      return DB::transaction(function () use ($userId, $id) {
        $row = Address::where('user_id', $userId)->where('id', $id)->first();
        if ($row === null) {
          return false;
        }

        Address::where('user_id', $userId)->update(['default' => false]);
        $row->default = true;
        $row->save();

        return true;
      });
    } catch (\Throwable $e) {
      Log::warning('AddressService.setDefault failed', [
        'user_id' => $userId,
        'id'      => $id,
        'error'   => $e->getMessage(),
      ]);
      return false;
    }
  }
}
