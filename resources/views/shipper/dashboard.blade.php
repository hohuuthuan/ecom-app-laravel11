@extends('layouts.shipper')

@section('title','Bảng điều khiển')

@section('content')
@php
$fmtVnd = fn($n) => number_format((int) $n, 0, ',', '.') . ' VNĐ';
$status = strtoupper((string) ($filters['status'] ?? 'SHIPPING'));

$statusLabelMap = [
'SHIPPING' => 'Đang giao',
'COMPLETED' => 'Giao thành công',
'DELIVERY_FAILED' => 'Giao thất bại',
'RETURNED' => 'Hoàn / trả hàng',
];

$statusBadgeMap = [
'SHIPPING' => 'badge-status--primary',
'COMPLETED' => 'badge-status--success',
'DELIVERY_FAILED' => 'badge-status--danger',
'RETURNED' => 'badge-status--danger',
];
@endphp

<div class="container-fluid shipper-dashboard">
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="shipper-card stats-card blue warehouse-section fade-in">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div class="fw-semibold">Đơn đang giao</div>
          <span class="badge bg-primary-subtle text-primary"></span>
        </div>
        <div class="h3 mb-0">
          {{ $stats['shipping'] ?? 0 }}
        </div>
        <div class="mini text-muted">
          Đơn nội bộ bạn đang thực hiện giao.
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="shipper-card stats-card green warehouse-section fade-in">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div class="fw-semibold">Giao thành công</div>
          <span class="badge bg-success-subtle text-success"></span>
        </div>
        <div class="h3 mb-0">
          {{ $stats['completed'] ?? 0 }}
        </div>
        <div class="mini text-muted">
          Tổng đơn đã giao thành công.
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="shipper-card stats-card red warehouse-section fade-in">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div class="fw-semibold">Giao thất bại / hoàn</div>
          <span class="badge bg-danger-subtle text-danger">FAILED</span>
        </div>
        <div class="h3 mb-0">
          {{ $stats['failed'] ?? 0 }}
        </div>
        <div class="mini text-muted">
          Bao gồm đơn giao thất bại và hoàn / trả.
        </div>
      </div>
    </div>
  </div>

  <div class="shipper-card warehouse-section slide-down">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <div>
        <h5 class="mb-1">Danh sách đơn giao hàng</h5>
        <div class="mini text-muted">
          Trạng thái hiện tại:
          <b>{{ $statusLabelMap[$status] ?? 'Đang giao' }}</b>
        </div>
      </div>

      <form
        method="GET"
        class="d-flex flex-wrap gap-2 align-items-center shipper-filter-form">
        <input type="hidden" name="status" value="{{ $status }}">
        <input
          type="text"
          name="keyword"
          value="{{ $filters['keyword'] ?? '' }}"
          class="form-control form-control-sm"
          placeholder="Mã đơn / người nhận / SĐT...">

        <select
          name="per_page"
          class="setupSelect2 form-select form-select-sm">
          @foreach([10,15,20,30] as $size)
          <option
            value="{{ $size }}"
            {{ (int)($filters['per_page'] ?? 15) === $size ? 'selected' : '' }}>
            {{ $size }} / trang
          </option>
          @endforeach
        </select>
      </form>

    </div>

    <div class="mb-3 d-flex flex-wrap gap-2">
      <a
        href="{{ route('shipper.dashboard', ['status' => 'SHIPPING']) }}"
        class="btn btn-outline-primary btn-sm {{ $status === 'SHIPPING' ? 'active' : '' }}">
        Đang giao
      </a>
      <a
        href="{{ route('shipper.dashboard', ['status' => 'COMPLETED']) }}"
        class="btn btn-outline-success btn-sm {{ $status === 'COMPLETED' ? 'active' : '' }}">
        Giao thành công
      </a>
      <a
        href="{{ route('shipper.dashboard', ['status' => 'DELIVERY_FAILED']) }}"
        class="btn btn-outline-danger btn-sm {{ $status === 'DELIVERY_FAILED' ? 'active' : '' }}">
        Giao thất bại / hoàn
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-sm align-middle warehouse-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Mã đơn</th>
            <th>Khách hàng \ SĐT</th>
            <th>Địa chỉ</th>
            <th class="text-center">Trạng thái</th>
            <th class="text-end">Tổng tiền</th>
            <th class="text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $order)
          @php
          $s = strtoupper((string) $order->status);
          $label = $statusLabelMap[$s] ?? 'Không xác định';
          $badgeClass = $statusBadgeMap[$s] ?? 'badge-status--secondary';

          $customerName = $order->user?->name ?? 'Khách hàng';
          $customerPhone = $order->user?->phone ?? $order->shipment?->phone;
          $customerAddress = $order->shipment?->address;
          @endphp
          <tr>
            <td>{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
            <td>
              <div class="fw-semibold">#{{ $order->code }}</div>
              <div class="mini text-muted">
                {{ optional($order->placed_at ?? $order->created_at)->format('d/m/Y H:i') }}
              </div>
            </td>
            <td>
              <div class="fw-semibold">
                {{ $customerName }} \ {{ $customerPhone }}
              </div>
            </td>
            <td>
              <div class="mini text-muted">
                {{ $customerAddress }}
              </div>
            </td>
            <td class="text-center">
              <span class="badge rounded-pill {{ $badgeClass }}">
                {{ $label }}
              </span>
            </td>
            <td class="text-end">
              {{ $fmtVnd($order->grand_total_vnd) }}
            </td>
            <td class="text-end">
              <a
                href="{{ route('shipper.orders.detail', $order->id) }}"
                class="btn">
                <i class="fa fa-eye icon-eye-view-order-detail"></i>
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center text-muted mini">
              Không có đơn hàng nào phù hợp.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-2">
      {{ $orders->links() }}
    </div>
  </div>
</div>
<script>
  (function() {
    const form = document.querySelector('.shipper-filter-form');
    if (!form) return;

    const perPageSelect = form.querySelector('select[name="per_page"]');
    if (!perPageSelect) return;

    perPageSelect.addEventListener('change', function() {
      form.submit();
    });
  })();
</script>
@endsection