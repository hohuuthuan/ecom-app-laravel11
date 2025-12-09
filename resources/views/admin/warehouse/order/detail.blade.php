@extends('layouts.warehouse')

@section('title','Đơn hàng kho: Chi tiết đơn hàng')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item">
      <a href="{{ route('warehouse.dashboard') }}">Kho hàng</a>
    </li>
    <li class="breadcrumb-item">
      <a href="{{ route('warehouse.orders') }}">Danh sách đơn hàng</a>
    </li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">
      Chi tiết đơn hàng
    </li>
  </ol>
</nav>

@php
  /** @var \App\Models\Order $order */
  $fmtVnd = fn($n) => number_format((int) $n, 0, ',', '.') . ' VNĐ';

  $paymentStatus = strtolower((string) $order->payment_status);

  $paymentStatusTextMap = [
    'unpaid'   => 'Chưa thanh toán',
    'pending'  => 'Chờ thanh toán',
    'paid'     => 'Đã thanh toán',
    'refunded' => 'Đã hoàn tiền',
  ];

  $paymentStatusClassMap = [
    'unpaid'   => 'text-bg-secondary',
    'pending'  => 'text-bg-warning',
    'paid'     => 'text-bg-success',
    'refunded' => 'text-bg-info',
  ];

  $paymentStatusText  = $paymentStatusTextMap[$paymentStatus] ?? ucfirst($paymentStatus);
  $paymentStatusClass = $paymentStatusClassMap[$paymentStatus] ?? 'text-bg-secondary';

  $shipment = $order->shipment;
  $user     = $order->user;

  $status = strtolower((string) $order->status);

  // Label trạng thái theo góc nhìn kho
  $statusLabel = match ($status) {
    'pending'         => 'Chờ xử lý',
    'processing'      => 'Đang chờ tiếp nhận',
    'picking'         => 'Đang chuẩn bị hàng',
    'shipping'        => 'Đã giao cho đơn vị vận chuyển',
    'completed'       => 'Hoàn tất',
    'cancelled'       => 'Đã hủy đơn hàng',
    'delivery_failed' => 'Giao hàng thất bại',
    'returned'        => 'Hoàn / trả hàng',
    'confirmed'       => 'Đã xác nhận (cũ)',
    'shipped'         => 'Đã giao cho ĐVVC (cũ)',
    'delivered'       => 'Đã giao hàng (cũ)',
    default           => 'Không xác định',
  };

  // Màu badge trạng thái kho
  $statusClass = match ($status) {
    'pending'                                => 'badge-status--warning',
    'processing', 'picking', 'shipping',
    'confirmed', 'shipped', 'delivered'      => 'badge-status--primary',
    'completed'                              => 'badge-status--success',
    'cancelled', 'delivery_failed', 'returned' => 'badge-status--danger',
    default                                  => 'badge-status--secondary',
  };

  // Map từ status đơn -> warehouse_status cho select
  $warehouseStatusMap = [
    'processing' => 'RECEIVING_PROCESS',
    'picking'    => 'PREPARING_ITEMS',
    'shipping'   => 'HANDED_OVER_CARRIER',
    'completed'  => 'ORDER_COMPLETED',
  ];
  $currentWarehouseStatus = $warehouseStatusMap[$status] ?? null;

  // Thông tin giao hàng
  $shippingType     = strtoupper((string) (old('shipping_type', $order->shipping_type ?? 'INTERNAL')));
  $currentShipperId = (string) (old('shipper_id', $order->shipper_id ?? ''));
@endphp

