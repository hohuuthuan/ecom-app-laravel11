<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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

      if (array_key_exists('avatar', $data) && $data['avatar'] instanceof UploadedFile) {
        try {
          $oldAvatar = $user->avatar;
          $fileName = $data['avatar']->hashName();
          $data['avatar']->storeAs('avatars', $fileName, 'public');
          $user->avatar = $fileName;
          if ($oldAvatar && Storage::disk('public')->exists('avatars/' . $oldAvatar)) {
            Storage::disk('public')->delete('avatars/' . $oldAvatar);
          }
        } catch (Throwable $e) {
          Log::error('Avatar upload failed', ['msg' => $e->getMessage()]);
          throw $e;
        }
      }

      return $user->save();
    });
  }
}
