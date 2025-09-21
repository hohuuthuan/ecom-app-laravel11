{{-- Modal: Brand --}}
<div class="modal fade" id="uiBrandModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content"
          id="uiBrandForm"
          method="POST"
          action="{{ route('admin.brands.store') }}"
          enctype="multipart/form-data"
          data-store-url="{{ route('admin.brands.store') }}">
      @csrf
      <input type="hidden" name="_method" value="POST">
      <input type="hidden" name="__form" value="brand">
      <input type="hidden" name="__mode" value="{{ old('__mode','create') }}">
      <input type="hidden" name="__update_action" value="{{ old('__update_action','') }}">
      <input type="hidden" name="__image" value="{{ old('__image','') }}">

      @php
        $isBrandError = $errors->any() && old('__form')==='brand';
        $st = $isBrandError ? old('status','ACTIVE') : 'ACTIVE';
      @endphp

      <div class="modal-header">
        <h3 class="modal-title" id="brandModalTitle">Thêm NSX</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 align-items-start">
          <div class="col-lg-2">
            <div class="ac-avatar position-relative">
              <img id="brand_image_preview" src="" class="d-none" />
              <div id="brand_image_placeholder" class="text-body text-center py-4">Chưa có hình ảnh</div>
            </div>

            <label class="form-label mt-2 label-select-image"><b>Chọn hình ảnh</b></label>
            <input id="brand_image" name="image" type="file" accept="image/*" class="visually-hidden">
            <label for="brand_image" class="btn btn-primary w-100" id="btnPickBrandImage">
              <i class="fa fa-upload me-1"></i>
            </label>
            @if($isBrandError && $errors->has('image'))
              <div class="invalid-feedback d-block mt-1">{{ $errors->first('image') }}</div>
            @endif
          </div>

          <div class="col-lg-10">
            <div data-slug-scope class="row g-3">
              <div class="col-md-6">
                <label for="brand_name" class="form-label"><b>Tên NSX</b> <span class="text-danger">*</span></label>
                <input id="brand_name" type="text" name="name" placeholder="Tên NSX" data-slug-source
                  value="{{ $isBrandError ? old('name') : '' }}"
                  class="form-control {{ $isBrandError && $errors->has('name') ? 'is-invalid' : '' }}">
                @if($isBrandError && $errors->has('name'))
                  <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
              </div>

              <div class="col-md-6">
                <label for="brand_slug" class="form-label"><b>Slug</b> <span class="text-danger">*</span></label>
                <input id="brand_slug" type="text" name="slug" placeholder="Slug" data-slug-dest
                  value="{{ $isBrandError ? old('slug') : '' }}"
                  class="form-control {{ $isBrandError && $errors->has('slug') ? 'is-invalid' : '' }}">
                @if($isBrandError && $errors->has('slug'))
                  <div class="invalid-feedback">{{ $errors->first('slug') }}</div>
                @endif
              </div>

              <div class="col-12">
                <label for="brand_description" class="form-label"><b>Mô tả</b> <span class="text-danger">*</span></label>
                <textarea id="brand_description" name="description" rows="4"
                  class="form-control {{ $isBrandError && $errors->has('description') ? 'is-invalid' : '' }}">{{ $isBrandError ? old('description') : '' }}</textarea>
                @if($isBrandError && $errors->has('description'))
                  <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                @endif
              </div>

              <div class="col-md-6 select2CustomWidth">
                <label for="brand_status" class="form-label"><b>Trạng thái</b></label>
                <select id="brand_status" name="status" class="form-select setupSelect2 {{ $isBrandError && $errors->has('status') ? 'is-invalid' : '' }}">
                  <option value="ACTIVE" {{ $st==='ACTIVE'?'selected':'' }}>Kích hoạt</option>
                  <option value="INACTIVE" {{ $st==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
                </select>
                @if($isBrandError && $errors->has('status'))
                  <div class="invalid-feedback">{{ $errors->first('status') }}</div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-cancel-modal" data-bs-dismiss="modal">Huỷ</button>
        <button type="submit" class="btn btn-success btn-submit-modal">Lưu</button>
      </div>
    </form>
  </div>
</div>

<script src="{{ asset('library/slugify.js') }}"></script>
