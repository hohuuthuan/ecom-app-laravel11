<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Warehouse\StoreWarehouseImportRequest;
use App\Models\Product;
use App\Models\Publisher;
use App\Models\Order;
use App\Services\Admin\Warehouse\WarehouseImportService;
use App\Services\Admin\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class WarehousePageController extends Controller
{
  public function __construct(
    private OrderService $orderService,
    private WarehouseImportService $warehouseImportService
  ) {}

  public function dashboard(Request $r)
  {
    return view('admin.warehouse.dashboard');
  }

  public function orders(Request $r)
  {
    $filters = [
      'keyword'        => $r->input('keyword'),
      'payment_method' => $r->input('payment_method'),
      'payment_status' => $r->input('payment_status'),
      'status'         => $r->input('status'),
      'created_from'   => $r->input('created_from'),
      'created_to'     => $r->input('created_to'),
      'per_page'       => $r->input('per_page_order'),
    ];

    $orders = $this->orderService->getWarehouseOrders($filters);

    return view('admin.warehouse.order.index', compact('orders'));
  }

  public function orderDetail(string $id)
  {
    [$order, $stockMap, $itemBatches] = $this->orderService->getWarehouseOrderDetail($id);
    
    return view('admin.warehouse.order.detail', compact('order', 'stockMap', 'itemBatches'));
  }

  public function changeOrderStatus(Request $r, string $id)
  {
    $validated = $r->validate([
      'warehouse_status' => [
        'required',
        'string',
        Rule::in([
          'RECEIVING_PROCESS',
          'PREPARING_ITEMS',
          'HANDED_OVER_CARRIER',
        ]),
      ],
    ]);

    $order = Order::findOrFail($id);

    $mapToOrderStatus = [
      'RECEIVING_PROCESS'   => 'PROCESSING',
      'PREPARING_ITEMS'     => 'SHIPPING',
      'HANDED_OVER_CARRIER' => 'DELIVERED',
    ];

    $targetStatus = $mapToOrderStatus[$validated['warehouse_status']];

    $levelMap = [
      'PROCESSING' => 1,
      'SHIPPING'   => 2,
      'DELIVERED'  => 3,
    ];

    $currentStatus = strtoupper((string) $order->status);
    if (!isset($levelMap[$currentStatus])) {
      return back()->with('toast_error', 'Trạng thái đơn hiện tại không thể cập nhật từ giao diện kho.');
    }
    if ($levelMap[$targetStatus] < $levelMap[$currentStatus]) {
      return back()->with('toast_error', 'Không thể chuyển trạng thái lùi về bước trước đó.');
    }
    if ($currentStatus === $targetStatus) {
      return back()->with('toast_info', 'Trạng thái đơn hàng không thay đổi.');
    }

    $order->status = $targetStatus;
    $order->save();

    return back()->with('toast_success', 'Cập nhật trạng thái đơn hàng thành công');
  }

  public function inventory(Request $r)
  {
    $filters = [
      'keyword'  => $r->query('keyword'),
      'status'   => $r->query('status'),
      'per_page' => (int) $r->query('per_page', 20),
    ];

    $overview = $this->warehouseImportService->getInventoryOverview($filters);

    return view('admin.warehouse.inventory', [
      'lowStocks'         => $overview['lowStocks'],
      'outOfStocks'       => $overview['outOfStocks'],
      'inventoryProducts' => $overview['inventoryProducts'],
      'filters'           => $filters,
    ]);
  }

  public function import(Request $r)
  {
    $publishers = Publisher::query()
      ->where('status', 'ACTIVE')
      ->orderBy('name')
      ->get(['id', 'name']);

    $initialProducts = [];
    $oldPublisherId = old('publisher_id');

    if ($oldPublisherId !== null && $oldPublisherId !== '') {
      $initialProducts = Product::query()
        ->where('status', 'ACTIVE')
        ->where('publisher_id', $oldPublisherId)
        ->orderBy('title')
        ->get([
          'id',
          'title',
          'code',
          'isbn',
          'unit',
        ]);
    }

    return view('admin.warehouse.warehouse-import', [
      'publishers' => $publishers,
      'initialProducts' => $initialProducts,
    ]);
  }

  public function productsByPublisher(Request $r): JsonResponse
  {
    $publisherId = $r->query('publisher_id');

    if (!is_string($publisherId) || $publisherId === '') {
      return response()->json([
        'items' => [],
      ]);
    }

    $products = Product::query()
      ->where('status', 'ACTIVE')
      ->where('publisher_id', $publisherId)
      ->orderBy('title')
      ->get([
        'id',
        'title',
        'code',
        'isbn',
        'unit',
      ]);

    return response()->json([
      'items' => $products,
    ]);
  }

  public function handleImport(StoreWarehouseImportRequest $request): RedirectResponse
  {
    $data = $request->validated();
    $items = $data['items'] ?? [];

    $items = array_values(array_filter($items, function ($row) {
      return !empty($row['product_id']);
    }));

    if (!is_array($items) || count($items) === 0) {
      return redirect()
        ->back()
        ->with('toast_error', 'Bạn cần chọn sản phẩm.')
        ->withInput();
    }

    $data['items'] = $items;

    $productIds = [];
    foreach ($items as $item) {
      $productIds[] = $item['product_id'];
    }
    $productIds = array_values(array_unique($productIds));

    $countProducts = Product::query()
      ->where('status', 'ACTIVE')
      ->where('publisher_id', $data['publisher_id'])
      ->whereIn('id', $productIds)
      ->count();

    if ($countProducts !== count($productIds)) {
      return redirect()
        ->back()
        ->with('toast_error', 'Có ít nhất một sản phẩm không thuộc nhà xuất bản đã chọn.')
        ->withInput();
    }

    foreach ($items as $item) {
      if ((int) $item['qty_document'] !== (int) $item['qty_real']) {
        return redirect()
          ->back()
          ->with('toast_error', 'Số lượng theo chứng từ và số lượng thực nhập của mỗi sản phẩm phải bằng nhau.')
          ->withInput();
      }
    }

    $success = $this->warehouseImportService->createPurchaseReceipt($data);

    if (!$success) {
      return redirect()
        ->back()
        ->with('toast_error', 'Có lỗi phía server, vui lòng thử lại sau')
        ->withInput();
    }

    return redirect()
      ->back()
      ->with('toast_success', 'Tạo phiếu nhập kho thành công.');
  }

  public function purchaseReceiptIndex(Request $r): View
  {
    $filters = [
      'per_page'     => (int) $r->query('per_page', 20),
      'keyword'      => $r->query('keyword'),
      'publisher_id' => $r->query('publisher_id'),
      'warehouse_id' => $r->query('warehouse_id'),
    ];

    $receipts = $this->warehouseImportService->getReceiptList($filters);

    $publishers = Publisher::query()
      ->orderBy('name')
      ->get(['id', 'name']);

    return view('admin.warehouse.purchase-receipts.index', compact(
      'receipts',
      'filters',
      'publishers',
    ));
  }

  public function purchaseReceiptShow(string $id): View|RedirectResponse
  {
    $receipt = $this->warehouseImportService->getReceiptDetail($id);

    if (!$receipt) {
      return redirect()
        ->route('warehouse.purchase_receipts.index')
        ->with('toast_error', 'Không tìm thấy phiếu nhập kho');
    }

    return view('admin.warehouse.purchase-receipts.show', compact('receipt'));
  }
}
