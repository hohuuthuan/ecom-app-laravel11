@extends('layouts.warehouse')

@section('title','Quản lý kho')

@section('content')
<div id="warehouse-dashboard" class="warehouse-section">
  <div class="mb-5 d-flex justify-content-between align-items-end flex-wrap gap-3">
    <div>
      <h1 class="display-6 fw-bold text-dark mb-2">Tổng Quan Kho</h1>
      <p class="text-muted mb-0">Thống kê nhanh tình hình tồn kho và đơn hàng.</p>
    </div>
    <div class="text-end">
      <a href="{{ route('warehouse.inventory') }}" class="btn btn-sm btn-outline-secondary me-2">
        <i class="fa fa-clipboard-list me-1"></i> Tồn kho chi tiết
      </a>
      <a href="{{ route('warehouse.import') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-download me-1"></i> Nhập hàng mới
      </a>
    </div>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
      <div class="warehouse-card stats-card blue h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="text-muted small mb-1">Tổng Sản Phẩm</p>
              <h3 class="fw-bold mb-0">
                {{ number_format($stats['total_products'] ?? 0) }}
              </h3>
            </div>
            <div class="fs-1">📦</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <a href="{{ route('warehouse.orders', ['status' => 'PROCESSING']) }}" class="text-decoration-none text-reset">
        <div class="warehouse-card stats-card green h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted small mb-1">Đơn đang chờ kho xử lý</p>
                <h3 class="fw-bold mb-0">
                  {{ number_format($stats['pending_orders'] ?? 0) }} đơn
                </h3>
              </div>
              <div class="fs-1">📋</div>
            </div>
          </div>
        </div>
      </a>
    </div>

    <div class="col-xl-3 col-md-6">
      <a href="{{ route('warehouse.inventory', ['status' => 'low']) }}" class="text-decoration-none text-reset">
        <div class="warehouse-card stats-card yellow h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted small mb-1">Sắp Hết Hàng</p>
                <h3 class="fw-bold mb-0">
                  {{ number_format($stats['low_stock_items'] ?? 0) }}
                </h3>
              </div>
              <div class="fs-1">⚠️</div>
            </div>
          </div>
        </div>
      </a>
    </div>

    <div class="col-xl-3 col-md-6">
      <a href="{{ route('warehouse.inventory', ['status' => 'out']) }}" class="text-decoration-none text-reset">
        <div class="warehouse-card stats-card red h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted small mb-1">Hết Hàng</p>
                <h3 class="fw-bold mb-0">
                  {{ number_format($stats['out_of_stock_items'] ?? 0) }}
                </h3>
              </div>
              <div class="fs-1">🚫</div>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-7">
      <div class="warehouse-card card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title fw-semibold mb-0">Hoạt Động Nhập Kho Gần Đây</h4>
            <a href="{{ route('warehouse.purchase_receipts.index') }}" class="small text-decoration-none">
              Xem tất cả
            </a>
          </div>

          @if($recentReceipts->isEmpty())
          <p class="text-muted mb-0">Chưa có phiếu nhập nào gần đây.</p>
          @else
          <div class="d-flex flex-column gap-3">
            @foreach($recentReceipts as $receipt)
            <div class="activity-item p-3 d-flex align-items-center border rounded-3">
              <div class="fs-4 me-3">⬇️</div>
              <div class="flex-grow-1">
                <p class="mb-1 fw-medium">
                  Phiếu nhập
                  <span class="fw-semibold">{{ $receipt->receipt_code }}</span>
                  @if(!empty($receipt->delivery_unit))
                  - {{ $receipt->delivery_unit }}
                  @endif
                </p>
                @php
                $displayDate = $receipt->received_at ?? $receipt->created_at;
                @endphp
                <small class="text-muted">
                  {{ $displayDate ? \Illuminate\Support\Carbon::parse($displayDate)->diffForHumans() : '' }}
                </small>
              </div>
              <div class="text-end small text-muted ms-3">
                {{ $displayDate ? \Illuminate\Support\Carbon::parse($displayDate)->format('d/m/Y') : '' }}
              </div>
            </div>
            @endforeach
          </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="warehouse-card card h-100">
        <div class="card-body">
          <h4 class="card-title fw-semibold mb-3">Thao Tác Nhanh</h4>
          <div class="list-group list-group-flush">
            <a href="{{ route('warehouse.import') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-medium">Tạo phiếu nhập mới</div>
                <small class="text-muted">Tiếp nhận lô hàng mới từ nhà xuất bản / nhà cung cấp</small>
              </div>
              <i class="fa fa-arrow-right ms-2"></i>
            </a>
            <a href="{{ route('warehouse.inventory') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-medium">Xem tồn kho chi tiết</div>
                <small class="text-muted">Tra cứu nhanh số lượng tồn theo từng đầu sách</small>
              </div>
              <i class="fa fa-arrow-right ms-2"></i>
            </a>
            <a href="{{ route('warehouse.orders') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-medium">Đơn hàng chờ xử lý</div>
                <small class="text-muted">Danh sách đơn cần kiểm kho / đóng gói</small>
              </div>
              <i class="fa fa-arrow-right ms-2"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@push('scripts')
@vite(['resources/js/pages/warehouse.js'])
@endpush
@endsection