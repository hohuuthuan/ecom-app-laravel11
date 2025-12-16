<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\Order\OrderService;
use App\Services\Admin\Order\OrderStatusService;
use App\Models\Order;
use App\Models\OrderDeliveryIssue;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;

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
      'discount' => function ($q) {
        $q->withTrashed();
      },
      'statusHistories' => function ($query) {
        $query->orderBy('created_at', 'desc');
      },
    ])->findOrFail($id);

    return view('admin.order.detail', compact('order'));
  }

  public function changeStatus(Request $request, string $id): RedirectResponse
  {
    $validated = $request->validate([
      'status' => ['required', 'string', Rule::in(['PENDING', 'PROCESSING', 'CANCELLED'])],
    ]);

    $order = Order::findOrFail($id);

    $target = strtoupper((string) $validated['status']);
    $current = strtoupper((string) ($order->status ?? ''));

    if (!$this->canChangeStatus($current, $target)) {
      return back()->with(
        'toast_error',
        'Không thể chuyển trạng thái từ ' . ($current !== '' ? $current : 'N/A') . ' sang ' . $target . '.'
      );
    }

    if ($current === $target) {
      return back()->with('toast_info', 'Trạng thái không thay đổi');
    }

    $this->orderStatusService->changeStatus($order, $target);

    return back()->with('toast_success', 'Cập nhật trạng thái thành công');
  }

  public function bulkChangeStatus(Request $request): RedirectResponse
  {
    $validated = $request->validate([
      'status' => ['required', 'string', Rule::in(['PROCESSING', 'CANCELLED'])],
      'ids'    => ['required', 'array', 'min:1'],
      'ids.*'  => ['required', 'uuid', 'distinct', 'exists:orders,id'],
    ]);

    $result = $this->orderStatusService->bulkChangeStatus($validated['ids'], $validated['status']);

    if (($result['updated'] ?? 0) <= 0) {
      return back()->with('toast_error', 'Không có đơn hàng nào đủ điều kiện để cập nhật.');
    }

    $message = 'Đã cập nhật ' . (int) $result['updated'] . ' đơn hàng.';
    $skipped = (int) ($result['skipped'] ?? 0);

    if ($skipped > 0) {
      $message .= ' Bỏ qua ' . $skipped . ' đơn (không hợp lệ / không đủ điều kiện / đã đúng trạng thái).';
    }

    return back()->with('toast_success', $message);
  }

  private function canChangeStatus(string $current, string $target): bool
  {
    $current = strtoupper(trim($current));
    $target = strtoupper(trim($target));

    if ($current === $target) {
      return true;
    }

    if ($current === 'PENDING') {
      return in_array($target, ['PROCESSING', 'CANCELLED'], true);
    }

    if ($current === 'PROCESSING') {
      return $target === 'CANCELLED';
    }

    return false;
  }

  public function issues(Request $request): View
  {
    $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');

    $query = OrderDeliveryIssue::query()
      ->with(['order.user', 'order.shipment'])
      ->whereIn('order_payment_method', ['momo', 'vnpay'])
      ->where('refund_amount_vnd', '>', 0);

    $keyword = trim((string) $request->query('keyword', ''));
    if ($keyword !== '') {
      $kw = '%' . $keyword . '%';

      $query->where(function ($q) use ($kw) {
        $q->whereHas('order', function ($oq) use ($kw) {
          $oq->where('code', 'LIKE', $kw)
            ->orWhereHas('user', function ($uq) use ($kw) {
              $uq->where('name', 'LIKE', $kw)
                ->orWhere('phone', 'LIKE', $kw)
                ->orWhere('email', 'LIKE', $kw);
            })
            ->orWhereHas('shipment', function ($sq) use ($kw) {
              $sq->where('name', 'LIKE', $kw)
                ->orWhere('phone', 'LIKE', $kw)
                ->orWhere('address', 'LIKE', $kw);
            });
        });
      });
    }

    $refundStatus = (string) $request->query('refund_status', '');
    if ($refundStatus === 'NEED_REFUND') {
      $query->where('is_refunded', false);
    } elseif ($refundStatus === 'REFUNDED') {
      $query->where('is_refunded', true);
    }

    if ($request->filled('issued_from')) {
      $from = Carbon::createFromFormat('Y-m-d', (string) $request->query('issued_from'), $tz)
        ->startOfDay()
        ->utc();
      $query->where('issued_at', '>=', $from);
    }

    if ($request->filled('issued_to')) {
      $to = Carbon::createFromFormat('Y-m-d', (string) $request->query('issued_to'), $tz)
        ->endOfDay()
        ->utc();
      $query->where('issued_at', '<=', $to);
    }

    $perPage = (int) $request->query('per_page_issue', 10);
    if ($perPage <= 0) {
      $perPage = 10;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    if ($refundStatus === '') {
      $query->orderBy('is_refunded');
    }

    $issues = $query
      ->orderByDesc('issued_at')
      ->orderByDesc('created_at')
      ->paginate($perPage)
      ->withQueryString();

    return view('admin.order.issues', compact('issues'));
  }

  public function markIssueRefunded(Request $request, string $id): RedirectResponse
  {
    /** @var \App\Models\OrderDeliveryIssue $issue */
    $issue = OrderDeliveryIssue::findOrFail($id);

    if ($issue->is_refunded) {
      return back()->with('toast_info', 'Sự cố này đã được đánh dấu hoàn tiền trước đó');
    }

    $issue->is_refunded = true;
    $issue->refunded_at = now();
    $issue->save();

    return back()->with('toast_success', 'Đã đánh dấu hoàn tiền cho đơn hàng');
  }
}