<div class="card mb-3">
  <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <h1 class="h4 mb-1">
        MÃ ĐƠN HÀNG
        <span id="orderCode" class="copyable" title="Nhấp để sao chép">
          # {{ $order->code }}
        </span>
      </h1>
      <div class="text-muted">
        Tạo lúc:
        <span>{{ optional($order->placed_at ?? $order->created_at)->format('d/m/Y H:i A') }}</span>
        •
        Cập nhật:
        <span>{{ optional($order->updated_at)->format('d/m/Y H:i A') }}</span>
      </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-end gap-3">
      <span
        id="statusBadge"
        class="badge rounded-pill badge-status {{ $statusClass }}">
        {{ $statusLabel }}
      </span>

      {{-- Form cập nhật trạng thái kho --}}
      <form
        method="POST"
        action="{{ route('warehouse.order.changeStatus', $order->id) }}"
        class="d-flex align-items-center gap-2">
        @csrf
        @method('PATCH')

        <div class="admin-select-status-order">
          <select name="warehouse_status" class="form-select form-select-sm setupSelect2">
            <option
              value="RECEIVING_PROCESS"
              @selected($currentWarehouseStatus === 'RECEIVING_PROCESS')>
              Đang chờ tiếp nhận
            </option>
            <option
              value="PREPARING_ITEMS"
              @selected($currentWarehouseStatus === 'PREPARING_ITEMS')>
              Đang chuẩn bị hàng
            </option>
            <option
              value="HANDED_OVER_CARRIER"
              @selected($currentWarehouseStatus === 'HANDED_OVER_CARRIER')>
              Đã giao cho đơn vị vận chuyển
            </option>

            @if($shippingType === 'EXTERNAL')
              <option
                value="ORDER_COMPLETED"
                @selected($currentWarehouseStatus === 'ORDER_COMPLETED')>
                Đơn hàng hoàn tất
              </option>
            @endif
          </select>
        </div>

        <button type="submit" class="btn btn-primary btn-admin">
          Cập nhật
        </button>
      </form>

      <span class="vr d-none d-md-block"></span>

      <div class="text-end">
        <div class="order-detail-grand-total-title">TỔNG TIỀN</div>
        <div class="h5 mb-0 order-detail-grand-total-number" id="grandTotal">
          {{ $fmtVnd($order->grand_total_vnd) }}
        </div>
      </div>
    </div>
  </div>
</div>

{{-- 2 KHỐI: KHÁCH HÀNG + GIAO HÀNG --}}
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="section-title mb-0">THÔNG TIN KHÁCH HÀNG</div>
          <div class="mini d-flex align-items-center gap-2">
            <div class="section-title mb-0">TRẠNG THÁI THANH TOÁN:</div>
            <span class="badge {{ $paymentStatusClass }}">
              {{ $paymentStatusText }}
            </span>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3">
          <div
            class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
            style="width:48px;height:48px">
            <i class="bi bi-person-fill order-detail-icon-user"></i>
          </div>
          <div>
            <div class="fw-semibold">
              {{ $user?->name ?? 'Khách hàng' }}
            </div>
            <div class="mt-2">
              @if($user?->email)
                <div class="mini">
                  <i class="bi bi-envelope"></i>
                  <a href="mailto:{{ $user->email }}">
                    {{ $user->email }}
                  </a>
                </div>
              @endif

              @if($user?->phone)
                <div class="mini">
                  <i class="bi bi-telephone"></i>
                  {{ $user->phone }}
                </div>
              @endif
            </div>
          </div>
        </div>

        <hr>

        <div class="section-title mb-2">Thanh toán</div>
        <div class="mini">
          Phương thức thanh toán:
          <b>{{ strtoupper($order->payment_method) }}</b>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">THÔNG TIN GIAO HÀNG</div>
        <h5>
          <i class="icon-delivery-address bi bi-geo-alt-fill"></i>
          Địa chỉ nhận hàng:
        </h5>

        @if($shipment)
          <div class="order-detail-shipment-address">{{ $shipment->address }}</div>
          @if($shipment->phone)
            <div class="mini mt-2">
              <i class="icon-telephone bi bi-telephone-fill"></i>
              {{ $shipment->phone }}
            </div>
          @endif
        @else
          <div>Chưa có thông tin giao hàng</div>
        @endif

        <hr>

        <div class="mini">
          Phí vận chuyển:
          {{ $fmtVnd($order->shipping_fee_vnd) }}
        </div>
      </div>
    </div>
  </div>
</div>

