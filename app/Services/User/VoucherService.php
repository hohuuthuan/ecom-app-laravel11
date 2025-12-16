<?php

namespace App\Services\User;

use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\DiscountWalletItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VoucherService
{
    public function getVoucherCenterData(User $user, int $perPage = 9): array
    {
        $now = now();

        /** @var LengthAwarePaginator $discounts */
        $discounts = Discount::query()
            ->where('status', 'ACTIVE')
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $ids = $discounts->getCollection()->pluck('id')->all();
        if (count($ids) === 0) {
            return [$discounts, [], [], []];
        }

        $savedIds = DiscountWalletItem::query()
            ->where('user_id', $user->id)
            ->where('status', 'SAVED')
            ->whereIn('discount_id', $ids)
            ->pluck('discount_id')
            ->all();

        $userUsedMap = DiscountUsage::query()
            ->selectRaw('discount_id, COUNT(*) as cnt')
            ->where('user_id', $user->id)
            ->whereIn('discount_id', $ids)
            ->groupBy('discount_id')
            ->pluck('cnt', 'discount_id')
            ->all();

        $globalUsedMap = DiscountUsage::query()
            ->selectRaw('discount_id, COUNT(*) as cnt')
            ->whereIn('discount_id', $ids)
            ->groupBy('discount_id')
            ->pluck('cnt', 'discount_id')
            ->all();

        return [$discounts, array_fill_keys($savedIds, true), $userUsedMap, $globalUsedMap];
    }

    public function getVoucherWalletData(User $user, int $perPage = 9): array
    {
        /** @var LengthAwarePaginator $discounts */
        $discounts = Discount::query()
            ->join('discount_wallet_items as w', 'w.discount_id', '=', 'discounts.id')
            ->where('w.user_id', $user->id)
            ->where('w.status', 'SAVED')
            ->select('discounts.*')
            ->orderByDesc('w.saved_at')
            ->paginate($perPage)
            ->withQueryString();

        $ids = $discounts->getCollection()->pluck('id')->all();
        if (count($ids) === 0) {
            return [$discounts, [], [], []];
        }

        $userUsedMap = DiscountUsage::query()
            ->selectRaw('discount_id, COUNT(*) as cnt')
            ->where('user_id', $user->id)
            ->whereIn('discount_id', $ids)
            ->groupBy('discount_id')
            ->pluck('cnt', 'discount_id')
            ->all();

        $globalUsedMap = DiscountUsage::query()
            ->selectRaw('discount_id, COUNT(*) as cnt')
            ->whereIn('discount_id', $ids)
            ->groupBy('discount_id')
            ->pluck('cnt', 'discount_id')
            ->all();

        return [$discounts, array_fill_keys($ids, true), $userUsedMap, $globalUsedMap];
    }

    public function claim(User $user, string $code): array
    {
        $normalized = strtoupper(trim($code));
        if ($normalized === '') {
            return ['ok' => false, 'message' => 'Mã giảm giá không hợp lệ.'];
        }

        $discount = Discount::query()
            ->where('code', $normalized)
            ->first();

        if (!$discount) {
            return ['ok' => false, 'message' => 'Mã giảm giá không tồn tại.'];
        }

        if (strtoupper((string) $discount->status) !== 'ACTIVE') {
            return ['ok' => false, 'message' => 'Voucher hiện không khả dụng.'];
        }

        $now = now();

        if ($discount->start_date && $discount->start_date->gt($now)) {
            return ['ok' => false, 'message' => 'Voucher chưa đến thời gian phát hành.'];
        }

        if ($discount->end_date && $discount->end_date->lt($now)) {
            return ['ok' => false, 'message' => 'Voucher đã hết hạn.'];
        }

        if ($discount->usage_limit !== null) {
            $globalUsed = DiscountUsage::query()
                ->where('discount_id', $discount->id)
                ->count();

            if ($globalUsed >= (int) $discount->usage_limit) {
                return ['ok' => false, 'message' => 'Voucher đã hết lượt sử dụng.'];
            }
        }

        if ($discount->per_user_limit !== null) {
            $userUsed = DiscountUsage::query()
                ->where('discount_id', $discount->id)
                ->where('user_id', $user->id)
                ->count();

            if ($userUsed >= (int) $discount->per_user_limit) {
                return ['ok' => false, 'message' => 'Bạn đã dùng voucher này tối đa số lần cho phép.'];
            }
        }

        $wallet = DiscountWalletItem::query()
            ->where('discount_id', $discount->id)
            ->where('user_id', $user->id)
            ->first();

        if ($wallet && strtoupper((string) $wallet->status) === 'SAVED') {
            return ['ok' => true, 'message' => 'Voucher đã có trong ví.', 'saved' => true];
        }

        DiscountWalletItem::query()->updateOrCreate(
            [
                'discount_id' => $discount->id,
                'user_id'     => $user->id,
            ],
            [
                'status'            => 'SAVED',
                'saved_at'          => now(),
                'reserved_order_id' => null,
                'reserved_at'       => null,
            ]
        );

        return ['ok' => true, 'message' => 'Đã lưu voucher vào ví.', 'saved' => true];
    }

    public function removeFromWallet(User $user, string $discountId): array
    {
        $wallet = DiscountWalletItem::query()
            ->where('discount_id', $discountId)
            ->where('user_id', $user->id)
            ->first();

        if (!$wallet || strtoupper((string) $wallet->status) !== 'SAVED') {
            return ['ok' => true, 'message' => 'Voucher không còn trong ví.'];
        }

        $wallet->update([
            'status'            => 'REMOVED',
            'reserved_order_id' => null,
            'reserved_at'       => null,
        ]);

        return ['ok' => true, 'message' => 'Đã xóa voucher khỏi ví.'];
    }
}
