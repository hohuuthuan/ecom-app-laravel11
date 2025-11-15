<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\Order\OrderService;
use App\Services\Admin\Order\OrderStatusService;
use App\Models\Order;

class OrderPageController extends Controller
{
  public function __construct(
    private OrderService $orderService,
    private readonly OrderStatusService $orderStatusService
  ) {}

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
      'statusHistories' => function ($query) {
        $query->orderBy('created_at', 'desc');
      },
    ])->findOrFail($id);

    return view('admin.order.detail', compact('order'));
  }

  public function changeStatus(Request $request, string $id)
  {
    $request->validate([
      'status' => ['required', 'string'],
    ]);

    $order = Order::findOrFail($id);

    // Giá trị gửi từ form: PENDING / CONFIRMED / PROCESSING / SHIPPING / DELIVERED / COMPLETED / CANCELLED
    $input = strtoupper(trim($request->input('status')));

    $map = [
      'PENDING'    => 'pending',
      'CONFIRMED'  => 'confirmed',
      'PROCESSING' => 'processing',
      'SHIPPING'   => 'shipping',
      'DELIVERED'  => 'delivered',
      'COMPLETED'  => 'completed',
      'CANCELLED'  => 'cancelled',
    ];

    if (!isset($map[$input])) {
      return back()->with('toast_error', 'Trạng thái không hợp lệ');
    }

    $current = strtolower((string) $order->status);
    $target  = $map[$input];

    if (!$this->canChangeStatus($current, $target)) {
      return back()->with(
        'toast_error',
        'Không thể chuyển trạng thái từ ' . strtoupper($current) . ' sang ' . $input . '.'
      );
    }

    if ($current === $target) {
      return back()->with('toast_info', 'Trạng thái đơn hàng không thay đổi');
    }

    $this->orderStatusService->changeStatus($order, $target);

    return back()->with('toast_success', 'Cập nhật trạng thái đơn hàng thành công');
  }

  private function canChangeStatus(string $current, string $target): bool
  {
    if ($current === $target) {
      return true;
    }
    if ($current === 'cancelled') {
      return false;
    }

    $early = ['pending', 'confirmed', 'processing'];
    if (in_array($current, $early, true) && in_array($target, $early, true)) {
      return true;
    }
    if ($target === 'cancelled') {
      return in_array($current, $early, true);
    }

    $flow = ['shipping', 'delivered', 'completed'];

    $currentIndex = array_search($current, $flow, true);
    $targetIndex  = array_search($target, $flow, true);
    if ($currentIndex !== false && $targetIndex !== false) {
      return $targetIndex >= $currentIndex;
    }
    if (in_array($current, $early, true) && $target === 'shipping') {
      return true;
    }

    return false;
  }
}
