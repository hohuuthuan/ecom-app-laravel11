@extends('layouts.warehouse')

@section('title','Quản lý kho')

@section('content')
<div id="warehouse-dashboard" class="warehouse-section">
  <div class="mb-5 text-end">
    <h1 class="display-6 fw-bold text-dark mb-2">Tổng Quan Kho</h1>
    <p class="text-muted">Thống kê và báo cáo tổng quan</p>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
      <div class="warehouse-card stats-card blue">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="text-muted small mb-1">Tổng Sản Phẩm</p>
              <h3 class="fw-bold mb-0">45</h3>
            </div>
            <div class="fs-1">📦</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="warehouse-card stats-card green">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="text-muted small mb-1">Đơn đang chờ</p>
              <h3 class="fw-bold mb-0">12 đơn</h3>
            </div>
            <div class="fs-1">📋</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="warehouse-card stats-card yellow">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="text-muted small mb-1">Sắp Hết Hàng</p>
              <h3 class="fw-bold mb-0">23</h3>
            </div>
            <div class="fs-1">⚠️</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="warehouse-card stats-card red">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="text-muted small mb-1">Hết Hàng</p>
              <h3 class="fw-bold mb-0">5</h3>
            </div>
            <div class="fs-1">🚫</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Hoạt động gần đây --}}
  <div class="warehouse-card card">
    <div class="card-body">
      <h4 class="card-title fw-semibold mb-4">Hoạt Động Gần Đây</h4>
      <div class="d-flex flex-column gap-3">
        <div class="activity-item p-3 d-flex align-items-center">
          <div class="fs-4 me-3">⬇️</div>
          <div class="flex-grow-1">
            <p class="mb-1 fw-medium">Nhập kho 100 sản phẩm iPhone 15</p>
            <small class="text-muted">2 giờ trước</small>
          </div>
        </div>
        <div class="activity-item p-3 d-flex align-items-center">
          <div class="fs-4 me-3">⬆️</div>
          <div class="flex-grow-1">
            <p class="mb-1 fw-medium">Xuất kho 50 sản phẩm Samsung Galaxy</p>
            <small class="text-muted">4 giờ trước</small>
          </div>
        </div>
        <div class="activity-item p-3 d-flex align-items-center">
          <div class="fs-4 me-3">📝</div>
          <div class="flex-grow-1">
            <p class="mb-1 fw-medium">Cập nhật thông tin sản phẩm Laptop Dell</p>
            <small class="text-muted">6 giờ trước</small>
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