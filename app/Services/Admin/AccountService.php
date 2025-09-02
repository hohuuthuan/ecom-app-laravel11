<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Role;
use App\Helpers\PaginationHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Storage;

class AccountService
{
  public function getList(array $filters = [])
  {
    $query = User::query()->with(['roles' => function ($q) {
      $q->orderBy('name', 'asc');
    }]);
    if (!empty($filters['keyword'])) {
      $keyword = $filters['keyword'];
      $query->where(function ($q) use ($keyword) {
        $q->where('full_name', 'LIKE', "%{$keyword}%")
          ->orWhere('email', 'LIKE', "%{$keyword}%")
          ->orWhere('phone', 'LIKE', "%{$keyword}%");
      });
    }
    if (!empty($filters['role_id'])) {
      $query->whereHas('roles', function ($q) use ($filters) {
        $q->where('roles.id', $filters['role_id']);
      });
    }
    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    $perPage = $filters['per_page'] ?? 10;
    $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

    return PaginationHelper::appendQuery($users);
  }

  public function updateAccount(string $id, array $data): bool
  {
    $oldAvatar = null;
    $newAvatar = null;
    try {
      DB::beginTransaction();
      $user = User::query()->lockForUpdate()->findOrFail($id);
      $user->full_name = $data['full_name'];
      $user->email     = $data['email'];
      $user->phone     = $data['phone'] ?? null;
      $user->address   = $data['address'] ?? null;
      $user->status    = $data['status'];
      if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
        $oldAvatar = $user->avatar;
        $newAvatar = $data['avatar']->store('avatars', 'public');
        $user->avatar = $newAvatar;
      }
      $user->save();

      $roleIds = isset($data['role_ids']) ? array_values(array_unique($data['role_ids'])) : [];
      $user->roles()->sync($roleIds);

      DB::commit();
      if ($oldAvatar && $oldAvatar !== $newAvatar && Storage::disk('public')->exists($oldAvatar)) {
        Storage::disk('public')->delete($oldAvatar);
      }

      return true;
    } catch (\Throwable $e) {
      DB::rollBack();
      if ($newAvatar && Storage::disk('public')->exists($newAvatar)) {
        Storage::disk('public')->delete($newAvatar);
      }
      Log::error('Account update failed', ['id' => $id, 'msg' => $e->getMessage()]);
      return false;
    }
  }

  public function bulkUpdateStatus(array $ids, string $status): int
  {
    return DB::transaction(function () use ($ids, $status) {
      return User::query()
        ->whereIn('id', $ids)
        ->update([
          'status'     => $status,
          'updated_at' => now(),
        ]);
    });
  }
}
