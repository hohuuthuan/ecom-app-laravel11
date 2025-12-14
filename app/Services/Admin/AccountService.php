<?php

namespace App\Services\Admin;

use App\Helpers\PaginationHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                $q->where('name', 'LIKE', "%{$keyword}%")
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

        return PaginationHelper::appendQuery(
            $query->orderBy('created_at', 'desc')->paginate($perPage)
        );
    }

    public function updateAccount(string $id, array $data): bool
    {
        $oldAvatar = null;
        $newAvatar = null;

        try {
            DB::beginTransaction();

            $user = User::query()->lockForUpdate()->findOrFail($id);

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'] ?? null;
            $user->status = $data['status'];

            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $oldAvatar = $user->avatar;

                $fileName = $data['avatar']->hashName();
                $data['avatar']->storeAs('avatars', $fileName, 'public');

                $newAvatar = $fileName;
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
                    'status' => $status,
                    'updated_at' => now(),
                ]);
        });
    }

    public function getStats(array $filters = []): array
    {
        $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now = Carbon::now($tz);

        $topFrom = $now->copy()->subDays(90)->startOfDay();
        $topTo = $now->copy()->endOfDay();

        $totalUsers = User::query()->count();
        $activeUsers = User::query()->where('status', 'ACTIVE')->count();
        $bannedUsers = User::query()->where('status', 'BAN')->count();

        $rows = DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->whereRaw('LOWER(o.payment_status) = ?', ['paid'])
            ->whereRaw('LOWER(o.status) = ?', ['completed'])
            ->whereBetween(DB::raw('coalesce(o.placed_at, o.created_at)'), [$topFrom, $topTo])
            ->selectRaw(
                'u.id,
                 u.name,
                 u.email,
                 u.phone,
                 count(*) as orders_count,
                 coalesce(sum(coalesce(o.grand_total_vnd, 0) - coalesce(o.shipping_fee_vnd, 0)), 0) as total_spent_vnd,
                 coalesce(avg(coalesce(o.grand_total_vnd, 0) - coalesce(o.shipping_fee_vnd, 0)), 0) as avg_order_vnd,
                 max(coalesce(o.placed_at, o.created_at)) as last_order_at'
            )
            ->groupBy('u.id', 'u.name', 'u.email', 'u.phone')
            ->orderByDesc('total_spent_vnd')
            ->limit(10)
            ->get();

        $topCustomers = [];

        foreach ($rows as $row) {
            $topCustomers[] = [
                'id' => (string) $row->id,
                'name' => (string) $row->name,
                'email' => $row->email ? (string) $row->email : null,
                'phone' => $row->phone ? (string) $row->phone : null,
                'orders_count' => (int) ($row->orders_count ?? 0),
                'total_spent_vnd' => (int) ($row->total_spent_vnd ?? 0),
                'avg_order_vnd' => (int) ($row->avg_order_vnd ?? 0),
                'last_order_at' => $row->last_order_at ? (string) $row->last_order_at : null,
            ];
        }

        return [
            'summary' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'banned_users' => $bannedUsers,
            ],
            'top_customers' => $topCustomers,
            'range' => [
                'from' => $topFrom->toDateString(),
                'to' => $topTo->toDateString(),
            ],
        ];
    }
}
