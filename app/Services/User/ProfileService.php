<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProfileService
{
  public function updateInfo(int|string $userId, array $data): bool
  {
    return DB::transaction(function () use ($userId, $data): bool {
      $user = User::find($userId);
      if ($user === null) {
        return false;
      }

      $user->name = trim($data['name']);
      $user->email = trim($data['email']);

      if (array_key_exists('phone', $data)) {
        $user->phone = $data['phone'] !== null && $data['phone'] !== '' ? $data['phone'] : null;
      }

      if (array_key_exists('birthday', $data)) {
        $user->birthday = $data['birthday'] !== null && $data['birthday'] !== '' ? $data['birthday'] : null;
      }

      if (array_key_exists('gender', $data)) {
        $user->gender = $data['gender'] !== null && $data['gender'] !== '' ? $data['gender'] : null;
      }

      return $user->save();
    });
  }
}
