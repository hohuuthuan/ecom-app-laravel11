<?php

namespace App\Services\Admin\Order;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use App\Helpers\PaginationHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
  public function getList(array $filters = []): LengthAwarePaginator
  {
    $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');

    $query = Order::query()
      ->select([
        'id',
        'code',
        'user_id',
        'status',
        'payment_method',
        'payment_status',
        'items_count',
        'grand_total_vnd',
        'placed_at',
        'created_at'
      ])
      ->with([
        'shipment:id,order_id,name,phone'
      ]);

    if (!empty($filters['keyword'])) {
      $kw = trim((string)$filters['keyword']);
      $query->where(function ($q) use ($kw) {
        $q->where('code', 'LIKE', "%{$kw}%");
      });
    }
    if (!empty($filters['payment_method'])) {
      $query->where('payment_method', (string)$filters['payment_method']);
    }
    if (!empty($filters['payment_status'])) {
      $query->where('payment_status', (string)$filters['payment_status']);
    }
    if (!empty($filters['status'])) {
      $query->where('status', (string)$filters['status']);
    }
    if (!empty($filters['created_from'])) {
      $fromUtc = \Carbon\Carbon::createFromFormat('Y-m-d', (string)$filters['created_from'], $tz)
        ->startOfDay()->utc();
      $query->where('placed_at', '>=', $fromUtc);
    }
    if (!empty($filters['created_to'])) {
      $toUtc = \Carbon\Carbon::createFromFormat('Y-m-d', (string)$filters['created_to'], $tz)
        ->endOfDay()->utc();
      $query->where('placed_at', '<=', $toUtc);
    }

    $perPage = (int)($filters['per_page'] ?? 10);
    if ($perPage <= 0) {
      $perPage = 10;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    $orders = $query->orderByDesc('placed_at')->paginate($perPage);

    return PaginationHelper::appendQuery($orders);
  }

  public function getWarehouseOrders(array $filters = []): LengthAwarePaginator
  {
    $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');

    $query = Order::query()
      ->select([
        'id',
        'code',
        'user_id',
        'status',
        'payment_method',
        'payment_status',
        'items_count',
        'grand_total_vnd',
        'placed_at',
        'created_at'
      ])
      ->with([
        'shipment:id,order_id,name,phone'
      ])
      // KHÔNG lấy đơn PENDING
      ->where(function ($q) {
        $q->where('status', '!=', 'PENDING')
          ->orWhereNull('status');
      });

    if (!empty($filters['keyword'])) {
      $kw = trim((string)$filters['keyword']);
      $query->where(function ($q) use ($kw) {
        $q->where('code', 'LIKE', "%{$kw}%");
      });
    }

    if (!empty($filters['payment_method'])) {
      $query->where('payment_method', (string)$filters['payment_method']);
    }

    if (!empty($filters['payment_status'])) {
      $query->where('payment_status', (string)$filters['payment_status']);
    }

    // Nếu sau này bạn muốn cho kho lọc riêng theo status khác PENDING vẫn được
    if (!empty($filters['status'])) {
      $query->where('status', (string)$filters['status']);
    }

    if (!empty($filters['created_from'])) {
      $fromUtc = Carbon::createFromFormat('Y-m-d', (string)$filters['created_from'], $tz)
        ->startOfDay()
        ->utc();
      $query->where('placed_at', '>=', $fromUtc);
    }

    if (!empty($filters['created_to'])) {
      $toUtc = Carbon::createFromFormat('Y-m-d', (string)$filters['created_to'], $tz)
        ->endOfDay()
        ->utc();
      $query->where('placed_at', '<=', $toUtc);
    }

    $perPage = (int)($filters['per_page'] ?? 10);
    if ($perPage <= 0) {
      $perPage = 10;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    // ƯU TIÊN: PROCESSING trước, rồi các trạng thái khác
    $query
      ->orderByRaw("CASE WHEN status = 'PROCESSING' THEN 0 ELSE 1 END")
      ->orderByDesc('placed_at');

    $orders = $query->paginate($perPage);

    return PaginationHelper::appendQuery($orders);
  }

  public function getWarehouseOrderDetail(string $orderId): array
  {
    $order = Order::with([
      'user',
      'items.product',
      'shipment',
      'discount',
      'statusHistories' => function ($query): void {
        $query->orderBy('created_at', 'desc');
      },
    ])->findOrFail($orderId);

    $items = $order->items;

    // Nếu đơn không có item thì vẫn cần trả về danh sách shipper
    if ($items->isEmpty()) {
      $shippers = User::query()
        ->whereHas('roles', function ($q): void {
          $q->whereIn('name', ['Admin', 'Warehouse Manager', 'Shipper']);
        })
        ->orderBy('name')
        ->get(['id', 'name']);

      return [$order, [], [], $shippers];
    }

    $productIds   = $items->pluck('product_id')->unique()->values()->all();
    $warehouseIds = $items->pluck('warehouse_id')->unique()->values()->all();

    // ========= TỒN KHO: đọc từ bảng stocks =========
    $stocks = DB::table('stocks')
      ->select(['product_id', 'warehouse_id', 'on_hand'])
      ->whereIn('product_id', $productIds)
      ->whereIn('warehouse_id', $warehouseIds)
      ->get();

    $stockMap = [];
    foreach ($stocks as $row) {
      $pid = (string) $row->product_id;
      $wid = (string) $row->warehouse_id;

      if (!isset($stockMap[$pid])) {
        $stockMap[$pid] = [];
      }

      $stockMap[$pid][$wid] = (int) $row->on_hand;
    }

    // ========= PHÂN BỔ LÔ: tính theo batch_stocks + batches =========
    $itemBatches = [];

    foreach ($items as $item) {
      $pid  = (string) $item->product_id;
      $wid  = (string) $item->warehouse_id;
      $need = (int) $item->quantity;

      if ($need <= 0) {
        $itemBatches[(string) $item->id] = [];
        continue;
      }

      $batchRows = DB::table('batch_stocks as bs')
        ->join('batches as b', 'b.id', '=', 'bs.batch_id')
        ->select([
          'bs.batch_id',
          'bs.on_hand',
          'b.import_date',
          'b.quantity as batch_quantity',
          'b.import_price_vnd',
          'b.code',
        ])
        ->where('bs.product_id', $pid)
        ->where('bs.warehouse_id', $wid)
        ->orderBy('b.import_date')
        ->get();

      if ($batchRows->isEmpty()) {
        $itemBatches[(string) $item->id] = [];
        continue;
      }

      $allocations = [];

      foreach ($batchRows as $row) {
        if ($need <= 0) {
          break;
        }

        $available = (int) $row->on_hand;

        if ($available <= 0) {
          continue;
        }

        $take = $need > $available ? $available : $need;

        $allocations[] = [
          'batch_id'           => (string) $row->batch_id,
          'import_date'        => $row->import_date,
          'quantity_allocated' => $take,
          'unit_cost_vnd'      => (int) $row->import_price_vnd,
          'batch_quantity'     => (int) $row->batch_quantity,
          'batch_available'    => $available - $take,
          'code'               => (string) ($row->code ?? ''),
        ];

        $need -= $take;
      }

      $itemBatches[(string) $item->id] = $allocations;
    }

    // ========= Lấy danh sách user có 1 trong các role: Admin, Warehouse Manager, Shipper =========
    $shippers = User::query()
      ->whereHas('roles', function ($q): void {
        $q->whereIn('name', ['Admin', 'Warehouse Manager', 'Shipper']);
      })
      ->orderBy('name')
      ->get(['id', 'name']);

    return [$order, $stockMap, $itemBatches, $shippers];
  }

  // public function getWarehouseOrderDetail(string $orderId): array
  // {
  //   $order = Order::with([
  //     'user',
  //     'items.product',
  //     'shipment',
  //     'discount',
  //     'statusHistories' => function ($query) {
  //       $query->orderBy('created_at', 'desc');
  //     },
  //   ])->findOrFail($orderId);

  //   $items = $order->items;

  //   if ($items->isEmpty()) {
  //     return [$order, [], []];
  //   }

  //   $productIds   = $items->pluck('product_id')->unique()->values()->all();
  //   $warehouseIds = $items->pluck('warehouse_id')->unique()->values()->all();

  //   // ========= TỒN KHO: đọc từ bảng stocks =========
  //   // Không dùng reserved nữa, chỉ hiển thị on_hand hiện tại
  //   $stocks = DB::table('stocks')
  //     ->select(['product_id', 'warehouse_id', 'on_hand'])
  //     ->whereIn('product_id', $productIds)
  //     ->whereIn('warehouse_id', $warehouseIds)
  //     ->get();

  //   // $stockMap[product_id][warehouse_id] = on_hand
  //   $stockMap = [];
  //   foreach ($stocks as $row) {
  //     $pid = (string) $row->product_id;
  //     $wid = (string) $row->warehouse_id;

  //     if (!isset($stockMap[$pid])) {
  //       $stockMap[$pid] = [];
  //     }

  //     $stockMap[$pid][$wid] = (int) $row->on_hand;
  //   }

  //   // ========= PHÂN BỔ LÔ: tính theo batch_stocks + batches (không dùng order_batches) =========
  //   // $itemBatches[order_item_id] = [...]
  //   $itemBatches = [];

  //   foreach ($items as $item) {
  //     $pid  = (string) $item->product_id;
  //     $wid  = (string) $item->warehouse_id;
  //     $need = (int) $item->quantity;

  //     if ($need <= 0) {
  //       $itemBatches[(string) $item->id] = [];
  //       continue;
  //     }

  //     // Lấy tất cả lô của sản phẩm này trong kho này, sắp theo ngày nhập
  //     $batchRows = DB::table('batch_stocks as bs')
  //       ->join('batches as b', 'b.id', '=', 'bs.batch_id')
  //       ->select([
  //         'bs.batch_id',
  //         'bs.on_hand',
  //         'b.import_date',
  //         'b.quantity as batch_quantity',
  //         'b.import_price_vnd',
  //         'b.code',
  //       ])
  //       ->where('bs.product_id', $pid)
  //       ->where('bs.warehouse_id', $wid)
  //       ->orderBy('b.import_date')
  //       ->get();

  //     if ($batchRows->isEmpty()) {
  //       $itemBatches[(string) $item->id] = [];
  //       continue;
  //     }

  //     $allocations = [];

  //     foreach ($batchRows as $row) {
  //       if ($need <= 0) {
  //         break;
  //       }

  //       $available = (int) $row->on_hand; // không trừ reserved nữa

  //       if ($available <= 0) {
  //         continue;
  //       }

  //       $take = $need > $available ? $available : $need;

  //       $allocations[] = [
  //         'batch_id'           => (string) $row->batch_id,
  //         'import_date'        => $row->import_date,
  //         'quantity_allocated' => $take,
  //         'unit_cost_vnd'      => (int) $row->import_price_vnd,
  //         'batch_quantity'     => (int) $row->batch_quantity,
  //         // Còn lại giả lập sau khi xuất cho đơn này
  //         'batch_available'    => $available - $take,
  //         'code'               => (string) ($row->code ?? ''),
  //       ];

  //       $need -= $take;
  //     }

  //     // Nếu không đủ hàng thì vẫn hiển thị những gì đã "allocate" được,
  //     // còn phần thiếu kho sẽ phải xử lý sau khi bạn bổ sung nghiệp vụ.
  //     $itemBatches[(string) $item->id] = $allocations;
  //   }

  //   return [$order, $stockMap, $itemBatches];
  // }
}
