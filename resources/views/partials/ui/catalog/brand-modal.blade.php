{{-- Modal: Brand --}}
<div class="modal fade" id="uiBrandModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="uiBrandForm" method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="_method" value="POST">
      <input type="hidden" name="__form" value="brand">
      <input type="hidden" name="__mode" value="create">
      <input type="hidden" name="__update_action" value="{{ old('__update_action','') }}">
      @php
        $isBrandCreateError = $errors->any() && old('__form')==='brand' && old('__mode','create')==='create';
      @endphp

      <div class="modal-header">
        <h3 class="modal-title">Brand</h3>
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
            @if($isBrandCreateError && $errors->has('image'))
              <div class="invalid-feedback d-block mt-1">{{ $errors->first('image') }}</div>
            @endif
          </div>

          <div class="col-lg-10">
            <div data-slug-scope class="row g-3">
              <div class="col-md-6">
                <label for="brand_name" class="form-label"><b>Tên nhà sản xuất</b> <span class="text-danger">*</span></label>
                <input id="brand_name" type="text" name="name" placeholder="Tên nhà sản xuất" data-slug-source
                  value="{{ $isBrandCreateError ? old('name') : '' }}"
                  class="form-control {{ $isBrandCreateError && $errors->has('name') ? 'is-invalid' : '' }}">
                @if($isBrandCreateError && $errors->has('name'))
                  <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
              </div>

              <div class="col-md-6">
                <label for="brand_slug" class="form-label"><b>Slug</b> <span class="text-danger">*</span></label>
                <input id="brand_slug" type="text" name="slug" placeholder="Slug" data-slug-dest
                  value="{{ $isBrandCreateError ? old('slug') : '' }}"
                  class="form-control {{ $isBrandCreateError && $errors->has('slug') ? 'is-invalid' : '' }}">
                @if($isBrandCreateError && $errors->has('slug'))
                  <div class="invalid-feedback">{{ $errors->first('slug') }}</div>
                @endif
              </div>

              <div class="col-12">
                <label for="brand_description" class="form-label"><b>Mô tả</b> <span class="text-danger">*</span></label>
                <textarea id="brand_description" name="description" rows="4"
                  class="form-control {{ $isBrandCreateError && $errors->has('description') ? 'is-invalid' : '' }}">{{ $isBrandCreateError ? old('description') : '' }}</textarea>
                @if($isBrandCreateError && $errors->has('description'))
                  <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                @endif
              </div>

              <div class="col-md-6 select2CustomWidth">
                <label for="brand_status" class="form-label"><b>Trạng thái</b></label>
                @php $bst = $isBrandCreateError ? old('status','ACTIVE') : 'ACTIVE'; @endphp
                <select id="brand_status" name="status" class="form-select setupSelect2 {{ $isBrandCreateError && $errors->has('status') ? 'is-invalid' : '' }}">
                  <option value="ACTIVE" {{ $bst==='ACTIVE'?'selected':'' }}>Kích hoạt</option>
                  <option value="INACTIVE" {{ $bst==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
                </select>
                @if($isBrandCreateError && $errors->has('status'))
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