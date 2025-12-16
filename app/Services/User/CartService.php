<?php

namespace App\Services\User;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
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
            return $this->recalc();
        }

        $cart = $this->get();
        $key = $this->key($productId, $variantId);

        if (!isset($cart['items'][$key])) {
            $cart['items'][$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'qty' => 0,
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

        if (empty($cart['items']) || !is_array($cart['items'])) {
            $out = ['items' => [], 'subtotal' => 0, 'warnings' => []];
            Session::put(self::SSN_KEY, $out);
            return $out;
        }

        $productIds = [];
        foreach ($cart['items'] as $it) {
            if (!empty($it['product_id'])) {
                $productIds[(string) $it['product_id']] = true;
            }
        }

        if (count($productIds) === 0) {
            $out = ['items' => [], 'subtotal' => 0, 'warnings' => []];
            Session::put(self::SSN_KEY, $out);
            return $out;
        }

        $productIdList = array_keys($productIds);

        $products = Product::query()
            ->select('id', 'title', 'image', 'selling_price_vnd')
            ->whereIn('id', $productIdList)
            ->get()
            ->keyBy('id');

        $stockMap = DB::table('stocks')
            ->whereIn('product_id', $productIdList)
            ->groupBy('product_id')
            ->selectRaw('product_id, COALESCE(SUM(on_hand), 0) as on_hand')
            ->pluck('on_hand', 'product_id')
            ->all();

        $subtotal = 0;
        $warnings = [];
        $normalized = [];

        foreach ($cart['items'] as $key => $it) {
            $pid = (string) ($it['product_id'] ?? '');
            if ($pid === '' || !$products->has($pid)) {
                $warnings[] = 'Sản phẩm không còn tồn tại';
                continue;
            }

            $p = $products[$pid];
            $available = isset($stockMap[$pid]) ? (int) $stockMap[$pid] : 0;

            if ($available <= 0) {
                $warnings[] = 'Hết hàng: ' . $p->title;
                continue;
            }

            $qty = (int) ($it['qty'] ?? 1);
            if ($qty > $available) {
                $warnings[] = 'Giới hạn theo tồn kho: ' . $p->title;
                $qty = $available;
            }

            $price = (int) $p->selling_price_vnd;
            $lineTotal = $price * $qty;

            $normalized[$key] = [
                'key'        => $key,
                'product_id' => $p->id,
                'variant_id' => $it['variant_id'] ?? null,
                'title'      => $p->title,
                'image'      => $p->image,
                'qty'        => $qty,
                'max_qty'    => $available,
                'price'      => $price,
                'line_total' => $lineTotal,
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
            $subtotal += (int) $cart['items'][$k]['line_total'];
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
