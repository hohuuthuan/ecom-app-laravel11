@extends('layouts.admin')

@section('title','Products: Thêm sản phẩm')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.product.index') }}">Sản phẩm</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Thêm mới</li>
  </ol>
</nav>

<div class="card shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Thêm sản phẩm</h5>
  </div>

  <div class="card-body">
    <form id="productCreateForm">
      <div class="row">
        <!-- LEFT: Ảnh -->
        <div class="col-lg-3 mb-3">
          <label class="form-label label-filter-admin-product">Hình ảnh sản phẩm <span class="text-danger">*</span></label>
          <div class="product-image-box mb-2" id="previewBox">
            <span class="text-muted"><i class="fa-regular fa-image me-1"></i> Ảnh sản phẩm</span>
          </div>

          <div class="product-image-actions mb-1">
            <label class="btn btn-outline-primary w-100 position-relative m-0">
              <i class="fa-solid fa-upload me-1"></i> Chọn ảnh
              <input type="file" accept="image/*" id="productImageFile" class="file-input">
            </label>
            <button type="button" class="btn btn-outline-secondary" id="btnClearImage" aria-label="Xóa ảnh">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <div class="form-text-compact">Chỉ chấp nhận định dạng: jpg, jpeg, png, webp</div>
          <div class="form-text-compact">Kích thước tối đa 10MB</div>
        </div>

        <!-- RIGHT: Thông tin -->
        <div data-slug-scope class="col-lg-9">
          <div class="row g-3 mb-2">
            <div class="col-md-6">
              <label class="form-label label-filter-admin-product">Tên sản phẩm <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="Nhập tên sản phẩm" name="title" data-slug-source>
            </div>
            <div class="col-md-6">
              <label class="form-label label-filter-admin-product">Slug</label>
              <input type="text" class="form-control" placeholder="slug-tu-dong" name="slug" data-slug-dest>
            </div>
          </div>

          <div class="row g-3 mb-2">
            <div class="col-md-6">
              <label class="form-label label-filter-admin-product">Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="Mã nội bộ" name="code">
            </div>
            <div class="col-md-6">
              <label class="form-label label-filter-admin-product">ISBN <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="ISBN" name="isbn">
            </div>
          </div>

          <div class="row g-3 mb-2">
            <div class="col-12">
              <label class="form-label label-filter-admin-product">Mô tả sản phẩm <span class="text-danger">*</span></label>
              <textarea rows="4" class="form-control" placeholder="Mô tả chi tiết…" name="description"></textarea>
            </div>
          </div>

          <div class="row g-3 mb-2">
            <div class="col-md-6">
              <label class="form-label label-filter-admin-product">Giá bán <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" min="0" step="1" class="form-control" placeholder="0" name="price">
                <span class="input-group-text">₫</span>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label label-filter-admin-product">Đơn vị tính <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="Quyển / Bộ / ..." name="unit">
            </div>
          </div>

          <!-- Tagify: Danh mục / Tác giả -->
          <div class="row g-4">
            <div class="col-lg-6">
              <label class="form-label label-filter-admin-product">Chọn danh mục <span class="text-danger">*</span></label>
              <input
                id="categoriesInput"
                class="tag-input"
                placeholder="Gõ để chọn danh mục…"
                name="categories"
                data-source='@json($categories)'>
              <div class="form-text">Gõ để tìm, Enter để chọn, Backspace xóa</div>
            </div>
            <div class="col-lg-6">
              <label class="form-label label-filter-admin-product">Chọn tác giả <span class="text-danger">*</span></label>
              <input
                id="authorsInput"
                class="tag-input"
                placeholder="Gõ để chọn tác giả…"
                name="authors"
                data-source='@json($authors)'>
              <div class="form-text">Gõ để tìm, Enter để chọn, Backspace xóa</div>
            </div>
          </div>

          <div class="row g-4 mb-2">
            <div class="col-md-6">
              <label class="form-label label-filter-admin-product">Nhà xuất bản <span class="text-danger">*</span></label>
              <select class="form-select setupSelect2" name="publisher_id">
                <option value="">-- Chọn NXB --</option>
                @foreach ($publishers as $pub)
                  <option value="{{ $pub['id'] }}">{{ $pub['name'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label label-filter-admin-product">Trạng thái <span class="text-danger">*</span></label>
              <select class="form-select setupSelect2" name="status">
                <option value="">-- Chọn trạng thái --</option>
                <option value="ACTIVE">Đang bán</option>
                <option value="INACTIVE">Ẩn</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <hr class="my-3">
      <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-primary" id="btnSaveDraftBottom">
          <i class="fa-solid fa-floppy-disk me-1"></i> Lưu nháp
        </button>
        <button type="submit" class="btn btn-success">
          <i class="fa-solid fa-check me-1"></i> Lưu sản phẩm
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('head')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
  @vite('resources/js/pages/admin_product_create.js')
  @vite(['resources/js/pages/slugify.js'])
@endpush
