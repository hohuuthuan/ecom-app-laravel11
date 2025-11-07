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

    $keys = array_filter(array_map('trim', explode(',', $raw)));
    if (empty($keys)) {
      return redirect()->route('cart')->with('toast_error', 'Vui lòng chọn sản phẩm trước khi thanh toán.');
    }

    $summary = $svc->summarize($keys);
    if (empty($summary['items'])) {
      return redirect()->route('cart')->with('toast_error', 'Các sản phẩm đã chọn không còn hợp lệ.');
    }

    return view('user.checkout', $summary);
  }
}
