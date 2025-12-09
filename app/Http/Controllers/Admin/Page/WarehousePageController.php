<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Warehouse\StoreWarehouseImportRequest;
use App\Models\Product;
use App\Models\Publisher;
use App\Models\Order;
use App\Models\Stock;
use App\Models\OrderBatch;
use Illuminate\Support\Facades\DB;
use App\Services\Admin\Warehouse\WarehouseImportService;
use App\Services\Admin\Order\OrderService;
use App\Services\Admin\Warehouse\WarehouseActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class WarehousePageController extends Controller
{
  public function __construct(
    private OrderService $orderService,
    private WarehouseImportService $warehouseImportService,
    private WarehouseActivityService $warehouseActivityService,
  ) {}

  public function dashboard(Request $r): View
  {
    $totalProducts = Product::query()
      ->where('status', 'ACTIVE')
      ->count();

    $pendingOrders = Order::query()
      ->whereIn('status', ['PROCESSING', 'PICKING'])
      ->count();

    $lowStockItems = Stock::query()
      ->where('on_hand', '>', 0)
      ->where(function ($q) {
        $q->where(function ($q2) {
          $q2->whereNotNull('reorder_point')
            ->whereColumn('on_hand', '<=', 'reorder_point');
        })
          ->orWhere(function ($q2) {
            $q2->whereNull('reorder_point')
              ->where('on_hand', '<=', 50);
          });
      })
      ->count();

    $outOfStockItems = Product::query()
      ->where('status', 'ACTIVE')
      ->where(function ($q) {
        $q->whereDoesntHave('stocks')
          ->orWhereHas('stocks', function ($q2) {
            $q2->select('product_id', DB::raw('SUM(on_hand) as total_on_hand'))
              ->groupBy('product_id')
              ->havingRaw('SUM(on_hand) <= 0');
          });
      })
      ->count();

    $stats = [
      'total_products'     => $totalProducts,
      'pending_orders'     => $pendingOrders,
      'low_stock_items'    => $lowStockItems,
      'out_of_stock_items' => $outOfStockItems,
    ];

    $recentActivities = $this->warehouseActivityService->getTodayActivityList([
      'per_page' => 5,
    ]);

    return view('admin.warehouse.dashboard', [
      'stats'            => $stats,
      'recentActivities' => $recentActivities,
    ]);
  }

  public function orders(Request $r): View
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

  public function orderDetail(string $id): View
  {
    [$order, $stockMap, $itemBatches, $shippers] = $this->orderService->getWarehouseOrderDetail($id);

    return view('admin.warehouse.order.detail', compact('order', 'stockMap', 'itemBatches', 'shippers'));
  }

  public function changeOrderStatus(Request $r, string $id): RedirectResponse
  {
    $validated = $r->validate([
      'warehouse_status' => [
        'required',
        'string',
        Rule::in([
          'RECEIVING_PROCESS',
          'PREPARING_ITEMS',
          'HANDED_OVER_CARRIER',
          'ORDER_COMPLETED',
        ]),
      ],
    ]);

    $order = Order::with(['items', 'items.product'])->findOrFail($id);

    $mapToOrderStatus = [
      'RECEIVING_PROCESS'   => 'PROCESSING',
      'PREPARING_ITEMS'     => 'PICKING',
      'HANDED_OVER_CARRIER' => 'SHIPPING',
      'ORDER_COMPLETED'     => 'COMPLETED',
    ];

    $warehouseStatusLabels = [
      'RECEIVING_PROCESS'   => 'Tiếp nhận đơn',
      'PREPARING_ITEMS'     => 'Đang chuẩn bị hàng',
      'HANDED_OVER_CARRIER' => 'Đã giao cho đơn vị vận chuyển',
      'ORDER_COMPLETED'     => 'Đơn hàng hoàn tất',
    ];

    $targetStatus = $mapToOrderStatus[$validated['warehouse_status']];
    $statusLabel  = $warehouseStatusLabels[$validated['warehouse_status']] ?? $targetStatus;

    $shippingType = strtoupper((string) $order->shipping_type);

    if ($targetStatus === 'SHIPPING' && !in_array($shippingType, ['INTERNAL', 'EXTERNAL'], true)) {
      return back()->with(
        'toast_error',
        'Vui lòng chọn đơn vị vận chuyển'
      );
    }
    if ($targetStatus === 'COMPLETED' && $shippingType !== 'EXTERNAL') {
      return back()->with(
        'toast_error',
        'Chỉ đơn hàng giao bởi đơn vị vận chuyển khác mới được hoàn tất từ kho'
      );
    }

    $levelMap = [
      'PROCESSING' => 1,
      'PICKING'    => 2,
      'SHIPPING'   => 3,
      'COMPLETED'  => 4,
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

    try {
      DB::transaction(function () use ($order, $targetStatus, $statusLabel, $levelMap) {
        $currentStatusInside = strtoupper((string) $order->status);

        // Lần đầu vượt qua mốc SHIPPING thì phân bổ lô + trừ tồn
        $needHandleShipping = $levelMap[$targetStatus] >= $levelMap['SHIPPING']
          && $levelMap[$currentStatusInside] < $levelMap['SHIPPING'];

        if ($needHandleShipping) {
          $this->allocateBatchesAndDeductStock($order);
        }

        $order->status = $targetStatus;
        $order->save();

        WarehouseActivityService::log('Đơn #' . $order->code . ' - ' . $statusLabel);
      });
    } catch (\RuntimeException $e) {
      return back()->with('toast_error', $e->getMessage());
    } catch (\Throwable $e) {
      return back()->with('toast_error', 'Có lỗi khi cập nhật trạng thái đơn hàng.');
    }

    return back()->with('toast_success', 'Cập nhật trạng thái đơn hàng: ' . $statusLabel);
  }

  private function allocateBatchesAndDeductStock(Order $order): void
  {
    $itemIds = $order->items->pluck('id')->all();
    if (count($itemIds) === 0) {
      return;
    }
    $hasBatches = DB::table('order_batches')
      ->whereIn('order_item_id', $itemIds)
      ->exists();

    if ($hasBatches) {
      return;
    }

    foreach ($order->items as $item) {
      $qty = (int) $item->quantity;
      if ($qty <= 0) {
        continue;
      }

      if (!$item->warehouse_id) {
        $name = $item->product_title_snapshot
          ?? $item->product->title
          ?? (string) $item->product_id;

        throw new \RuntimeException('Thiếu thông tin kho cho sản phẩm: ' . $name);
      }

      $pid  = (string) $item->product_id;
      $wid  = (string) $item->warehouse_id;
      $need = $qty;

      $batchRows = DB::table('batch_stocks as bs')
        ->join('batches as b', 'b.id', '=', 'bs.batch_id')
        ->select([
          'bs.batch_id',
          'bs.product_id',
          'bs.warehouse_id',
          'bs.on_hand',
          'b.import_date',
          'b.quantity as batch_quantity',
          'b.import_price_vnd',
          'b.code',
        ])
        ->where('bs.product_id', $pid)
        ->where('bs.warehouse_id', $wid)
        ->orderBy('b.import_date')
        ->lockForUpdate()
        ->get();

      if ($batchRows->isEmpty()) {
        $name = $item->product_title_snapshot
          ?? $item->product->title
          ?? (string) $item->product_id;

        throw new \RuntimeException(
          'Không tìm thấy lô hàng cho sản phẩm: ' . $name
        );
      }

      foreach ($batchRows as $row) {
        if ($need <= 0) {
          break;
        }

        $available = (int) $row->on_hand;
        if ($available <= 0) {
          continue;
        }

        $take = $need > $available ? $available : $need;
        OrderBatch::create([
          'order_item_id' => $item->id,
          'batch_id'      => $row->batch_id,
          'quantity'      => $take,
          'unit_cost_vnd' => (int) $row->import_price_vnd,
        ]);
        DB::table('batch_stocks')
          ->where('product_id', $pid)
          ->where('warehouse_id', $wid)
          ->where('batch_id', $row->batch_id)
          ->update([
            'on_hand' => $available - $take,
          ]);

        $need -= $take;
      }

      if ($need > 0) {
        $name = $item->product_title_snapshot
          ?? $item->product->title
          ?? (string) $item->product_id;

        throw new \RuntimeException(
          'Không đủ lô hàng để phân bổ cho sản phẩm: ' . $name . ' (thiếu ' . $need . ').'
        );
      }
      $stockRow = DB::table('stocks')
        ->where('product_id', $item->product_id)
        ->where('warehouse_id', $item->warehouse_id)
        ->lockForUpdate()
        ->first();

      if (!$stockRow || (int) $stockRow->on_hand < $qty) {
        $name = $item->product_title_snapshot
          ?? $item->product->title
          ?? (string) $item->product_id;

        throw new \RuntimeException(
          'Sản phẩm "' . $name . '" không đủ tồn kho tổng (cần ' . $qty . ').'
        );
      }

      DB::table('stocks')
        ->where('product_id', $item->product_id)
        ->where('warehouse_id', $item->warehouse_id)
        ->update([
          'on_hand' => DB::raw('on_hand - ' . $qty),
        ]);
    }
  }

  public function assignShipper(Request $request, string $orderId): RedirectResponse|JsonResponse
  {
    $order = Order::findOrFail($orderId);

    $shippingTypeInput = strtoupper((string) $request->input('shipping_type'));

    $rules = [
      'shipping_type' => [
        'required',
        'string',
        Rule::in(['INTERNAL', 'EXTERNAL']),
      ],
      'shipper_id' => [
        $shippingTypeInput === 'INTERNAL' ? 'required' : 'nullable',
        'uuid',
        'exists:users,id',
      ],
    ];

    $messages = [
      'shipper_id.required' => 'Vui lòng chọn người giao hàng.',
    ];

    $data = $request->validate($rules, $messages);

    $shippingType = strtoupper($data['shipping_type']);

    if ($shippingType === 'INTERNAL') {
      $shipperId = $data['shipper_id'];
    } else {
      $shipperId = null;
    }

    $order->shipping_type = $shippingType;
    $order->shipper_id = $shipperId;
    $order->save();

    if ($request->expectsJson()) {
      return response()->json([
        'success'       => true,
        'shipping_type' => $order->shipping_type,
        'shipper_id'    => $order->shipper_id,
      ]);
    }

    return back()->with('toast_success', 'Cập nhật đơn vị vận chuyển thành công.');
  }

  public function inventory(Request $r): View
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

  public function import(Request $r): View
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
      'publishers'      => $publishers,
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
