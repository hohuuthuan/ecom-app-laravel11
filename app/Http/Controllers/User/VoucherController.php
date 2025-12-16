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
        [$discounts, $savedMap, $userUsedMap, $globalUsedMap] = $service->getVoucherWalletData($request->user(), 9);

        return view('user.voucher_wallet', [
            'discounts'     => $discounts,
            'savedMap'      => $savedMap,
            'userUsedMap'   => $userUsedMap,
            'globalUsedMap' => $globalUsedMap,
        ]);
    }

    public function claim(Request $request, VoucherService $service): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $res = $service->claim($request->user(), (string) $validated['code']);

        return response()->json($res, !empty($res['ok']) ? 200 : 422);
    }

    public function remove(Request $request, VoucherService $service): JsonResponse
    {
        $validated = $request->validate([
            'discount_id' => ['required', 'uuid'],
        ]);

        $res = $service->removeFromWallet($request->user(), (string) $validated['discount_id']);

        return response()->json($res, !empty($res['ok']) ? 200 : 422);
    }
}
