<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\Order\OrderService;
use App\Models\Order;

class OrderPageController extends Controller
{
  public function __construct(private OrderService $orderService) {}

  public function index(Request $r)
  {
    $filters = [
      'per_page'        => $r->query('per_page_order', 10),
      'keyword'         => $r->query('keyword'),
      'payment_method'  => $r->query('payment_method'),
      'payment_status'  => $r->query('payment_status'),
      'status'          => $r->query('status'),
      'created_from'    => $r->query('created_from'),
      'created_to'      => $r->query('created_to'),
    ];

    $orders = $this->orderService->getList($filters);

    return view('admin.order.index', compact('orders'));
  }

  public function detail(string $id)
  {
    $order = Order::with([
      'user',
      'items.product',
      'shipment',
      'discount',
    ])->findOrFail($id);

    return view('admin.order.detail', compact('order'));
  }
}
