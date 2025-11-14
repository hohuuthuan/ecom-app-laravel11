<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\Province;
use App\Services\User\CartService;

class CheckoutController extends Controller
{

    public function enter(Request $request, CartService $svc)
    {
        $raw = (string) $request->input('keys', '');
        $ids = array_values(array_filter(array_unique(explode(',', $raw))));
        if (count($ids) === 0) {
            return redirect()->route('cart')->with('toast_error', 'Bạn cần chọn sản phẩm trước.');
        }

        $cart = $svc->get();
        if (!is_array($cart) || empty($cart['items'])) {
            return redirect()->route('cart')->with('toast_error', 'Các sản phẩm không còn hợp lệ.');
        }

        $cartMap = [];
        foreach ($cart['items'] as $line) {
            if (!empty($line['product_id'])) {
                $cartMap[$line['product_id']] = (int) ($line['qty'] ?? 1);
            }
        }

        $pairs = [];
        foreach ($ids as $pid) {
            if (!isset($cartMap[$pid])) {
                return redirect()->route('cart')->with('toast_error', 'Các sản phẩm không còn hợp lệ');
            }
            $pairs[] = ['id' => $pid, 'qty' => $cartMap[$pid]];
        }

        $exists = Product::query()->whereIn('id', $ids)->pluck('id')->all();
        if (count($exists) !== count($ids)) {
            return redirect()->route('cart')->with('toast_error', 'Các sản phẩm không còn hợp lệ');
        }

        $request->session()->put('checkout.items', $pairs);
        $request->session()->put('checkout.expires_at', now()->addMinutes(15)->timestamp);

        return redirect()->route('checkout.page');
    }

    public function index(Request $request)
    {
        $pairs = $request->session()->get('checkout.items', []);
        if (!is_array($pairs) || count($pairs) === 0) {
            return redirect()->route('cart')->with('toast_error', 'Bạn cần chọn sản phẩm trước');
        }

        $ids = [];
        $qtyMap = [];
        foreach ($pairs as $it) {
            if (!empty($it['id'])) {
                $ids[] = $it['id'];
                $qtyMap[$it['id']] = (int) ($it['qty'] ?? 1);
            }
        }

        if (count($ids) === 0) {
            $request->session()->forget(['checkout.items', 'checkout.expires_at']);
            return redirect()->route('cart')->with('toast_error', 'Bạn cần chọn sản phẩm trước');
        }

        $rows = \App\Models\Product::query()
            ->select(['id', 'title', 'image', 'selling_price_vnd'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        if ($rows->count() !== count($ids)) {
            $request->session()->forget(['checkout.items', 'checkout.expires_at']);
            return redirect()->route('cart')->with('toast_error', 'Các sản phẩm đã chọn không còn hợp lệ');
        }

        $items = [];
        $subtotal = 0;
        foreach ($ids as $pid) {
            $p = $rows[$pid];
            $qty = $qtyMap[$pid] ?? 1;
            $price = (int) $p->selling_price_vnd;
            $lineTotal = $price * $qty;

            $items[] = [
                'id'         => (string) $p->id,
                'title'      => (string) $p->title,
                'image'      => (string) ($p->image ?? ''),
                'qty'        => $qty,
                'price'      => $price,
                'line_total' => $lineTotal,
            ];

            $subtotal += $lineTotal;
        }

        $shipping = count($items) > 0 ? 30000 : 0;
        $total = $subtotal + $shipping;

        /** @var User|null $user */
        $user = Auth::user();

        $addresses = $user
            ? $user->addresses()
            ->with(['ward', 'province'])
            ->orderByDesc('default')
            ->get()
            : collect();

        $selectedAddress = $addresses->first();

        $fullName = old('full_name', $user?->name ?? '');
        $phone    = $user?->phone ?? '';
        $email    = old('email', $user?->email ?? '');
        $provinces = Province::orderBy('name')->get();

        return view('user.checkout', compact(
            'items',
            'subtotal',
            'shipping',
            'total',
            'addresses',
            'selectedAddress',
            'fullName',
            'phone',
            'email',
            'provinces'
        ));
    }

    public function place(Request $request)
    {
        echo "Place order coming soon...";
    }
}
