<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request, VoucherService $service): View
    {
        [$discounts, $savedMap, $userUsedMap, $globalUsedMap] = $service->getVoucherCenterData($request->user(), 9);

        return view('user.vouchers', [
            'discounts'     => $discounts,
            'savedMap'      => $savedMap,
            'userUsedMap'   => $userUsedMap,
            'globalUsedMap' => $globalUsedMap,
        ]);
    }

    public function wallet(Request $request, VoucherService $service): View
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        [$discounts, $savedMap, $userUsedMap, $globalUsedMap] = $service->getVoucherWalletData($user, 9);

        return view('user.voucher_wallet', [
            'discounts'     => $discounts,
            'savedMap'      => $savedMap,
            'userUsedMap'   => $userUsedMap,
            'globalUsedMap' => $globalUsedMap,
        ]);
    }

    public function claim(Request $request, VoucherService $service): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'ok'      => false,
                'message' => 'Vui lòng đăng nhập để lưu voucher vào ví.',
            ], 401);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $res = $service->claim($user, (string) $validated['code']);

        return response()->json($res, !empty($res['ok']) ? 200 : 422);
    }

    public function remove(Request $request, VoucherService $service): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'ok'      => false,
                'message' => 'Vui lòng đăng nhập để xóa voucher khỏi ví.',
            ], 401);
        }

        $validated = $request->validate([
            'discount_id' => ['required', 'uuid'],
        ]);

        $res = $service->removeFromWallet($user, (string) $validated['discount_id']);

        return response()->json($res, !empty($res['ok']) ? 200 : 422);
    }
}
