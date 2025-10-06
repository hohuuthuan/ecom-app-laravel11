{{-- resources/views/admin/product/edit.blade.php --}}
@extends('layouts.admin')

@section('title','Products: Chỉnh sửa sản phẩm')
@section('body_class','edit-product-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.product.index') }}">Sản phẩm</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Chỉnh sửa</li>
  </ol>
</nav>

<div class="table-in-clip">
  <div class="card shadow-sm table-in">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Cập nhật: {{ $product->title }}</h5>
    </div>

    <div class="card-body">
      <form id="productEditForm" method="POST" action="{{ route('admin.product.update', $product->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
          <!-- LEFT: Ảnh -->
          <div class="col-lg-3 mb-3">
            <label class="form-label label-filter-admin-product">Hình ảnh sản phẩm <span class="text-danger">*</span></label>
            <div class="product-image-box mb-2 {{ $errors->has('image') ? 'is-invalid' : '' }}" id="previewBox">
              @if($product->image)
                <img src="{{ asset('storage/products/'.$product->image) }}" alt="preview">
              @else
                <span class="text-muted"><i class="fa-regular fa-image me-1"></i> Ảnh sản phẩm</span>
              @endif
            </div>

            <div class="product-image-actions mb-1">
              <label class="btn btn-outline-primary w-100 position-relative m-0 {{ $errors->has('image') ? 'is-invalid' : '' }}">
                <i class="fa-solid fa-upload me-1"></i> Chọn ảnh mới
                <input type="file" accept="image/*" id="productImageFile" class="file-input" name="image">
              </label>
              <button type="button" class="btn btn-outline-secondary" id="btnClearImage" aria-label="Xóa ảnh">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <div class="form-text-compact">Chỉ chấp nhận định dạng: jpg, jpeg, png, webp</div>
            <div class="form-text-compact">Kích thước tối đa 10MB</div>
            @if($errors->has('image'))
              <div class="invalid-feedback d-block mt-1">{{ $errors->first('image') }}</div>
            @endif
          </div>

          <!-- RIGHT: Thông tin -->
          <div data-slug-scope class="col-lg-9">
            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label label-filter-admin-product">Tên sản phẩm <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                       placeholder="Nhập tên sản phẩm"
                       name="title"
                       value="{{ old('title', $product->title) }}"
                       data-slug-source>
                @if($errors->has('title')) <div class="invalid-feedback">{{ $errors->first('title') }}</div> @endif
              </div>
              <div class="col-md-6">
                <label class="form-label label-filter-admin-product">Slug <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}"
                       placeholder="slug-tu-dong"
                       name="slug"
                       value="{{ old('slug', $product->slug) }}"
                       data-slug-dest>
                @if($errors->has('slug')) <div class="invalid-feedback">{{ $errors->first('slug') }}</div> @endif
              </div>
            </div>

            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label label-filter-admin-product mt-2">Code <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                       placeholder="Mã nội bộ"
                       name="code"
                       value="{{ old('code', $product->code) }}">
                @if($errors->has('code')) <div class="invalid-feedback">{{ $errors->first('code') }}</div> @endif
              </div>
              <div class="col-md-6">
                <label class="form-label label-filter-admin-product mt-2">ISBN <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control {{ $errors->has('isbn') ? 'is-invalid' : '' }}"
                       placeholder="ISBN"
                       name="isbn"
                       value="{{ old('isbn', $product->isbn) }}">
                @if($errors->has('isbn')) <div class="invalid-feedback">{{ $errors->first('isbn') }}</div> @endif
              </div>
            </div>

            <div class="row g-3 mb-2">
              <div class="col-12">
                <label class="form-label label-filter-admin-product mt-2">Mô tả sản phẩm <span class="text-danger">*</span></label>
                <textarea rows="4"
                          class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                          placeholder="Mô tả chi tiết…"
                          name="description">{{ old('description', $product->description) }}</textarea>
                @if($errors->has('description')) <div class="invalid-feedback">{{ $errors->first('description') }}</div> @endif
              </div>
            </div>

            <div class="row g-3 mb-2">
              <div class="col-md-6">
                <label class="form-label label-filter-admin-product mt-2">Giá bán <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="number"
                         min="1"
                         step="1"
                         class="form-control {{ $errors->has('selling_price_vnd') ? 'is-invalid' : '' }}"
                         placeholder="0"
                         name="selling_price_vnd"
                         value="{{ old('selling_price_vnd', $product->selling_price_vnd) }}">
                  <span class="input-group-text">VND</span>
                </div>
                @if($errors->has('selling_price_vnd')) <div class="invalid-feedback d-block">{{ $errors->first('selling_price_vnd') }}</div> @endif
              </div>
              <div class="col-md-6">
                <label class="form-label label-filter-admin-product mt-2">Đơn vị tính <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control {{ $errors->has('unit') ? 'is-invalid' : '' }}"
                       placeholder="Quyển / Bộ / ..."
                       name="unit"
                       value="{{ old('unit', $product->unit) }}">
                @if($errors->has('unit')) <div class="invalid-feedback">{{ $errors->first('unit') }}</div> @endif
              </div>
            </div>

            <!-- Tagify: Danh mục / Tác giả -->
            <div class="row g-4">
              <div class="col-lg-6">
                <label class="form-label label-filter-admin-product mt-2">Chọn danh mục <span class="text-danger">*</span></label>
                <input id="categoriesInput"
                       class="tag-input {{ ($errors->has('categoriesInput') || $errors->has('categoriesInput.*')) ? 'is-invalid' : '' }}"
                       placeholder="Gõ để chọn danh mục…"
                       name="categoriesInput"
                       data-source='@json($categories)'
                       data-old='@json(old("categoriesInput", $product->categories->pluck("id")))'>
                <div class="form-text">Gõ để tìm, Enter để chọn, Backspace xóa</div>
                @php $catError = $errors->first('categoriesInput') ?: $errors->first('categoriesInput.*'); @endphp
                @if($catError) <div class="invalid-feedback d-block">{{ $catError }}</div> @endif
              </div>

              <div class="col-lg-6">
                <label class="form-label label-filter-admin-product mt-2">Chọn tác giả <span class="text-danger">*</span></label>
                <input id="authorsInput"
                       class="tag-input {{ ($errors->has('authorsInput') || $errors->has('authorsInput.*')) ? 'is-invalid' : '' }}"
                       placeholder="Gõ để chọn tác giả…"
                       name="authorsInput"
                       data-source='@json($authors)'
                       data-old='@json(old("authorsInput", $product->authors->pluck("id")))'>
                <div class="form-text">Gõ để tìm, Enter để chọn, Backspace xóa</div>
                @php $authError = $errors->first('authorsInput') ?: $errors->first('authorsInput.*'); @endphp
                @if($authError) <div class="invalid-feedback d-block">{{ $authError }}</div> @endif
              </div>
            </div>

            <div class="row g-4 mb-2">
              <div class="col-md-6">
                <label class="form-label label-filter-admin-product mt-3">Nhà xuất bản <span class="text-danger">*</span></label>
                @php $oldPub = old('publisher_id', $product->publisher_id); @endphp
                <select class="form-select setupSelect2 {{ $errors->has('publisher_id') ? 'is-invalid' : '' }}" name="publisher_id">
                  <option value="">-- Chọn NXB --</option>
                  @foreach ($publishers as $pub)
                    <option value="{{ $pub['id'] }}" {{ $oldPub===$pub['id'] ? 'selected' : '' }}>
                      {{ $pub['name'] }}
                    </option>
                  @endforeach
                </select>
                @if($errors->has('publisher_id')) <div class="invalid-feedback d-block">{{ $errors->first('publisher_id') }}</div> @endif
              </div>

              <div class="col-md-6">
                <label class="form-label label-filter-admin-product mt-3">Trạng thái <span class="text-danger">*</span></label>
                @php $oldStatus = old('status', $product->status); @endphp
                <select class="form-select setupSelect2 {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status">
                  <option value="">-- Chọn trạng thái --</option>
                  <option value="ACTIVE"   {{ $oldStatus==='ACTIVE' ? 'selected' : '' }}>Đang bán</option>
                  <option value="INACTIVE" {{ $oldStatus==='INACTIVE' ? 'selected' : '' }}>Ẩn</option>
                </select>
                @if($errors->has('status')) <div class="invalid-feedback d-block">{{ $errors->first('status') }}</div> @endif
              </div>
            </div>
          </div>
        </div>

        <hr class="my-3">
        <div class="d-flex justify-content-end gap-2">
          <button type="submit" class="btn btn-success">
            <i class="fa-solid fa-check me-1"></i> Cập nhật
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@include('partials.ui.confirm-modal')
@endsection

@push('scripts')
  @vite('resources/js/pages/admin_product_edit.js')
  @vite(['resources/js/pages/slugify.js'])
@endpush
