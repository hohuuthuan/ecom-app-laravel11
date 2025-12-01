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

    $input = strtoupper(trim($request->input('status')));

    $map = [
      'PENDING'    => 'pending',
      'PROCESSING' => 'processing',
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
      return back()->with('toast_info', 'Trạng thái không thay đổi');
    }

    $this->orderStatusService->changeStatus($order, $target);

    return back()->with('toast_success', 'Cập nhật trạng thái thành công');
  }

  private function canChangeStatus(string $current, string $target): bool
  {
    $current = strtolower($current);
    $target = strtolower($target);
    if ($current === $target) {
      return true;
    }
    $editable = ['pending', 'processing', 'cancelled'];

    if (!in_array($current, $editable, true)) {
      return false;
    }
    $allowedTargets = ['pending', 'processing', 'cancelled'];

    return in_array($target, $allowedTargets, true);
  }
}
