<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Warehouse\StoreWarehouseImportRequest;
use App\Models\Product;
use App\Models\Publisher;
use App\Services\Admin\Warehouse\WarehouseImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class WarehousePageController extends Controller
{
  public function __construct(
    private WarehouseImportService $warehouseImportService
  ) {}

  public function dashboard(Request $r)
  {
    return view('admin.warehouse.dashboard');
  }

  public function orders(Request $r)
  {
    return view('admin.warehouse.orders');
  }

  public function inventory(Request $r)
  {
    return view('admin.warehouse.inventory');
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
    $items = $data['items'];

    if (!is_array($items) || count($items) === 0) {
      return redirect()
        ->back()
        ->with('toast_error', 'Phiếu nhập phải có ít nhất một sản phẩm.')
        ->withInput();
    }

    $userId = Auth::id();

    $productIds = [];
    foreach ($items as $item) {
      $productIds[] = (int) $item['product_id'];
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

    // ================== CHUẨN BỊ DỮ LIỆU INSERT ==================

    $receiptId = (string) Str::uuid();
    $receivedAt = Carbon::parse($data['receipt_date'])->startOfDay();
    $warehouseId = null;

    $subTotalVnd = 0;

    $itemsData = [];
    $batchesData = [];
    $batchStocksData = [];
    $stockMovementsData = [];
    $stockTotalsByProduct = [];

    foreach ($items as $item) {
      $productId = (int) $item['product_id'];
      $qtyDoc = (int) $item['qty_document'];
      $qtyActual = (int) $item['qty_real'];
      $unitPrice = (int) $item['price'];
      $note = $item['note'] ?? null;

      $lineTotal = $unitPrice * $qtyActual;
      $subTotalVnd += $lineTotal;

      $itemId = (string) Str::uuid();
      $batchId = (string) Str::uuid();

      $itemsData[] = [
        'id' => $itemId,
        'purchase_receipt_id' => $receiptId,
        'product_id' => $productId,
        'import_price_vnd' => $unitPrice,
        'qty_doc' => $qtyDoc,
        'qty_actual' => $qtyActual,
        'notes' => $note,
      ];

      $batchesData[] = [
        'id' => $batchId,
        'purchase_receipt_item_id' => $itemId,
        'product_id' => $productId,
        'warehouse_id' => $warehouseId,
        'quantity' => $qtyActual,
        'import_price_vnd' => $unitPrice,
        'import_date' => $receivedAt->toDateString(),
      ];

      $batchStocksData[] = [
        'batch_id' => $batchId,
        'product_id' => $productId,
        'warehouse_id' => $warehouseId,
        'on_hand' => $qtyActual,
        'reserved' => 0,
      ];

      $stockMovementsData[] = [
        'id' => (string) Str::uuid(),
        'product_id' => $productId,
        'warehouse_id' => $warehouseId,
        'batch_id' => $batchId,
        'type' => 'receipt',
        'qty' => $qtyActual,
        'related_id' => $receiptId,
        'note' => $note,
        'created_by' => $userId,
      ];

      if (!isset($stockTotalsByProduct[$productId])) {
        $stockTotalsByProduct[$productId] = 0;
      }
      $stockTotalsByProduct[$productId] += $qtyActual;
    }

    $receiptData = [
      'id' => $receiptId,
      'publisher_id' => $data['publisher_id'],
      'warehouse_id' => $warehouseId,
      'received_at' => $receivedAt,
      'name_of_delivery_person' => $data['deliver_name'],
      'delivery_unit_name' => $data['deliver_unit'],
      'delivery_address' => $data['deliver_address'],
      'delivery_note_number' => $data['delivery_number'],
      'internal_from_warehouse' => $data['internal_from_warehouse'],
      'tax_identification_number' => null,
      'sub_total_vnd' => $subTotalVnd,
      'created_by' => $userId,
    ];

    $stockUpdatesData = [];
    foreach ($stockTotalsByProduct as $productId => $qtyIncrease) {
      $stockUpdatesData[] = [
        'product_id' => $productId,
        'warehouse_id' => $warehouseId,
        'qty_change' => $qtyIncrease,
      ];
    }

    $payload = [
      'receipt' => $receiptData,
      'items' => $itemsData,
      'batches' => $batchesData,
      'batch_stocks' => $batchStocksData,
      'stock_movements' => $stockMovementsData,
      'stock_updates' => $stockUpdatesData,
    ];

    $success = $this->warehouseImportService->createPurchaseReceipt($data);

    if (!$success) {
      return redirect()
        ->back()
        ->with('toast_error', 'Có lỗi phía server, vui lòng thử lại sau.')
        ->withInput();
    }

    return redirect()
      ->back()
      ->with('toast_success', 'Tạo phiếu nhập kho thành công.');
  }
}
