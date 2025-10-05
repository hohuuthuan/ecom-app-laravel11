@extends('layouts.admin')

@section('title','Products: Danh sách sản phẩm')

@section('body_class','create-product-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Sản phẩm</li>
  </ol>
</nav>

<div class="table-in-clip">
  <div class="card shadow-sm table-in">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Danh sách sản phẩm</h5>

      <form method="GET" class="d-flex align-items-center">
        <label class="me-2 mb-0">Hiển thị</label>
        @php($pp = (int)request('per_page_product', 10))
        <select class="form-select form-select-sm w-auto setupSelect2" name="per_page_product" onchange="this.form.submit()">
          <option value="10" {{ $pp===10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ $pp===20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ $pp===50 ? 'selected' : '' }}>50</option>
        </select>

        {{-- preserve current filters when changing page size --}}
        <input type="hidden" name="keyword" value="{{ request('keyword') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
        <input type="hidden" name="author_id" value="{{ request('author_id') }}">
        <input type="hidden" name="publisher_id" value="{{ request('publisher_id') }}">
      </form>
    </div>

    <div class="card-body">
      {{-- Filters --}}
      <form method="GET" class="row g-2 mb-3 filter-form">
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-6 searchProduct">
              <label for="keyword" class="form-label mb-1 label-filter-admin-product">Tìm kiếm</label>
              <input id="keyword" type="text" name="keyword" class="form-control" placeholder="Tìm tên / ISBN / slug" value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary btn-admin btn-submit-filter-admin-product"><i class="fa fa-search me-1"></i> Tìm kiếm</button>
            </div>
          </div>

          <div class="row searchProduct">
            <div class="col-md-2">
              <label for="category_id" class="form-label mb-1 label-filter-admin-product">Danh mục</label>
              <select id="category_id" name="category_id" class="form-select setupSelect2">
                <option value="">-- Tất cả danh mục --</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ request('category_id') === (string)$c->id ? 'selected' : '' }}>
                  {{ $c->name }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label for="author_id" class="form-label mb-1 label-filter-admin-product">Tác giả</label>
              <select id="author_id" name="author_id" class="form-select setupSelect2">
                <option value="">-- Tất cả tác giả --</option>
                @foreach($authors as $a)
                <option value="{{ $a->id }}" {{ request('author_id') === (string)$a->id ? 'selected' : '' }}>
                  {{ $a->name }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label for="publisher_id" class="form-label mb-1 label-filter-admin-product">Nhà xuất bản</label>
              <select id="publisher_id" name="publisher_id" class="form-select setupSelect2">
                <option value="">-- Tất cả NXB --</option>
                @foreach($publishers as $p)
                <option value="{{ $p->id }}" {{ request('publisher_id') === (string)$p->id ? 'selected' : '' }}>
                  {{ $p->name }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label for="status" class="form-label mb-1 label-filter-admin-product">Trạng thái</label>
              <select id="status" name="status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="ACTIVE" {{ request('status')==='ACTIVE'?'selected':'' }}>Đang bán</option>
                <option value="INACTIVE" {{ request('status')==='INACTIVE'?'selected':'' }}>Ẩn</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-2">
              <label for="price_min" class="form-label mb-1 label-filter-admin-product">Giá từ</label>
              <input id="price_min" type="number" name="price_min" class="form-control" placeholder="0" value="{{ request('price_min') }}" min="0" step="1">
            </div>
            <div class="col-md-2">
              <label for="price_max" class="form-label mb-1 label-filter-admin-product">Giá đến</label>
              <input id="price_max" type="number" name="price_max" class="form-control" placeholder="∞" value="{{ request('price_max') }}" min="0" step="1">
            </div>
            <div class="col-md-2">
              <label for="stock_min" class="form-label mb-1 label-filter-admin-product">Tồn từ</label>
              <input id="stock_min" type="number" name="stock_min" class="form-control" placeholder="0" value="{{ request('stock_min') }}" min="0" step="1">
            </div>
            <div class="col-md-2">
              <label for="stock_max" class="form-label mb-1 label-filter-admin-product">Tồn đến</label>
              <input id="stock_max" type="number" name="stock_max" class="form-control" placeholder="∞" value="{{ request('stock_max') }}" min="0" step="1">
            </div>
          </div>
        </div>

      </form>

      {{-- Bulk actions --}}
      <div class="d-flex justify-content-between mb-2">
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-danger btn-admin" id="productBtnBulkDelete" disabled>Xoá đã chọn</button>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('admin.product.create') }}"><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uiProductModal">Thêm sản phẩm</button></a>
        </div>
      </div>

      <div class="table-responsive">
        <table id="productTable" class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th class="checkAllWidth"><input type="checkbox" id="product_check_all"></th>
              <th class="STT_Width">#</th>
              <th>TÊN</th>
              <th>SLUG</th>
              <th>MÃ ĐỊNH DANH</th>
              <th>DANH MỤC</th>
              <th>TÁC GIẢ</th>
              <th>NHÀ XUẤT BẢN</th>
              <th>GIÁ BÁN</th>
              <th>TỒN</th>
              <th class="statusWidth">TRẠNG THÁI</th>
              <th class="actionWidth text-center">THAO TÁC</th>
            </tr>
          </thead>
          <tbody>
            @forelse($products as $idx => $p)
            <tr>
              <td><input type="checkbox" class="product-row-checkbox" value="{{ $p->id }}"></td>
              <td>{{ $products->firstItem() + $idx }}</td>
              <td>{{ $p->name }}</td>
              <td>{{ $p->slug }}</td>
              <td><code>{{ $p->isbn13 ?? '—' }}</code></td>
              <td>{{ $p->categories?->pluck('name')->join(', ') }}</td>
              <td>{{ $p->authors?->pluck('name')->join(', ') }}</td>
              <td>{{ $p->publisher?->name }}</td>
              <td>{{ number_format((int)$p->price,0,',','.') }}₫</td>
              <td>{{ (int)$p->stock }}</td>
              <td>
                @if($p->status === 'ACTIVE')
                <span class="badge bg-success">Đang bán</span>
                @else
                <span class="badge bg-secondary">Ẩn</span>
                @endif
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-success btnProductEdit" disabled>
                  <i class="fa fa-edit"></i>
                </button>
                <form method="POST" class="d-inline productDeleteForm">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger btnProductDelete" disabled>
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="12" class="text-center text-muted">Không có dữ liệu</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $products->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

{{-- Bulk form --}}
<form id="productBulkForm" method="POST" class="d-none">
  @csrf
  <div id="productBulkIds"></div>
</form>

{{-- giữ nguyên formState placeholder nếu cần về sau --}}
<div id="__formState"
  data-has-errors="0"
  data-which=""
  data-mode="create"
  data-update-action=""
  data-image=""
  style="display:none"></div>

{{-- Confirm modal dùng chung --}}
@include('partials.ui.confirm-modal')
@endsection

@push('scripts')
@vite('resources/js/pages/ecom-app-laravel_admin_product_index.js')
@endpush