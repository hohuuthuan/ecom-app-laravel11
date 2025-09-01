<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Helpers\PaginationHelper;

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
}
