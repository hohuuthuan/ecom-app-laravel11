@extends('layouts.warehouse')

@section('title','Quản lý kho')

@section('content')
<div id="warehouse-orders" class="warehouse-section">
  <div class="d-flex justify-content-between align-items-center mb-5">
    <div>
      <h1 class="display-6 fw-bold text-dark mb-2">Quản Lý Sản Phẩm</h1>
      <p class="text-muted">Thêm, sửa, xóa thông tin sản phẩm</p>
    </div>
  </div>

  <div class="warehouse-card card mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-8">
          <label for="warehouse-search-product" class="form-label fw-medium">Tìm kiếm sản phẩm</label>
          <input
            id="warehouse-search-product"
            type="text"
            class="form-control warehouse-form-control"
            placeholder="Nhập tên sản phẩm...">
        </div>
        <div class="col-md-4">
          <label for="warehouse-category-filter" class="form-label fw-medium">Danh mục</label>
          <select
            id="warehouse-category-filter"
            class="form-select warehouse-form-select">
            <option value="">Tất cả danh mục</option>
            <option value="electronics">Điện tử</option>
            <option value="clothing">Thời trang</option>
            <option value="books">Sách</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="warehouse-card card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 warehouse-table">
          <thead>
            <tr>
              <th class="px-4 py-3">Mã SP</th>
              <th class="px-4 py-3">Tên Sản Phẩm</th>
              <th class="px-4 py-3">Danh Mục</th>
              <th class="px-4 py-3">Giá</th>
              <th class="px-4 py-3">Tồn Kho</th>
              <th class="px-4 py-3">Thao Tác</th>
            </tr>
          </thead>
          <tbody id="warehouse-orders-table">
            <tr>
              <td class="px-4 py-3 fw-medium">SP001</td>
              <td class="px-4 py-3">iPhone 15 Pro Max</td>
              <td class="px-4 py-3 text-muted">Điện tử</td>
              <td class="px-4 py-3">29,990,000đ</td>
              <td class="px-4 py-3">
                <span class="badge bg-success badge-stock">150</span>
              </td>
              <td class="px-4 py-3">
                <button type="button" class="btn btn-sm btn-outline-primary me-1">✏️</button>
                <button type="button" class="btn btn-sm btn-outline-danger">🗑️</button>
              </td>
            </tr>
            <tr>
              <td class="px-4 py-3 fw-medium">SP002</td>
              <td class="px-4 py-3">Samsung Galaxy S24</td>
              <td class="px-4 py-3 text-muted">Điện tử</td>
              <td class="px-4 py-3">22,990,000đ</td>
              <td class="px-4 py-3">
                <span class="badge bg-warning badge-stock">25</span>
              </td>
              <td class="px-4 py-3">
                <button type="button" class="btn btn-sm btn-outline-primary me-1">✏️</button>
                <button type="button" class="btn btn-sm btn-outline-danger">🗑️</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@push('scripts')
@vite(['resources/js/pages/warehouse.js'])
@endpush
@endsection