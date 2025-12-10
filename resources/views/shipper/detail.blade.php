@extends('layouts.shipper')

@section('title','Shipper: Chi tiết đơn hàng')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item">
      <a href="{{ route('shipper.dashboard') }}">Shipper</a>
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

$statusRaw = strtoupper((string) $order->status);
$status    = strtolower($statusRaw);

// Label trạng thái ở góc độ giao hàng
$statusLabel = match ($status) {
  'pending'         => 'Chờ xử lý',
  'processing'      => 'Đang chờ tiếp nhận',
  'picking'         => 'Đang chuẩn bị hàng',
  'shipping'        => 'Đang giao',
  'completed'       => 'Giao thành công',
  'cancelled'       => 'Đã hủy đơn hàng',
  'delivery_failed' => 'Giao hàng thất bại',
  'returned'        => 'Hoàn / trả hàng',
  'confirmed'       => 'Đã xác nhận (cũ)',
  'shipped'         => 'Đã giao cho ĐVVC (cũ)',
  'delivered'       => 'Đã giao hàng (cũ)',
  default           => 'Không xác định',
};

$statusClass = match ($status) {
  'shipping'        => 'badge-status--primary',
  'completed'       => 'badge-status--success',
  'delivery_failed',
  'returned',
  'cancelled'       => 'badge-status--danger',
  default           => 'badge-status--secondary',
};

$isShipping     = $statusRaw === 'SHIPPING';
$isResultStatus = in_array($statusRaw, ['COMPLETED', 'DELIVERY_FAILED', 'RETURNED'], true);

$shipAddress = $order->receiver_address ?? $shipment?->address;
$shipPhone   = $order->receiver_phone ?? $shipment?->phone;
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

      @if($isShipping && !$isResultStatus)
      <div class="d-flex flex-wrap align-items-center gap-2 no-print">
        {{-- Nút giao hàng thành công --}}
        <form
          method="POST"
          action="{{ route('shipper.orders.changeStatus', $order->id) }}">
          @csrf
          @method('PATCH')
          <input type="hidden" name="status" value="COMPLETED">
          <button type="submit" class="btn btn-success btn-sm">
            Giao hàng thành công
          </button>
        </form>

        {{-- Nút giao hàng thất bại (mở modal nhập lý do) --}}
        <button
          type="button"
          class="btn btn-danger btn-sm"
          data-bs-toggle="modal"
          data-bs-target="#deliveryFailedModal">
          Giao hàng thất bại
        </button>
      </div>
      @endif

      <span class="vr d-none d-md-block"></span>

      <div class="text-end">
        <div class="order-detail-grand-total-title">TỔNG THANH TOÁN</div>
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
              {{ $user?->name ?? $order->receiver_name ?? 'Khách hàng' }}
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

              @if($user?->phone || $order->receiver_phone)
              <div class="mini">
                <i class="bi bi-telephone"></i>
                {{ $order->receiver_phone ?? $user?->phone }}
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

        @if($shipAddress)
        <div class="order-detail-shipment-address">
          {{ $shipAddress }}
        </div>
        @if($shipPhone)
        <div class="mini mt-2">
          <i class="icon-telephone bi bi-telephone-fill"></i>
          {{ $shipPhone }}
        </div>
        @endif
        @else
        <div>Chưa có thông tin giao hàng</div>
        @endif

        @if($order->note)
        <hr>
        <div class="mini">
          <span class="fw-semibold">Ghi chú từ khách:</span>
          <div>{{ $order->note }}</div>
        </div>
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
    <div class="section-title mb-3">Sản phẩm trong đơn</div>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>TÊN SẢN PHẨM</th>
            <th class="text-center">SỐ LƯỢNG</th>
            <th class="text-end">ĐƠN GIÁ</th>
            <th class="text-end">TẠM TÍNH</th>
          </tr>
        </thead>
        <tbody>
          @forelse($order->items as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
              <div class="fw-semibold">
                {{ $item->product_title_snapshot ?? $item->product->title ?? 'Sản phẩm' }}
              </div>
              <div class="text-muted mini">
                Mã SP: {{ $item->product_id }}
              </div>
            </td>
            <td class="text-center">
              {{ $item->quantity }}
            </td>
            <td class="text-end">
              {{ $fmtVnd($item->unit_price_vnd) }}
            </td>
            <td class="text-end">
              {{ $fmtVnd($item->total_price_vnd) }}
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-muted mini">
              Không có sản phẩm nào trong đơn hàng này.
            </td>
          </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" class="text-end">Tạm tính</td>
            <td class="text-end">
              {{ $fmtVnd($order->subtotal_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="4" class="text-end">Phí vận chuyển</td>
            <td class="text-end">
              {{ $fmtVnd($order->shipping_fee_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="4" class="text-end">Giảm giá</td>
            <td class="text-end">
              {{ $fmtVnd($order->discount_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="4" class="text-end">Tổng cộng</td>
            <td class="text-end h5 mb-0 text-success">
              {{ $fmtVnd($order->grand_total_vnd) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<div
  class="modal fade"
  id="deliveryFailedModal"
  tabindex="-1"
  aria-labelledby="deliveryFailedModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form
        method="POST"
        action="{{ route('shipper.orders.changeStatus', $order->id) }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="DELIVERY_FAILED">

        <div class="modal-header">
          <h5 class="modal-title" id="deliveryFailedModalLabel">
            Xác nhận giao hàng thất bại
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="deliveryFailedReason" class="form-label">
              Lý do giao hàng thất bại
            </label>
            <textarea
              id="deliveryFailedReason"
              name="reason"
              class="form-control"
              rows="3"
              placeholder="Nhập lý do: khách không nghe máy, khách từ chối nhận, sai địa chỉ,..."
              required></textarea>
          </div>

          <div class="alert alert-warning small mb-0">
            Vui lòng ghi rõ lý do để bộ phận xử lý đơn và kế toán có căn cứ hoàn tiền hoặc trừ phí ship phù hợp.
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Hủy
          </button>
          <button type="submit" class="btn btn-danger">
            Xác nhận thất bại
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
