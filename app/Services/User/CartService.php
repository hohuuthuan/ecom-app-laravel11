<?php

namespace App\Services\User;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
  private const SSN_KEY = 'cart';

  public function get(): array
  {
    return Session::get(self::SSN_KEY, ['items' => []]);
  }

  public function countDistinct(): int
  {
    $cart = $this->get();
    if (!is_array($cart) || !isset($cart['items']) || !is_array($cart['items'])) {
      return 0;
    }
    return count($cart['items']);
  }

  public function add(string $productId, ?string $variantId, int $qty = 1): array
  {
    if ($qty < 1) {
      $qty = 1;
    }

    $cart = $this->get();
    $key = $this->key($productId, $variantId);
    if (!isset($cart['items'][$key])) {
      $cart['items'][$key] = [
        'product_id' => $productId,
        'variant_id' => $variantId,
        'qty' => 0
      ];
    }
    $cart['items'][$key]['qty'] += $qty;

    Session::put(self::SSN_KEY, $cart);
    return $this->recalc();
  }

  public function updateQuantityItemInCart(string $key, int $qty): array
  {
    $cart = $this->get();
    if (!isset($cart['items'][$key])) {
      return $this->recalc();
    }

    if ($qty < 1) {
      unset($cart['items'][$key]);
    } else {
      $cart['items'][$key]['qty'] = $qty;
    }

    Session::put(self::SSN_KEY, $cart);
    return $this->recalc();
  }

  public function removeItemInCart(string $key): array
  {
    $cart = $this->get();
    unset($cart['items'][$key]);
    Session::put(self::SSN_KEY, $cart);
    return $this->recalc();
  }

  public function clearCart(): void
  {
    Session::forget(self::SSN_KEY);
  }

  public function recalc(): array
  {
    $cart = $this->get();
    if (empty($cart['items'])) {
      $out = ['items' => [], 'subtotal' => 0, 'warnings' => []];
      Session::put(self::SSN_KEY, $out);
      return $out;
    }

    $ids = [];
    foreach ($cart['items'] as $it) {
      $ids[$it['product_id']] = true;
    }

    $products = Product::query()
      ->select('id', 'title', 'image', 'selling_price_vnd')
      ->with('stocks:product_id,on_hand,reserved')
      ->whereIn('id', array_keys($ids))
      ->get()
      ->keyBy('id');

    $subtotal = 0;
    $warnings = [];
    $normalized = [];

    foreach ($cart['items'] as $key => $it) {
      if (!$products->has($it['product_id'])) {
        $warnings[] = 'Sản phẩm không còn tồn tại';
        continue;
      }

      $p = $products[$it['product_id']];
      $available = (int)$p->stocks->sum(fn($s) => (int)$s->on_hand - (int)$s->reserved);
      if ($available <= 0) {
        $warnings[] = 'Hết hàng: ' . $p->title;
        continue;
      }

      $qty = $it['qty'] > $available ? $available : $it['qty'];
      if ($qty < $it['qty']) {
        $warnings[] = 'Giới hạn theo tồn kho: ' . $p->title;
      }

      $price = (int)$p->selling_price_vnd;
      $lineTotal = $price * $qty;

      $normalized[$key] = [
        'key'        => $key,
        'product_id' => $p->id,
        'variant_id' => $it['variant_id'] ?? null,
        'title'      => $p->title,
        'image'      => $p->image,
        'qty'        => $qty,
        'price'      => $price,
        'line_total' => $lineTotal
      ];

      $subtotal += $lineTotal;
    }

    $out = ['items' => $normalized, 'subtotal' => $subtotal, 'warnings' => $warnings];
    Session::put(self::SSN_KEY, $out);
    return $out;
  }

  public function summarize(array $keys): array
  {
    $cart = $this->recalc();
    if (empty($keys)) {
      return ['items' => [], 'subtotal' => 0, 'shipping' => 0, 'total' => 0];
    }

    $items = [];
    $subtotal = 0;
    foreach ($keys as $k) {
      if (!isset($cart['items'][$k])) {
        continue;
      }
      $items[] = $cart['items'][$k];
      $subtotal += (int)$cart['items'][$k]['line_total'];
    }

    $shipping = count($items) > 0 ? 30000 : 0;
    $total = $subtotal + $shipping;

    return ['items' => $items, 'subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $total];
  }

  private function key(string $productId, ?string $variantId): string
  {
    return $variantId ? ($productId . '_' . $variantId) : $productId;
  }
}
