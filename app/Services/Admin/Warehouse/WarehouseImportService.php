<?php

namespace App\Services\Admin\Warehouse;

use App\Models\Warehouse;
use App\Models\PurchaseReceipt;
use App\Models\Stock;
use App\Models\Product;
use App\Services\Admin\Warehouse\WarehouseActivityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WarehouseImportService
{
  /**
   * Tạo phiếu nhập kho từ dữ liệu đã validate
   *
   * @param  array  $data
   * @return bool
   */
  public function createPurchaseReceipt(array $data): bool
  {
    try {
      DB::transaction(function () use ($data) {
        $items = $data['items'];
        $userId = Auth::id();
        $now = now();

        /** @var \App\Models\Warehouse|null $warehouse */
        $warehouse = Warehouse::query()->orderBy('id')->first();

        if (!$warehouse) {
          throw new \RuntimeException('Không tìm thấy kho để nhập hàng.');
        }

        $warehouseId = $warehouse->id;

        $receiptId = (string) Str::uuid();
        $receivedAt = Carbon::parse($data['receipt_date'])->startOfDay();
        $receiptCode = $this->generateUniqueReceiptCode();

        // ================== TÍNH TỔNG & CHUẨN BỊ ARRAYS ==================
        $subTotalVnd = 0;

        $receiptItemsRows = [];
        $batchRows = [];
        $batchStockRows = [];
        $movementRows = [];
        $stockTotalsByProduct = [];

        foreach ($items as $item) {
          $productId = $item['product_id'];
          $qtyDoc = (int) $item['qty_document'];
          $qtyActual = (int) $item['qty_real'];
          $unitPrice = (int) $item['price'];
          $note = $item['note'] ?? null;

          $lineTotal = $unitPrice * $qtyActual;
          $subTotalVnd += $lineTotal;

          $itemId = (string) Str::uuid();
          $batchId = (string) Str::uuid();

          // -------- purchase_receipt_items --------
          $receiptItemsRows[] = [
            'id' => $itemId,
            'purchase_receipt_id' => $receiptId,
            'product_id' => $productId,
            'import_price_vnd' => $unitPrice,
            'qty_doc' => $qtyDoc,
            'qty_actual' => $qtyActual,
            'notes' => $note,
            'created_at' => $now,
            'updated_at' => $now,
          ];

          // -------- batches --------
          $batchRows[] = [
            'id' => $batchId,
            'purchase_receipt_item_id' => $itemId,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity' => $qtyActual,
            'import_price_vnd' => $unitPrice,
            'import_date' => $receivedAt->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
          ];

          // -------- batch_stocks (tồn theo lô) --------
          $batchStockRows[] = [
            'batch_id' => $batchId,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'on_hand' => $qtyActual,
            'reserved' => 0,
            'created_at' => $now,
            'updated_at' => $now,
          ];

          // -------- stock_movements (nhật ký nhập) --------
          $movementRows[] = [
            'id' => (string) Str::uuid(),
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'batch_id' => $batchId,
            'type' => 'receipt',
            'qty' => $qtyActual,
            'unit_cost_vnd' => $unitPrice,
            'related_type' => 'purchase_receipt',
            'related_id' => $receiptId,
            'note' => $note,
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
          ];

          if (!isset($stockTotalsByProduct[$productId])) {
            $stockTotalsByProduct[$productId] = 0;
          }

          $stockTotalsByProduct[$productId] += $qtyActual;
        }

        // ================== purchase_receipts (header phiếu) ==================
        $receiptRow = [
          'id' => $receiptId,
          'receipt_code' => $receiptCode,
          'publisher_id' => $data['publisher_id'],
          'warehouse_id' => $warehouseId,
          'received_at' => $receivedAt,
          'name_of_delivery_person' => $data['deliver_name'],
          'delivery_unit' => $data['deliver_unit'],
          'address_of_delivery_person' => $data['deliver_address'],
          'delivery_note_number' => $data['delivery_number'],
          'tax_identification_number' => null,
          'sub_total_vnd' => $subTotalVnd,
          'created_by' => $userId,
          'created_at' => $now,
          'updated_at' => $now,
        ];

        // ================== INSERT THEO THỨ TỰ ==================
        DB::table('purchase_receipts')->insert($receiptRow);

        if (!empty($receiptItemsRows)) {
          DB::table('purchase_receipt_items')->insert($receiptItemsRows);
        }

        if (!empty($batchRows)) {
          DB::table('batches')->insert($batchRows);
        }

        if (!empty($batchStockRows)) {
          DB::table('batch_stocks')->insert($batchStockRows);
        }

        // -------- cập nhật bảng stocks tổng theo product/warehouse --------
        foreach ($stockTotalsByProduct as $productId => $qtyIncrease) {
          $stock = DB::table('stocks')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

          if ($stock) {
            DB::table('stocks')
              ->where('product_id', $productId)
              ->where('warehouse_id', $warehouseId)
              ->update([
                'on_hand' => $stock->on_hand + $qtyIncrease,
                'updated_at' => $now,
              ]);
          } else {
            DB::table('stocks')->insert([
              'product_id' => $productId,
              'warehouse_id' => $warehouseId,
              'on_hand' => $qtyIncrease,
              'reserved' => 0,
              'reorder_point' => null,
              'reorder_qty' => null,
              'created_at' => $now,
              'updated_at' => $now,
            ]);
          }
        }

        if (!empty($movementRows)) {
          DB::table('stock_movements')->insert($movementRows);
        }
        WarehouseActivityService::log('Tạo phiếu nhập ' . $receiptCode);
      });

      return true;
    } catch (\Throwable $e) {
      Log::error('Warehouse import failed', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return false;
    }
  }

  public function getReceiptList(array $filters = [])
  {
    $query = PurchaseReceipt::query()
      ->select([
        'id',
        'receipt_code',
        'publisher_id',
        'warehouse_id',
        'received_at',
        'delivery_note_number',
        'name_of_delivery_person',
        'sub_total_vnd',
        'created_by',
        'created_at',
      ])
      ->with([
        'publisher:id,name',
        'warehouse:id,name',
        'createdBy:id,name',
      ]);

    if (!empty($filters['keyword'])) {
      $kw = trim((string) $filters['keyword']);
      $query->where(function ($q) use ($kw) {
        $q->where('delivery_note_number', 'LIKE', "%{$kw}%")
          ->orWhere('name_of_delivery_person', 'LIKE', "%{$kw}%");
      });
    }

    if (!empty($filters['publisher_id'])) {
      $query->where('publisher_id', (string) $filters['publisher_id']);
    }

    if (!empty($filters['warehouse_id'])) {
      $query->where('warehouse_id', (string) $filters['warehouse_id']);
    }

    $perPage = (int)($filters['per_page'] ?? 20);
    if ($perPage <= 0) {
      $perPage = 20;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    $receipts = $query
      ->orderByDesc('received_at')
      ->orderByDesc('created_at')
      ->paginate($perPage);

    return \App\Helpers\PaginationHelper::appendQuery($receipts);
  }

  public function getReceiptDetail(string $id): ?PurchaseReceipt
  {
    return PurchaseReceipt::query()
      ->with([
        'publisher:id,name',
        'warehouse:id,name',
        'createdBy:id,name',
        'items' => function ($q) {
          $q->select([
            'id',
            'purchase_receipt_id',
            'product_id',
            'import_price_vnd',
            'qty_doc',
            'qty_actual',
            'notes',
          ])->with([
            'product:id,code,title,unit',
          ]);
        },
      ])
      ->find($id);
  }

  private function generateUniqueReceiptCode(): string
  {
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $digits = '0123456789';
    $alnum = $letters . $digits;

    do {
      $prefix =
        $letters[random_int(0, strlen($letters) - 1)] .
        $letters[random_int(0, strlen($letters) - 1)];

      $mid =
        $digits[random_int(0, strlen($digits) - 1)] .
        $digits[random_int(0, strlen($digits) - 1)];

      $suffix = '';
      for ($i = 0; $i < 10; $i++) {
        $suffix .= $alnum[random_int(0, strlen($alnum) - 1)];
      }

      $code = $prefix . '-' . $mid . '-' . $suffix;
    } while (
      PurchaseReceipt::query()
      ->where('receipt_code', $code)
      ->exists()
    );

    return $code;
  }

  public function getInventoryOverview(array $filters = []): array
  {
    // ========== SẢN PHẨM HẾT HÀNG ==========
    $outOfStocks = Product::query()
      ->where('status', 'ACTIVE')
      ->where(function ($q) {
        $q->whereDoesntHave('stocks')
          ->orWhereHas('stocks', function ($q2) {
            $q2->select('product_id', DB::raw('SUM(on_hand) as total_on_hand'))
              ->groupBy('product_id')
              ->havingRaw('SUM(on_hand) <= 0');
          });
      })
      ->with(['stocks.warehouse:id,name'])
      ->orderBy('title')
      ->limit(50)
      ->get();

    // ========== SẢN PHẨM SẮP HẾT ==========
    $lowStocks = Stock::query()
      ->with([
        'product:id,code,title',
        'warehouse:id,name',
      ])
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
      ->orderBy('on_hand')
      ->limit(50)
      ->get();

    // ========== PHÂN TRANG BẢNG CHI TIẾT TỒN ==========
    $perPage = (int) ($filters['per_page'] ?? 20);
    if ($perPage <= 0) {
      $perPage = 20;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    $inventoryQuery = Product::query()
      ->where('status', 'ACTIVE')
      ->withSum('stocks as total_on_hand', 'on_hand')
      ->with(['stocks.warehouse:id,name']);

    if (!empty($filters['keyword'])) {
      $kw = trim((string) $filters['keyword']);
      $inventoryQuery->where(function ($q) use ($kw) {
        $q->where('code', 'LIKE', "%{$kw}%")
          ->orWhere('title', 'LIKE', "%{$kw}%");
      });
    }

    $status    = $filters['status'] ?? null;
    $threshold = 50;

    if ($status === 'out') {
      $inventoryQuery->where(function ($q) {
        $q->whereDoesntHave('stocks')
          ->orWhereHas('stocks', function ($q2) {
            $q2->select('product_id', DB::raw('SUM(on_hand) as total_on_hand'))
              ->groupBy('product_id')
              ->havingRaw('SUM(on_hand) <= 0');
          });
      });
    } elseif ($status === 'low') {
      $inventoryQuery->whereHas('stocks', function ($q2) use ($threshold) {
        $q2->select('product_id', DB::raw('SUM(on_hand) as total_on_hand'))
          ->groupBy('product_id')
          ->havingRaw('SUM(on_hand) > 0 AND SUM(on_hand) <= ?', [$threshold]);
      });
    } elseif ($status === 'normal') {
      $inventoryQuery->whereHas('stocks', function ($q2) use ($threshold) {
        $q2->select('product_id', DB::raw('SUM(on_hand) as total_on_hand'))
          ->groupBy('product_id')
          ->havingRaw('SUM(on_hand) > ?', [$threshold]);
      });
    }

    $inventoryProducts = $inventoryQuery
      ->orderBy('title')
      ->paginate($perPage);

    return [
      'lowStocks'         => $lowStocks,
      'outOfStocks'       => $outOfStocks,
      'inventoryProducts' => $inventoryProducts,
    ];
  }
}
