@extends('layouts.admin')

@section('title','Orders: Danh sách đơn hàng')

@section('body_class','order-index-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Đơn hàng</li>
  </ol>
</nav>

<div class="table-in-clip">
  <div class="card shadow-sm table-in">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Danh sách đơn hàng</h5>

      <form method="GET" class="d-flex align-items-center">
        <label class="me-2 mb-0">Hiển thị</label>
        @php($pp = (int)request('per_page_order', 10))
        <select class="form-select form-select-sm w-auto setupSelect2" name="per_page_order" onchange="this.form.submit()">
          <option value="10" {{ $pp===10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ $pp===20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ $pp===50 ? 'selected' : '' }}>50</option>
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
              <input id="keyword" type="text" name="keyword" class="form-control" placeholder="Tìm theo mã / SDT / email / tên" value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2">
              <label class="d-block mb-1">&nbsp;</label>
              <button type="submit" class="btn btn-primary btn-admin btn-submit-filter-admin-order">
                <i class="fa fa-search me-1"></i> Tìm kiếm
              </button>
            </div>
          </div>

          <div class="row searchProduct">
            <div class="col-md-2">
              <label for="payment_method" class="form-label mb-1 label-filter-admin-product">Phương thức</label>
              <select id="payment_method" name="payment_method" class="form-select setupSelect2">
                <option value="">-- Tất cả phương thức --</option>
                <option value="COD" {{ request('payment_method')==='COD'?'selected':'' }}>COD</option>
                <option value="MOMO" {{ request('payment_method')==='MOMO'?'selected':'' }}>MOMO</option>
                <option value="VNPAY" {{ request('payment_method')==='VNPAY'?'selected':'' }}>VNPAY</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="payment_status" class="form-label mb-1 label-filter-admin-product">TT thanh toán</label>
              <select id="payment_status" name="payment_status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="UNPAID" {{ request('payment_status')==='UNPAID'?'selected':'' }}>Chưa thanh toán</option>
                <option value="PAID" {{ request('payment_status')==='PAID'?'selected':'' }}>Đã thanh toán</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="status" class="form-label mb-1 label-filter-admin-product">Trạng thái đơn</label>
              <select id="status" name="status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="PENDING" {{ request('status')==='PENDING'?'selected':'' }}>Đơn mới</option>
                <option value="CONFIRMED" {{ request('status')==='CONFIRMED'?'selected':'' }}>Đã xác nhận</option>
                <option value="PICKING" {{ request('status')==='PICKING'?'selected':'' }}>Đang lấy hàng</option>
                <option value="SHIPPED" {{ request('status')==='SHIPPED'?'selected':'' }}>Đã giao cho ĐVVC</option>
                <option value="DELIVERED" {{ request('status')==='DELIVERED'?'selected':'' }}>Đã giao</option>
                <option value="CANCELLED" {{ request('status')==='CANCELLED'?'selected':'' }}>Đã hủy</option>
                <option value="RETURNED" {{ request('status')==='RETURNED'?'selected':'' }}>Hoàn/trả</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="created_from" class="form-label mb-1 label-filter-admin-product">Từ ngày</label>
              <input id="created_from" type="date" name="created_from" class="form-control" value="{{ request('created_from') }}">
            </div>
            <div class="col-md-2">
              <label for="created_to" class="form-label mb-1 label-filter-admin-product">Đến ngày</label>
              <input id="created_to" type="date" name="created_to" class="form-control" value="{{ request('created_to') }}">
            </div>
          </div>
        </div>
      </form>

      <div class="table-responsive">
        <table id="orderTable" class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th class="th-order-table checkAllWidth"><input type="checkbox" id="order_check_all"></th>
              <th class="th-order-table STT_Width">#</th>
              <th class="th-order-table th-order-code">MÃ ĐƠN HÀNG</th>
              <th class="th-order-table">TÊN KHÁCH HÀNG \ SĐT</th>
              <th class="th-order-table th-order-method">PHƯƠNG THỨC</th>
              <th class="th-order-table th-date-order">NGÀY TẠO</th>
              <th class="th-order-table statusWidth">TRẠNG THÁI THANH TOÁN</th>
              <th class="th-order-table statusWidth">TRẠNG THÁI ĐƠN HÀNG</th>
              <th class="th-order-table actionWidth text-center">THAO TÁC</th>
            </tr>
          </thead>

          <tbody>
            @forelse($orders as $idx => $order)
            <tr>
              <td><input type="checkbox" value="{{ $order->id }}"></td>
              <td>{{ ($orders->firstItem() ?? 0) + $idx }}</td>
              <td>
                <a href="{{ route('admin.order.detail', $order->id) }}">
                  {{ $order->code ?? ('ORD-'.$order->id) }}
                </a>
              </td>
              <td>
                <div>{{ $order->shipment->name ?? '—' }} \ {{ $order->shipment->phone ?? $order->shipment->email ?? '—' }}</div>
              </td>
              <td>{{ strtoupper($order->payment_method ?? '—') }}</td>
              <td>{{ $order->placed_at?->format('d/m/Y h:i A') }}</td>

              {{-- TRẠNG THÁI THANH TOÁN --}}
              <td>
                @php($payStatus = strtoupper($order->payment_status ?? ''))

                @if($payStatus === 'PAID')
                <span class="badge rounded-pill badge-status badge-status--success">
                  Đã thanh toán
                </span>
                @elseif($payStatus === 'UNPAID')
                <span class="badge bg-secondary"">
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

                @if($status === 'pending')
                <span class="badge rounded-pill badge-status badge-status--warning">
                  Chờ xử lý
                </span>
                @elseif($status === 'confirmed')
                <span class="badge rounded-pill badge-status badge-status--warning">
                  Đã xác nhận
                </span>
                @elseif($status === 'processing')
                <span class="badge rounded-pill badge-status badge-status--warning">
                  Đang xử lý
                </span>
                @elseif($status === 'shipping')
                <span class="badge rounded-pill badge-status badge-status--warning">
                  Đang giao
                </span>
                @elseif($status === 'delivered')
                <span class="badge rounded-pill badge-status badge-status--success">
                  Đã giao hàng
                </span>
                @elseif($status === 'completed')
                <span class="badge rounded-pill badge-status badge-status--success">
                  Hoàn tất
                </span>
                @elseif($status === 'cancelled')
                <span class="badge rounded-pill badge-status badge-status--danger">
                  Đã hủy
                </span>
                @else
                <span class="badge rounded-pill badge-status badge-status--primary">
                  Không xác định
                </span>
                @endif
              </td>

              <td class="text-center">
                <a href="{{ route('admin.order.detail', $order->id) }}">
                  <i class="fa fa-eye icon-eye-view-order-detail"></i>
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center text-muted">Không có đơn hàng.</td>
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

@include('partials.ui.confirm-modal')
@endsection