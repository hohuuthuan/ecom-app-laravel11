@extends('layouts.warehouse')

@section('title','Quản lý kho')

@section('content')
<div id="warehouse-inventory" class="warehouse-section">
  <div class="mb-3 ">
    <h1 class="display-6 fw-bold text-dark mb-2">Báo Cáo Tồn Kho</h1>
    <p class="text-muted">Theo dõi số lượng tồn kho theo thời gian thực</p>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-lg-6">
      <div class="warehouse-card card">
        <div class="card-body">
          <h5 class="card-title d-flex align-items-center mb-3">
            <span class="me-2">⚠️</span>
            Sản Phẩm Sắp Hết Hàng
          </h5>

          @if($lowStocks->isEmpty())
          <p class="text-muted mb-0">Hiện chưa có sản phẩm nào sắp hết hàng.</p>
          @else
          <div class="inventory-alert-list">
            @foreach($lowStocks as $stock)
            <div class="alert alert-warning alert-custom inventory-alert-item mb-0">
              <div class="inventory-alert-main">
                <div class="inventory-alert-title">
                  {{ $stock->product->title ?? 'Sản phẩm không xác định' }}
                </div>
                <div class="inventory-alert-meta">
                  Mã: {{ $stock->product->code ?? 'N/A' }}
                  @if($stock->warehouse)
                  · Kho: {{ $stock->warehouse->name }}
                  @endif
                </div>
              </div>
              <div class="inventory-alert-badge">
                <span class="badge bg-warning text-dark">
                  {{ $stock->on_hand }} còn lại
                </span>
              </div>
            </div>
            @endforeach
          </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="warehouse-card card">
        <div class="card-body">
          <h5 class="card-title d-flex align-items-center mb-3">
            <span class="me-2">🚫</span>
            Sản Phẩm Hết Hàng
          </h5>

          @if($outOfStocks->isEmpty())
          <p class="text-muted mb-0">Hiện chưa có sản phẩm nào hết hàng.</p>
          @else
          <div class="inventory-alert-list">
            @foreach($outOfStocks as $product)
            @php
            $totalOnHand = (int) ($product->stocks->sum('on_hand') ?? 0);
            $firstWarehouse = $product->stocks->first()?->warehouse;
            @endphp
            <div class="alert alert-danger alert-custom inventory-alert-item mb-0">
              <div class="inventory-alert-main">
                <div class="inventory-alert-title">
                  {{ $product->title }}
                </div>
                <div class="inventory-alert-meta">
                  Mã: {{ $product->code ?? 'N/A' }}
                  @if($firstWarehouse)
                  · Kho gần nhất: {{ $firstWarehouse->name }}
                  @endif
                </div>
              </div>
              <div class="inventory-alert-badge">
                <span class="badge bg-danger">
                  {{ $totalOnHand }} còn lại
                </span>
              </div>
            </div>
            @endforeach
          </div>
          @endif
        </div>
      </div>
    </div>

  </div>

  {{-- Bảng chi tiết tồn kho từng sản phẩm (ACTIVE) --}}
  <div class="warehouse-card card inventory-table">
    <div class="card-body">
      <h5 class="card-title mb-4">Chi Tiết Tồn Kho Sản Phẩm</h5>
      <form method="GET"
        action="{{ route('warehouse.inventory') }}"
        class="row g-3 mb-3">

        <div class="col-md-4">
          <label class="form-label fw-medium">Tìm kiếm</label>
          <input type="text"
            name="keyword"
            class="form-control warehouse-form-control"
            placeholder="Tìm theo mã hoặc tên sản phẩm"
            value="{{ $filters['keyword'] ?? '' }}">
        </div>

        <div class="col-md-2">
          <label class="form-label fw-medium">Trạng thái</label>
          <div class="select2-stable-wrapper">
            <select
              name="status"
              class="form-select warehouse-form-control setupSelect2"
              data-width="100%">
              <option value="">-- Tất cả --</option>
              <option value="normal" {{ ($filters['status'] ?? '') === 'normal' ? 'selected' : '' }}>
                Ổn định
              </option>
              <option value="low" {{ ($filters['status'] ?? '') === 'low' ? 'selected' : '' }}>
                Sắp hết
              </option>
              <option value="out" {{ ($filters['status'] ?? '') === 'out' ? 'selected' : '' }}>
                Hết hàng
              </option>
            </select>
          </div>
        </div>

        <div class="col-md-2">
          <label class="form-label fw-medium">Hiển thị</label>
          <div class="select2-stable-wrapper">
            <select
              name="per_page"
              class="form-select warehouse-form-control setupSelect2"
              data-width="100%">
              <option value="20" {{ (int) ($filters['per_page'] ?? 20) === 20 ? 'selected' : '' }}>20 SP / trang</option>
              <option value="50" {{ (int) ($filters['per_page'] ?? 20) === 50 ? 'selected' : '' }}>50 SP / trang</option>
              <option value="100" {{ (int) ($filters['per_page'] ?? 20) === 100 ? 'selected' : '' }}>100 SP / trang</option>
            </select>
          </div>
        </div>


        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-dark px-4 me-2">
            Lọc
          </button>
          <a href="{{ route('warehouse.inventory') }}"
            class="btn btn-outline-secondary">
            Xóa lọc
          </a>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover mb-0 warehouse-table">
          <thead>
            <tr>
              <th class="px-3 py-2 text-start">Mã SP</th>
              <th class="px-3 py-2 text-start">Tên sản phẩm</th>
              <th class="px-3 py-2 text-end">Tồn thực tế</th>
              <th class="px-3 py-2 text-center">Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            @forelse($inventoryProducts as $product)
            @php
            $onHand = (int) ($product->total_on_hand ?? 0);

            $statusLabel = 'Ổn định';
            $statusClass = 'bg-success';

            if ($onHand <= 0) {
              $statusLabel='Hết hàng' ;
              $statusClass='bg-danger' ;
              } else {
              $threshold=50;
              if ($onHand <=$threshold) {
              $statusLabel='Sắp hết' ;
              $statusClass='bg-warning text-dark' ;
              }
              }
              @endphp
              <tr>
              <td class="px-3 py-2 text-start">
                {{ $product->code ?? 'N/A' }}
              </td>
              <td class="px-3 py-2 text-start">
                {{ $product->title ?? 'Sản phẩm không xác định' }}
              </td>
              <td class="px-3 py-2 text-end">
                {{ $onHand }} SP
              </td>
              <td class="px-3 py-2 text-center">
                <span class="badge {{ $statusClass }}">
                  {{ $statusLabel }}
                </span>
              </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center py-3 text-muted">
                  Chưa có sản phẩm nào đang bán
                </td>
              </tr>
              @endforelse
          </tbody>

        </table>
      </div>


      <div class="mt-3" id="pagination-links">
        {{ $inventoryProducts->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>

</div>

@push('scripts')
@vite(['resources/js/pages/warehouse.js'])
@endpush
@endsection