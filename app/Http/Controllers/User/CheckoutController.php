<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\CartService;

class CheckoutController extends Controller
{
  public function index(Request $request, CartService $svc)
  {
    $raw = (string)$request->query('keys', '');
    if ($raw === '') {
      return redirect()->route('cart')->with('toast_error', 'Vui lòng chọn sản phẩm trước khi thanh toán.');
    }

    $keys = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
    if (empty($keys)) {
      return redirect()->route('cart')->with('toast_error', 'Vui lòng chọn sản phẩm trước khi thanh toán.');
    }

    $summary = $svc->summarize($keys);
    if (empty($summary['items'])) {
      return redirect()->route('cart')->with('toast_error', 'Các sản phẩm đã chọn không còn hợp lệ.');
    }

    $effectiveKeys = [];
    foreach ($summary['items'] as $line) {
      if (!empty($line['key'])) {
        $effectiveKeys[] = $line['key'];
      }
    }
    if (empty($effectiveKeys)) {
      return redirect()->route('cart')->with('toast_error', 'Các sản phẩm đã chọn không còn hợp lệ.');
    }

    return view('user.checkout', [
      'items'    => $summary['items'],
      'subtotal' => (int)$summary['subtotal'],
      'shipping' => (int)$summary['shipping'],
      'total'    => (int)$summary['total'],
      'keys'     => implode(',', $effectiveKeys),
    ]);
  }

  public function place(Request $request, CartService $svc) {
    echo "Place order coming soon...";
  }
}
