@extends('layouts.warehouse')

@section('title','Đơn hàng kho: Danh sách đơn hàng')

@section('content')
<div id="warehouse-orders" class="warehouse-section">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h1 class="display-6 fw-bold text-dark mb-2">Quản lý đơn hàng</h1>
    </div>
  </div>
</div>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item">
      <a href="{{ route('warehouse.dashboard') }}">Kho hàng</a>
    </li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">
      Đơn hàng
    </li>
  </ol>
</nav>

<div class="table-in-clip">
  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Danh sách đơn hàng đang cần xử lý</h5>

      <form method="GET" class="d-flex align-items-center">
        <label class="me-2 mb-0">Hiển thị</label>
        @php($pp = (int)request('per_page_order', 10))
        <select
          class="form-select form-select-sm w-auto setupSelect2"
          name="per_page_order"
          onchange="this.form.submit()">
          <option value="10" {{ $pp === 10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ $pp === 20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ $pp === 50 ? 'selected' : '' }}>50</option>
        </select>

        <input type="hidden" name="keyword" value="{{ request('keyword') }}">
        <input type="hidden" name="payment_method" value="{{ request('payment_method') }}">
        <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="created_from" value="{{ request('created_from') }}">
        <input type="hidden" name="created_to" value="{{ request('created_to') }}">
      </form>
    </div>

    <div class="card-body">
      {{-- Filters --}}
      <form method="GET" class="row g-2 mb-3 filter-form">
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-6 searchProduct">
              <label for="keyword" class="form-label mb-1 label-filter-admin-product">Tìm kiếm</label>
              <input
                id="keyword"
                type="text"
                name="keyword"
                class="form-control"
                placeholder="Tìm theo mã / SDT / email / tên"
                value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2">
              <label class="d-block mb-1">&nbsp;</label>
              <button type="submit" class="btn btn-primary btn-admin btn-submit-filter-admin-order">
                <i class="fa fa-search me-1"></i> Tìm kiếm
              </button>
            </div>
          </div>

          <div class="row searchProduct">
            {{-- Trạng thái: chỉ cho lọc các trạng thái khác PENDING --}}
            <div class="col-md-2">
              <label for="status" class="form-label mb-1 label-filter-admin-product">Trạng thái đơn</label>
              <select id="status" name="status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>

                <option value="PROCESSING" @selected(request('status')==='PROCESSING')>
                  Đang chờ tiếp nhận
                </option>
                <option value="PICKING" @selected(request('status')==='PICKING')>
                  Đang chuẩn bị hàng
                </option>
                <option value="SHIPPING" @selected(request('status')==='SHIPPING')>
                  Đã giao cho ĐVVC
                </option>
                <option value="COMPLETED" @selected(request('status')==='COMPLETED')>
                  Hoàn tất
                </option>
                <option value="CANCELLED" @selected(request('status')==='CANCELLED')>
                  Đã huỷ
                </option>
                <option value="DELIVERY_FAILED" @selected(request('status')==='DELIVERY_FAILED')>
                  Giao thất bại
                </option>
                <option value="RETURNED" @selected(request('status')==='RETURNED')>
                  Hoàn / trả
                </option>
              </select>
            </div>

            <div class="col-md-2">
              <label for="created_from" class="form-label mb-1 label-filter-admin-product">Từ ngày</label>
              <input
                id="created_from"
                type="date"
                name="created_from"
                class="form-control"
                value="{{ request('created_from') }}">
            </div>
            <div class="col-md-2">
              <label for="created_to" class="form-label mb-1 label-filter-admin-product">Đến ngày</label>
              <input
                id="created_to"
                type="date"
                name="created_to"
                class="form-control"
                value="{{ request('created_to') }}">
            </div>
          </div>
        </div>
      </form>

      {{-- Thanh công cụ phía trên bảng --}}
      <div class="d-flex justify-content-end mb-2">
        <button
          type="button"
          class="btn btn-sm btn-success d-flex align-items-center"
          id="btnPrintSelectedOrders"
          disabled>
          <i class="bi bi-printer-fill me-1"></i>
          <span>In đơn đã chọn</span>
        </button>
      </div>

      <div class="table-responsive">
        <table id="warehouseOrderTable" class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th class="th-order-table checkAllWidth">
                <input type="checkbox" id="order_check_all">
              </th>
              <th class="th-order-table STT_Width">#</th>
              <th class="th-order-table th-order-code">MÃ ĐƠN HÀNG</th>
              <th class="th-order-table">TÊN KHÁCH HÀNG \ SĐT</th>
              <th class="th-order-table th-date-order">NGÀY TẠO</th>
              <th class="th-order-table statusWidth">TRẠNG THÁI THANH TOÁN</th>
              <th class="th-order-table statusWidth">TRẠNG THÁI ĐƠN HÀNG</th>
              <th class="th-order-table actionWidth text-center">THAO TÁC</th>
            </tr>
          </thead>

          <tbody>
            @forelse($orders as $idx => $order)
              <tr>
                <td>
                  <input
                    type="checkbox"
                    class="warehouse-order-checkbox"
                    value="{{ $order->id }}">
                </td>
                <td>{{ ($orders->firstItem() ?? 0) + $idx }}</td>
                <td>
                  <a href="{{ route('warehouse.order.detail', $order->id) }}">
                    {{ $order->code ?? ('ORD-' . $order->id) }}
                  </a>
                </td>
                <td>
                  <div>
                    {{ $order->shipment->name ?? '—' }}
                    \
                    {{ $order->shipment->phone ?? $order->shipment->email ?? '—' }}
                  </div>
                </td>

                <td>{{ $order->placed_at?->format('d/m/Y h:i A') }}</td>

                {{-- TRẠNG THÁI THANH TOÁN --}}
                <td>
                  @php($payStatus = strtoupper($order->payment_status ?? ''))

                  @if($payStatus === 'PAID')
                    <span class="badge rounded-pill badge-status badge-status--success">
                      Đã thanh toán
                    </span>
                  @elseif($payStatus === 'UNPAID')
                    <span class="badge bg-secondary">
                      Chưa thanh toán
                    </span>
                  @else
                    <span class="badge rounded-pill badge-status badge-status--primary">
                      Không xác định
                    </span>
                  @endif
                </td>

                {{-- TRẠNG THÁI ĐƠN HÀNG --}}
                <td>
                  @php($status = strtolower($order->status ?? ''))

                  @if ($status === 'processing')
                    <span class="badge rounded-pill badge-status badge-status--warning">
                      Đang chờ tiếp nhận
                    </span>
                  @elseif ($status === 'picking')
                    <span class="badge rounded-pill badge-status badge-status--warning">
                      Đang chuẩn bị hàng
                    </span>
                  @elseif (in_array($status, ['shipping', 'shipped'], true))
                    <span class="badge rounded-pill badge-status badge-status--warning">
                      Đã giao cho ĐVVC
                    </span>
                  @elseif (in_array($status, ['completed', 'delivered'], true))
                    <span class="badge rounded-pill badge-status badge-status--success">
                      Hoàn tất
                    </span>
                  @elseif (in_array($status, ['cancelled', 'delivery_failed'], true))
                    <span class="badge rounded-pill badge-status badge-status--danger">
                      Đã huỷ / giao thất bại
                    </span>
                  @elseif ($status === 'returned')
                    <span class="badge rounded-pill badge-status badge-status--warning">
                      Hoàn / trả hàng
                    </span>
                  @else
                    <span class="badge rounded-pill badge-status badge-status--primary">
                      {{ strtoupper($order->status ?? 'Không xác định') }}
                    </span>
                  @endif
                </td>

                <td class="text-center">
                  <div class="warehouse-order-actions">
                    <a
                      href="{{ route('warehouse.order.detail', $order->id) }}"
                      class="warehouse-action-btn warehouse-action-btn-view"
                      title="Xem chi tiết"
                      aria-label="Xem chi tiết">
                      <i class="fa fa-eye"></i>
                    </a>

                    <a
                      href="{{ route('warehouse.orders.print', ['id' => $order->id]) }}"
                      target="_blank"
                      class="warehouse-action-btn warehouse-action-btn-print"
                      title="In đơn A4"
                      aria-label="In đơn A4">
                      <i class="bi bi-printer"></i>
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted">
                  Không có đơn hàng phù hợp.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $orders->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    var checkAll = document.getElementById('order_check_all');
    var btnPrint = document.getElementById('btnPrintSelectedOrders');

    if (!btnPrint) {
      return;
    }

    function getRowCheckboxes() {
      return Array.prototype.slice.call(
        document.querySelectorAll('.warehouse-order-checkbox')
      );
    }

    function setRowHighlight(cb) {
      var row = cb.closest('tr');
      if (!row) {
        return;
      }
      row.classList.toggle('warehouse-order-row-selected', cb.checked);
    }

    function updateButtonState() {
      var checkboxes = getRowCheckboxes();
      var count = 0;

      checkboxes.forEach(function (cb) {
        if (cb.checked) {
          count++;
        }
      });

      btnPrint.disabled = count === 0;

      var label = count > 0
        ? 'In ' + count + ' đơn đã chọn'
        : 'In đơn đã chọn';

      var span = btnPrint.querySelector('span');
      if (span) {
        span.textContent = label;
      }
    }

    if (checkAll) {
      checkAll.addEventListener('change', function () {
        var checkboxes = getRowCheckboxes();

        checkboxes.forEach(function (cb) {
          cb.checked = checkAll.checked;
          setRowHighlight(cb);
        });

        updateButtonState();
      });
    }

    document.addEventListener('change', function (event) {
      var target = event.target;
      if (!target.classList || !target.classList.contains('warehouse-order-checkbox')) {
        return;
      }

      if (!target.checked && checkAll && checkAll.checked) {
        checkAll.checked = false;
      }

      setRowHighlight(target);
      updateButtonState();
    });

    btnPrint.addEventListener('click', function () {
      var checkboxes = getRowCheckboxes();
      var ids = [];

      checkboxes.forEach(function (cb) {
        if (cb.checked) {
          ids.push(cb.value);
        }
      });

      if (ids.length === 0) {
        return;
      }

      var url = new URL('{{ route('warehouse.orders.printMultiple') }}', window.location.origin);
      ids.forEach(function (id) {
        url.searchParams.append('ids[]', id);
      });

      window.open(url.toString(), '_blank');
    });

    getRowCheckboxes().forEach(function (cb) {
      setRowHighlight(cb);
    });

    updateButtonState();
  })();
</script>
@endpush
