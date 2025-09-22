@extends('layouts.admin')

@section('title','Catalog: Category & Brand')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Danh mục & NSX</li>
  </ol>
</nav>

<ul class="nav nav-tabs mb-3" id="catalogTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="category-tab" data-bs-toggle="tab" data-bs-target="#category-pane" type="button" role="tab">
      Danh mục
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="brand-tab" data-bs-toggle="tab" data-bs-target="#brand-pane" type="button" role="tab">
      NSX
    </button>
  </li>
</ul>

<div class="tab-content" id="catalogTabsContent">
  {{-- ================== TAB 1: CATEGORY ================== --}}
  <div class="tab-pane fade show active" id="category-pane" role="tabpanel" aria-labelledby="category-tab">
    <div class="table-in-clip">
      <div class="card shadow-sm table-in">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Danh sách category</h5>
          <form method="GET" class="d-flex align-items-center">
            <label class="me-2 mb-0">Hiển thị</label>
            <select class="form-select form-select-sm w-auto setupSelect2" name="per_page_cat" onchange="this.form.submit()">
              <option value="10" {{ request('per_page_cat')==10?'selected':'' }}>10</option>
              <option value="20" {{ request('per_page_cat')==20?'selected':'' }}>20</option>
              <option value="50" {{ request('per_page_cat')==50?'selected':'' }}>50</option>
            </select>
            <input type="hidden" name="tab" value="category">
            <input type="hidden" name="brand_keyword" value="{{ request('brand_keyword') }}">
            <input type="hidden" name="brand_status" value="{{ request('brand_status') }}">
            <input type="hidden" name="per_page_brand" value="{{ request('per_page_brand') }}">
          </form>
        </div>

        <div class="card-body">
          {{-- Filters --}}
          <form method="GET" class="row g-2 mb-3 filter-form">
            <div class="col-md-4">
              <input type="text" name="cat_keyword" class="form-control" placeholder="Tìm tên / slug" value="{{ request('cat_keyword') }}">
            </div>
            <div class="col-md-2">
              <select name="cat_status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="ACTIVE" {{ request('cat_status')==='ACTIVE'?'selected':'' }}>Đang hoạt động</option>
                <option value="INACTIVE" {{ request('cat_status')==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
              </select>
            </div>
            <div class="col-md-1 d-grid">
              <button type="submit" class="btn btn-primary btn-admin">Lọc</button>
            </div>
            <input type="hidden" name="tab" value="category">
            <input type="hidden" name="brand_keyword" value="{{ request('brand_keyword') }}">
            <input type="hidden" name="brand_status" value="{{ request('brand_status') }}">
            <input type="hidden" name="per_page_brand" value="{{ request('per_page_brand') }}">
          </form>

          {{-- Bulk actions --}}
          <div class="d-flex justify-content-between mb-2">
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-danger btn-admin" id="catBtnBulkDelete" disabled>Xoá đã chọn</button>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uiCategoryModal">Thêm danh mục</button>
            </div>
          </div>

          {{-- Table --}}
          <div class="table-responsive">
            <table id="categoryTable" class="table table-bordered table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th class="checkAllWidth"><input type="checkbox" id="cat_check_all"></th>
                  <th class="STT_Width">#</th>
                  <th>Tên</th>
                  <th>Slug</th>
                  <th class="statusWidth">Trạng thái</th>
                  <th class="createTimeWidth">Tạo lúc</th>
                  <th class="actionWihdth text-center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($categories as $index => $cat)
                <tr>
                  <td><input type="checkbox" class="cat-row-checkbox" value="{{ $cat->id }}"></td>
                  <td>{{ $categories->firstItem() + $index }}</td>
                  <td>{{ $cat->name }}</td>
                  <td>{{ $cat->slug }}</td>
                  <td>
                    @if($cat->status == 'ACTIVE')
                    <span class="badge bg-success">Đang hoạt động</span>
                    @else
                    <span class="badge bg-danger">Ngừng hoạt động</span>
                    @endif
                  </td>
                  <td>{{ $cat->created_at?->format('d/m/Y H:i') }}</td>
                  <td class="text-center">
                    <button type="button"
                      class="btn btn-sm btn-success btnCateEdit"
                      data-update-url="{{ route('admin.categories.update', $cat->id) }}"
                      data-name="{{ $cat->name }}"
                      data-slug="{{ $cat->slug }}"
                      data-description="{{ $cat->description }}"
                      data-status="{{ $cat->status }}"
                      data-image="{{ $cat->image ? Storage::url('categories/'.$cat->image) : '' }}">
                      <i class="fa fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}" class="d-inline catDeleteForm">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger btnCateDelete">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </form>

                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            {{ $categories->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ================== TAB 2: BRAND ================== --}}
  <div class="tab-pane fade" id="brand-pane" role="tabpanel" aria-labelledby="brand-tab">
    <div class="table-in-clip">
      <div class="card shadow-sm table-in">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Danh sách brand</h5>
          <form method="GET" class="d-flex align-items-center">
            <label class="me-2 mb-0">Hiển thị</label>
            <select class="form-select form-select-sm w-auto setupSelect2" name="per_page_brand" onchange="this.form.submit()">
              <option value="10" {{ request('per_page_brand')==10?'selected':'' }}>10</option>
              <option value="20" {{ request('per_page_brand')==20?'selected':'' }}>20</option>
              <option value="50" {{ request('per_page_brand')==50?'selected':'' }}>50</option>
            </select>
            <input type="hidden" name="tab" value="brand">
            {{-- bảo toàn filter category --}}
            <input type="hidden" name="cat_keyword" value="{{ request('cat_keyword') }}">
            <input type="hidden" name="cat_status" value="{{ request('cat_status') }}">
            <input type="hidden" name="per_page_cat" value="{{ request('per_page_cat') }}">
          </form>
        </div>

        <div class="card-body">
          {{-- Filters --}}
          <form method="GET" class="row g-2 mb-3 filter-form">
            <div class="col-md-4">
              <input type="text" name="brand_keyword" class="form-control" placeholder="Tìm tên / slug" value="{{ request('brand_keyword') }}">
            </div>
            <div class="col-md-2 select2CustomWidth">
              <select name="brand_status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="ACTIVE" {{ request('brand_status')==='ACTIVE'?'selected':'' }}>Đang hoạt động</option>
                <option value="INACTIVE" {{ request('brand_status')==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
              </select>
            </div>
            <div class="col-md-1 d-grid">
              <button type="submit" class="btn btn-primary btn-admin">Lọc</button>
            </div>
            <input type="hidden" name="tab" value="brand">
            {{-- bảo toàn filter category --}}
            <input type="hidden" name="cat_keyword" value="{{ request('cat_keyword') }}">
            <input type="hidden" name="cat_status" value="{{ request('cat_status') }}">
            <input type="hidden" name="per_page_cat" value="{{ request('per_page_cat') }}">
          </form>

          {{-- Bulk actions --}}
          <div class="d-flex justify-content-between mb-2">
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-danger btn-admin" id="brandBtnBulkDelete" disabled>Xoá đã chọn</button>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uiBrandModal">Thêm NSX</button>
            </div>
          </div>

          {{-- Table --}}
          <div class="table-responsive">
            <table id="brandTable" class="table table-bordered table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th class="checkAllWidth"><input type="checkbox" id="brand_check_all"></th>
                  <th class="STT_Width">#</th>
                  <th>Tên</th>
                  <th>Slug</th>
                  <th class="statusWidth">Trạng thái</th>
                  <th class="createTimeWidth">Tạo lúc</th>
                  <th class="actionWihdth text-center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($brands as $index => $brand)
                <tr>
                  <td><input type="checkbox" class="brand-row-checkbox" value="{{ $brand->id }}"></td>
                  <td>{{ $brands->firstItem() + $index }}</td>
                  <td>{{ $brand->name }}</td>
                  <td>{{ $brand->slug }}</td>
                  <td>
                    @if($brand->status === 'ACTIVE')
                    <span class="badge bg-success">Đang hoạt động</span>
                    @else
                    <span class="badge bg-secondary">Ngừng hoạt động</span>
                    @endif
                  </td>
                  <td>{{ $brand->created_at?->format('d/m/Y H:i') }}</td>
                  <td class="text-center">
                    <button type="button"
                      class="btn btn-sm btn-success btnBrandEdit"
                      data-update-url="{{ route('admin.brands.update', $brand->id) }}"
                      data-name="{{ $brand->name }}"
                      data-slug="{{ $brand->slug }}"
                      data-description="{{ $brand->description }}"
                      data-status="{{ $brand->status }}"
                      data-image="{{ $brand->image ? Storage::url('brands/'.$brand->image) : '' }}">
                      <i class="fa fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.brands.destroy', $brand->id) }}" class="d-inline brandDeleteForm">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger btnBrandDelete">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            {{ $brands->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Bulk forms --}}
<form id="catBulkForm" method="POST" action="{{ route('admin.categories.bulk-delete') }}" class="d-none">
  @csrf
  <div id="catBulkIds"></div>
</form>
<form id="brandBulkForm" method="POST" action="{{ route('admin.brands.bulk-delete') }}" class="d-none">
  @csrf
  <div id="brandBulkIds"></div>
</form>

<div id="__formState"
     data-has-errors="{{ $errors->any()?1:0 }}"
     data-which="{{ old('__form','') }}"
     data-mode="{{ old('__mode','create') }}"
     data-update-action="{{ old('__update_action','') }}"
     data-image="{{ old('__image','') }}"
     style="display:none"></div>

{{-- Modals: Category & Brand --}}
@include('partials.ui.catalog.category-modal')
@include('partials.ui.catalog.brand-modal')
@include('partials.ui.confirm-modal')
@endsection
@push('scripts')
  @vite('resources/js/pages/ecom-app-laravel_admin_catalog_index.js')
@endpush