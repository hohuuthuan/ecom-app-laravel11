<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidCheckout
{
    public function handle(Request $request, Closure $next): Response
    {
        $items = $request->session()->get('checkout.items');
        $expiresAt = $request->session()->get('checkout.expires_at');

        if (!$items || !is_array($items) || count($items) === 0) {
            return redirect()->route('cart')->with('toast_error', 'Bạn cần chọn sản phẩm trước.');
        }

        if ($expiresAt && now()->timestamp > (int)$expiresAt) {
            $request->session()->forget(['checkout.items', 'checkout.expires_at']);
            return redirect()->route('cart')->with('toast_error', 'Bạn cần chọn sản phẩm trước.');
        }

        return $next($request);
    }
}
