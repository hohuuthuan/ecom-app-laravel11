<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class UserOrderController extends Controller
{
  public function show(string $id): View
  {
    $userId = Auth::id();
    if ($userId === null) {
      abort(Response::HTTP_FORBIDDEN);
    }

    $order = Order::with([
      'user',
      'items.product',
      'shipment',
      'discount',
      'statusHistories' => function ($query) {
        $query->orderBy('created_at', 'desc');
      },
    ])
      ->where('id', $id)
      ->where('user_id', $userId) // CHỈ LẤY ĐƠN CỦA USER HIỆN TẠI
      ->firstOrFail();

    // Tính tiền cho view user.orderDetail.blade.php
    $subtotal = (int) ($order->subtotal_vnd ?? $order->subtotal ?? 0);
    $shipping = (int) ($order->shipping_fee_vnd ?? $order->shipping_fee ?? 0);
    $discountAmount = (int) ($order->discount_amount_vnd ?? $order->discount_amount ?? 0);
    $grandTotal = (int) ($order->grand_total_vnd ?? $order->grand_total ?? 0);

    // Nếu không có subtotal trong DB thì cộng từ items
    if ($subtotal === 0 && $order->relationLoaded('items')) {
      $subtotal = 0;
      foreach ($order->items as $item) {
        $qty = (int) ($item->quantity ?? 0);
        $unitPrice = (int) ($item->unit_price_vnd ?? $item->unit_price ?? 0);
        $lineTotal = (int) ($item->line_total_vnd ?? $item->total_price_vnd ?? ($qty * $unitPrice));
        $subtotal += $lineTotal;
      }
    }

    // Nếu chưa có grandTotal thì tự tính
    if ($grandTotal === 0) {
      $grandTotal = $subtotal + $shipping - $discountAmount;
      if ($grandTotal < 0) {
        $grandTotal = 0;
      }
    }

    return view('user.orderDetail', [
      'order'          => $order,
      'subtotal'       => $subtotal,
      'shipping'       => $shipping,
      'discountAmount' => $discountAmount,
      'grandTotal'     => $grandTotal,
    ]);
  }

  public function reorder(string $id, Request $request): RedirectResponse
  {
    $userId = Auth::id();
    if ($userId === null) {
      abort(Response::HTTP_FORBIDDEN);
    }
    $order = Order::with(['items.product'])
      ->where('id', $id)
      ->where('user_id', $userId)
      ->firstOrFail();

    $pairs = [];

    foreach ($order->items as $item) {
      $product = $item->product;
      if (!$product) {
        continue;
      }

      $status = strtoupper((string) $product->status);
      if ($status !== 'ACTIVE') {
        continue;
      }

      $qty = (int) ($item->quantity ?? 0);
      if ($qty <= 0) {
        continue;
      }

      $pairs[] = [
        'id'  => (string) $product->id, // UUID
        'qty' => $qty,
      ];
    }

    if (count($pairs) === 0) {
      return redirect()
        ->back()
        ->with('toast_error', 'Không có sản phẩm hợp lệ để mua lại trong đơn này');
    }

    // Ghi vào session checkout giống flow hiện tại
    $request->session()->put('checkout.items', $pairs);
    $request->session()->put('checkout.expires_at', now()->addMinutes(15)->timestamp);
    // Xoá mã giảm giá cũ (nếu có) để tránh áp dụng nhầm
    $request->session()->forget('checkout_discount');

    return redirect()
      ->route('checkout.page')
      ->with('toast_success', 'Đã chuẩn bị lại đơn hàng, hãy kiểm tra và thanh toán');
  }
}
