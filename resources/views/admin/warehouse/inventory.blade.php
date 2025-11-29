@extends('layouts.warehouse')

@section('title','Quản lý kho')

@section('content')
<div id="warehouse-inventory" class="warehouse-section">
  <div class="mb-5">
    <h1 class="display-6 fw-bold text-dark mb-2">Báo Cáo Tồn Kho</h1>
    <p class="text-muted">Theo dõi số lượng tồn kho theo thời gian thực</p>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-lg-6">
      <div class="warehouse-card card">
        <div class="card-body">
          <h5 class="card-title d-flex align-items-center mb-4">
            <span class="me-2">⚠️</span>
            Sản Phẩm Sắp Hết Hàng
          </h5>
          <div class="d-flex flex-column gap-3">
            <div class="alert alert-warning alert-custom d-flex justify-content-between align-items-center mb-0">
              <span class="fw-medium">Samsung Galaxy S24</span>
              <span class="badge bg-warning">25 còn lại</span>
            </div>
            <div class="alert alert-warning alert-custom d-flex justify-content-between align-items-center mb-0">
              <span class="fw-medium">MacBook Air M2</span>
              <span class="badge bg-warning">12 còn lại</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="warehouse-card card">
        <div class="card-body">
          <h5 class="card-title d-flex align-items-center mb-4">
            <span class="me-2">🚫</span>
            Sản Phẩm Hết Hàng
          </h5>
          <div class="d-flex flex-column gap-3">
            <div class="alert alert-danger alert-custom d-flex justify-content-between align-items-center mb-0">
              <span class="fw-medium">iPad Pro 12.9</span>
              <span class="badge bg-danger">0 còn lại</span>
            </div>
            <div class="alert alert-danger alert-custom d-flex justify-content-between align-items-center mb-0">
              <span class="fw-medium">AirPods Pro</span>
              <span class="badge bg-danger">0 còn lại</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="warehouse-card card">
    <div class="card-body">
      <h5 class="card-title mb-4">Biểu Đồ Tồn Kho Theo Danh Mục</h5>
      <div class="bg-light rounded p-5 text-center" style="height: 300px;">
        <div class="d-flex align-items-center justify-content-center h-100">
          <p class="text-muted fs-5">📊 Biểu đồ tồn kho sẽ hiển thị tại đây</p>
        </div>
      </div>
    </div>
  </div>
</div>
@push('scripts')
@vite(['resources/js/pages/warehouse.js'])
@endpush
@endsection