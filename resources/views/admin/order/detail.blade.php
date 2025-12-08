@extends('layouts.admin')

@section('title','Products: Chi tiết đơn hàng')

@section('body_class','order-detail-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item">
      <a href="{{ route('admin.dashboard') }}">Admin</a>
    </li>
    <li class="breadcrumb-item">
      <a href="{{ route('admin.order.index') }}">Danh sách đơn hàng</a>
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
'unpaid' => 'Chưa thanh toán',
'pending' => 'Chờ thanh toán',
'paid' => 'Đã thanh toán',
'refunded' => 'Đã hoàn tiền',
];

$paymentStatusClassMap = [
'unpaid' => 'text-bg-secondary',
'pending' => 'text-bg-warning',
'paid' => 'text-bg-success',
'refunded' => 'text-bg-info',
];

$paymentStatusText = $paymentStatusTextMap[$paymentStatus] ?? ucfirst($paymentStatus);
$paymentStatusClass = $paymentStatusClassMap[$paymentStatus] ?? 'text-bg-secondary';

$shipment = $order->shipment;
$user = $order->user;

$status = strtolower((string) $order->status);

// Nhãn hiển thị theo bộ status chuẩn
$statusLabel = match ($status) {
'pending' => 'Chờ xử lý',
'processing'=> 'Tiếp nhận đơn, chuyển đơn sang đơn vị kho',
'picking' => 'Đang chuẩn bị hàng',
'shipping' => 'Đã giao cho đơn vị vận chuyển',
'completed' => 'Hoàn tất đơn hàng',
'cancelled' => 'Hủy đơn hàng',
'delivery_failed' => 'Giao hàng thất bại',
'returned' => 'Hoàn / trả hàng',
'confirmed' => 'Đã xác nhận (legacy)',
'shipped' => 'Đã giao cho ĐVVC (legacy)',
'delivered' => 'Đã giao hàng (legacy)',
default => strtoupper((string) $order->status),
};


// Màu badge theo trạng thái
$statusClass = match ($status) {
'pending' => 'badge-status--warning',
'processing',
'picking',
'shipping',
'confirmed',
'shipped',
'delivered' => 'badge-status--primary',
'completed' => 'badge-status--success', 
'cancelled',
'delivery_failed',
'returned' => 'badge-status--danger',
default => 'badge-status--secondary',
};

$current = strtoupper((string) $order->status);
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

    <div class="d-flex align-items-center gap-2">
      <span
        id="statusBadge"
        class="badge rounded-pill badge-status {{ $statusClass }}">
        {{ $statusLabel }}
      </span>

      <form
        method="POST"
        action="{{ route('admin.order.changeStatus', $order->id) }}"
        class="d-flex align-items-center gap-2 no-print">
        @csrf
        @method('PATCH')

        <div class="admin-select-status-order">
          <select name="status" class="form-select form-select-sm setupSelect2">
            <option value="PENDING" @selected($current==='PENDING' )>
              Chờ xử lý
            </option>
            <option value="PROCESSING" @selected($current==='PROCESSING' )>
              Tiếp nhận đơn, chuyển đơn sang đơn vị kho
            </option>
            <option value="CANCELLED" @selected($current==='CANCELLED' )>
              Hủy đơn hàng
            </option>
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
        {{-- <div class="mini">
          Đơn vị VC:
          {{ $shipment?->courier_name ?? '—' }}
      </div> --}}
    </div>
  </div>
</div>
</div>

{{-- SẢN PHẨM --}}
<div class="card mt-3">
  <div class="card-body">
    <div class="section-title mb-3">Sản phẩm</div>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>TÊN SẢN PHẨM</th>
            <th>MÃ ISBN13</th>
            <th class="text-center">SỐ LƯỢNG</th>
            <th class="text-end">ĐƠN GIÁ</th>
            <th class="text-end">GIẢM</th>
            <th class="text-end">TẠM TÍNH</th>
          </tr>
        </thead>
        <tbody id="itemBody">
          @forelse($order->items as $item)
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
            <td>
              {{ $item->isbn13_snapshot ?? $item->product_id }}
            </td>
            <td class="text-center">
              {{ $item->quantity }}
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
            <td colspan="7" class="text-center text-muted mini">
              Không có sản phẩm nào trong đơn hàng này.
            </td>
          </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr>
            <td colspan="6" class="text-end">Tạm tính</td>
            <td class="text-end">
              {{ $fmtVnd($order->subtotal_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="6" class="text-end">Phí vận chuyển</td>
            <td class="text-end">
              {{ $fmtVnd($order->shipping_fee_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="6" class="text-end">Giảm giá</td>
            <td class="text-end">
              {{ $fmtVnd($order->discount_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="6" class="text-end">Tổng cộng</td>
            <td class="text-end h5 mb-0 text-success">
              {{ $fmtVnd($order->grand_total_vnd) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

{{-- TIMELINE + GHI CHÚ --}}
<div class="row g-3 mt-1">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Dòng thời gian</div>

        @php
        $timelineItems = collect();
        $createdAt = $order->placed_at ?? $order->created_at;

        if ($createdAt) {
        $timelineItems->push([
        'label' => 'Đã tạo đơn hàng',
        'time' => $createdAt,
        ]);
        }

        foreach ($order->statusHistories as $log) {
        $label = match ($log->status) {
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận đơn',
        'processing'=> 'Tiếp nhận đơn, chuyển đơn sang đơn vị kho',
        'shipping' => 'Đang chuẩn bị hàng',
        'delivered' => 'Đã giao cho đơn vị vận chuyển',
        'completed' => 'Hoàn tất đơn hàng',
        'cancelled' => 'Đã hủy đơn',
        default => ucfirst($log->status),
        };

        $timelineItems->push([
        'label' => $label,
        'time' => $log->created_at,
        ]);
        }

        $timelineItems = $timelineItems->sortByDesc('time')->values();
        @endphp

        @if($timelineItems->isNotEmpty())
        <div class="timeline">
          @foreach($timelineItems as $row)
          @php
          $dotClass = match ($row['label']) {
          'Đã tạo đơn hàng' => 'timeline-item--primary',
          'Đã giao cho đơn vị vận chuyển',
          'Hoàn tất đơn hàng' => 'timeline-item--success',
          'Đã hủy đơn' => 'timeline-item--danger',
          default => 'timeline-item--warning',
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
        <div class="section-title mb-3">Ghi chú nội bộ (Feature comming soon...)</div>
        <div class="mb-2 mini text-muted">Chỉ hiển thị cho admin</div>

        <ul class="list-group mb-3" id="noteList">
          @if($order->buyer_note)
          <li class="list-group-item d-flex justify-content-between align-items-start">
            <div class="me-2">
              {{ $order->buyer_note }}
              <div class="mini text-muted">
                {{ optional($order->placed_at ?? $order->created_at)->format('d/m/Y H:i') }} • Khách hàng
              </div>
            </div>
            <button class="btn btn-sm btn-outline-danger no-print" disabled>
              <i class="bi bi-trash"></i>
            </button>
          </li>
          @endif
        </ul>

        @if(!$order->buyer_note)
        <div class="mini text-muted mb-3">
          Chưa có ghi chú từ khách hàng.
        </div>
        @endif

        <div class="input-group">
          <input
            type="text"
            class="form-control"
            placeholder="Thêm ghi chú (TODO API)..."
            id="noteInput"
            disabled>
          <button class="btn btn-primary" id="noteAdd" disabled>
            <i class="bi bi-plus-lg"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@include('partials.ui.confirm-modal')
@endsection