{{-- BẢNG SẢN PHẨM --}}
<div class="card mt-3">
  <div class="card-body">
    <div class="section-title mb-3">Sản phẩm</div>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>TÊN SẢN PHẨM</th>
            <th class="text-center">SỐ LƯỢNG</th>
            <th class="text-center">TỒN KHO</th>
            <th>CHI TIẾT LÔ HÀNG</th>
            <th class="text-end">ĐƠN GIÁ / 1 SP</th>
            <th class="text-end">GIẢM</th>
            <th class="text-end">TẠM TÍNH</th>
          </tr>
        </thead>
        <tbody id="itemBody">
          @forelse($order->items as $item)
            @php
              $pid = (string) $item->product_id;
              $wid = (string) $item->warehouse_id;
              $available = isset($stockMap[$pid][$wid]) ? $stockMap[$pid][$wid] : 0;
              $batchesForItem = $itemBatches[(string) $item->id] ?? [];
            @endphp
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                <div class="fw-semibold">
                  {{ $item->product_title_snapshot ?? $item->product->title ?? 'Sản phẩm' }}
                </div>
                <div class="text-muted mini">
                  ID: {{ $item->product_id }}
                </div>
              </td>
              <td class="text-center">
                {{ $item->quantity }}
              </td>
              <td class="text-center">
                <span class="badge bg-info-subtle text-info">
                  {{ $available }}
                </span>
              </td>
              <td>
                @if(count($batchesForItem) > 0)
                  <ul class="list-unstyled mb-0 mini">
                    @foreach($batchesForItem as $batch)
                      @php
                        $code = $batch['code'] ?? null;
                        $code = $code === null || $code === '' ? '###' : $code;
                      @endphp

                      <li class="mb-1">
                        <div>
                          <span class="fw-semibold">Lô:</span>
                          <span class="badge bg-light text-dark">
                            {{ $code }}
                          </span>
                        </div>
                        <div>
                          Lấy: <b>{{ $batch['quantity_allocated'] }}</b>
                          @if($batch['batch_available'] !== null)
                            • Còn lại: {{ $batch['batch_available'] }}
                          @endif
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <span class="text-muted mini">Chưa có phân bổ lô hàng.</span>
                @endif
              </td>
              <td class="text-end">
                {{ $fmtVnd($item->unit_price_vnd) }}
              </td>
              <td class="text-end">
                {{ $fmtVnd($item->discount_amount_vnd ?? 0) }}
              </td>
              <td class="text-end">
                {{ $fmtVnd($item->total_price_vnd) }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted mini">
                Không có sản phẩm nào trong đơn hàng này.
              </td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr>
            <td colspan="7" class="text-end">Tạm tính</td>
            <td class="text-end">
              {{ $fmtVnd($order->subtotal_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="7" class="text-end">Phí vận chuyển</td>
            <td class="text-end">
              {{ $fmtVnd($order->shipping_fee_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="7" class="text-end">Giảm giá</td>
            <td class="text-end">
              {{ $fmtVnd($order->discount_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="7" class="text-end">Tổng cộng</td>
            <td class="text-end h5 mb-0 text-success">
              {{ $fmtVnd($order->grand_total_vnd) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

{{-- TIMELINE + ĐƠN VỊ VẬN CHUYỂN --}}
<div class="row g-3 mt-1">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Dòng thời gian</div>

        @php
          $itemsTimeline = collect();
          $createdAt = $order->placed_at ?? $order->created_at;

          if ($createdAt) {
            $itemsTimeline->push([
              'label' => 'Đã tạo đơn hàng',
              'time'  => $createdAt,
            ]);
          }

          foreach ($order->statusHistories as $log) {
            $s = strtolower((string) $log->status);

            $label = match ($s) {
              'pending'         => 'Chờ xử lý',
              'confirmed'       => 'Đã xác nhận đơn',
              'processing'      => 'Đang chờ tiếp nhận',
              'picking'         => 'Đang chuẩn bị hàng',
              'shipping'        => 'Đã giao cho đơn vị vận chuyển',
              'shipped'         => 'Đã giao cho đơn vị vận chuyển (cũ)',
              'delivered'       => 'Đã giao hàng (cũ)',
              'completed'       => 'Hoàn tất đơn hàng',
              'cancelled'       => 'Đã hủy đơn',
              'delivery_failed' => 'Giao hàng thất bại',
              'returned'        => 'Hoàn / trả hàng',
              default           => ucfirst($s),
            };

            $itemsTimeline->push([
              'label' => $label,
              'time'  => $log->created_at,
            ]);
          }

          $itemsTimeline = $itemsTimeline->sortByDesc('time')->values();
        @endphp

        @if($itemsTimeline->isNotEmpty())
          <div class="timeline">
            @foreach($itemsTimeline as $row)
              @php
                $labelText = $row['label'];
                $dotClass = match ($labelText) {
                  'Đã tạo đơn hàng'                   => 'timeline-item--primary',
                  'Đã giao cho đơn vị vận chuyển',
                  'Đã giao cho đơn vị vận chuyển (cũ)',
                  'Đã giao hàng (cũ)',
                  'Hoàn tất đơn hàng'                 => 'timeline-item--success',
                  'Đã hủy đơn',
                  'Giao hàng thất bại',
                  'Hoàn / trả hàng'                   => 'timeline-item--danger',
                  default                              => 'timeline-item--warning',
                };
              @endphp

              <div class="timeline-item {{ $dotClass }}">
                <div class="fw-semibold">{{ $row['label'] }}</div>
                <div class="text-muted mini">
                  {{ $row['time']?->format('d/m/Y h:i A') }}
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-muted mini">
            Chưa có lịch sử trạng thái cho đơn hàng này.
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Đơn vị vận chuyển</div>

        <form
          method="POST"
          action="{{ route('warehouse.order.assignShipper', $order->id) }}"
          class="no-print">
          @csrf
          @method('PATCH')

          <div class="mb-3">
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="shipping_type"
                id="shippingTypeInternal"
                value="INTERNAL"
                {{ $shippingType === 'INTERNAL' ? 'checked' : '' }}>
              <label class="form-check-label" for="shippingTypeInternal">
                Shipper nội bộ
              </label>
            </div>

            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="shipping_type"
                id="shippingTypeExternal"
                value="EXTERNAL"
                {{ $shippingType === 'EXTERNAL' ? 'checked' : '' }}>
              <label class="form-check-label" for="shippingTypeExternal">
                Đơn vị vận chuyển khác
              </label>
            </div>
          </div>

          <div class="mb-3 js-internal-shipper {{ $shippingType !== 'INTERNAL' ? 'd-none' : '' }}">
            <label class="form-label">
              Người giao hàng (nội bộ)
            </label>

            <select
              name="shipper_id"
              class="form-select form-select-sm setupSelect2">
              <option value="">
                Chọn người giao hàng
              </option>

              @foreach($shippers as $shipper)
                <option
                  value="{{ $shipper->id }}"
                  {{ $currentShipperId === (string) $shipper->id ? 'selected' : '' }}>
                  {{ $shipper->name }}
                </option>
              @endforeach
            </select>
            @error('shipper_id')
              <div class="text-danger mini mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="text-end">
            <button
              type="submit"
              class="btn btn-primary btn-sm {{ $shippingType !== 'INTERNAL' ? 'd-none' : '' }}">
              Cập nhật
            </button>
          </div>
        </form>

        @if($shippingType === 'INTERNAL' && $currentShipperId !== '')
          <hr>
          @php
            $current = $shippers->firstWhere('id', $currentShipperId);
          @endphp
          <div class="mini">
            Đơn hàng đã được phân công cho:
            <b>{{ $current?->name ?? 'Không xác định' }}</b>
          </div>
        @elseif($shippingType === 'EXTERNAL')
          <hr>
          <div class="mini">
            Đơn đang được giao bởi đơn vị vận chuyển bên ngoài.
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@include('partials.ui.confirm-modal')
@endsection
@push('scripts')
<script>
  (function() {
    const internalRadio = document.getElementById('shippingTypeInternal');
    const externalRadio = document.getElementById('shippingTypeExternal');
    const internalBlock = document.querySelector('.js-internal-shipper');
    const form = internalBlock ? internalBlock.closest('form') : null;

    if (!internalRadio || !externalRadio || !internalBlock || !form) {
      return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const tokenInput = form.querySelector('input[name="_token"]');
    const csrf = tokenInput ? tokenInput.value : '';

    function syncUI() {
      const isInternal = internalRadio.checked;
      internalBlock.classList.toggle('d-none', !isInternal);

      if (submitBtn) {
        submitBtn.classList.toggle('d-none', !isInternal);
      }
    }

    internalRadio.addEventListener('change', syncUI);

    externalRadio.addEventListener('change', function() {
      syncUI();

      if (!externalRadio.checked || !csrf) {
        return;
      }

      fetch(form.getAttribute('action'), {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          shipping_type: 'EXTERNAL',
          shipper_id: null
        })
      })
      .then(function(res) {
        if (!res.ok) {
          throw new Error('Request failed');
        }
        return res.json();
      })
      .then(function() {
        window.location.reload();
      })
      .catch(function() {
        alert('Cập nhật đơn vị vận chuyển thất bại. Vui lòng thử lại.');
      });
    });

    syncUI();
  })();
</script>
@endpush
