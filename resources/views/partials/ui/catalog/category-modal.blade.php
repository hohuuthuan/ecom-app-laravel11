{{-- Modal: Category --}}
<div class="modal fade" id="uiCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="uiCategoryForm" method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="_method" value="POST">
      <input type="hidden" name="__form" value="category">
      <input type="hidden" name="__mode" value="create">
      <input type="hidden" name="__update_action" value="{{ old('__update_action','') }}">
      @php
        $isCatCreateError = $errors->any() && old('__form')==='category' && old('__mode','create')==='create';
      @endphp

      <div class="modal-header">
        <h3 class="modal-title">Category</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 align-items-start">
          <div class="col-lg-2">
            <div class="ac-avatar position-relative">
              <img id="cat_image_preview" src="" class="d-none" />
              <div id="cat_image_placeholder" class="text-body text-center py-4">Chưa có hình ảnh</div>
            </div>

            <label class="form-label mt-2 label-select-image"><b>Chọn hình ảnh</b></label>
            <input id="cat_image" name="image" type="file" accept="image/*" class="visually-hidden">
            <label for="cat_image" class="btn btn-primary w-100" id="btnPickCatImage">
              <i class="fa fa-upload me-1"></i>
            </label>
            @if($isCatCreateError && $errors->has('image'))
              <div class="invalid-feedback d-block mt-1">{{ $errors->first('image') }}</div>
            @endif
          </div>

          <div class="col-lg-10">
            <div data-slug-scope class="row g-3">
              <div class="col-md-6">
                <label for="cat_name" class="form-label"><b>Tên danh mục</b> <span class="text-danger">*</span></label>
                <input id="cat_name" type="text" name="name" placeholder="Tên danh mục" data-slug-source
                  value="{{ $isCatCreateError ? old('name') : '' }}"
                  class="form-control {{ $isCatCreateError && $errors->has('name') ? 'is-invalid' : '' }}">
                @if($isCatCreateError && $errors->has('name'))
                  <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
              </div>

              <div class="col-md-6">
                <label for="cat_slug" class="form-label"><b>Slug</b> <span class="text-danger">*</span></label>
                <input id="cat_slug" type="text" name="slug" placeholder="Slug" data-slug-dest
                  value="{{ $isCatCreateError ? old('slug') : '' }}"
                  class="form-control {{ $isCatCreateError && $errors->has('slug') ? 'is-invalid' : '' }}">
                @if($isCatCreateError && $errors->has('slug'))
                  <div class="invalid-feedback">{{ $errors->first('slug') }}</div>
                @endif
              </div>

              <div class="col-12">
                <label for="cat_description" class="form-label"><b>Mô tả</b> <span class="text-danger">*</span></label>
                <textarea id="cat_description" name="description" rows="4"
                  class="form-control {{ $isCatCreateError && $errors->has('description') ? 'is-invalid' : '' }}">{{ $isCatCreateError ? old('description') : '' }}</textarea>
                @if($isCatCreateError && $errors->has('description'))
                  <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                @endif
              </div>

              <div class="col-md-6 select2CustomWidth">
                <label for="cat_status" class="form-label"><b>Trạng thái</b></label>
                @php $st = $isCatCreateError ? old('status','ACTIVE') : 'ACTIVE'; @endphp
                <select id="cat_status" name="status" class="form-select setupSelect2 {{ $isCatCreateError && $errors->has('status') ? 'is-invalid' : '' }}">
                  <option value="ACTIVE" {{ $st==='ACTIVE'?'selected':'' }}>Kích hoạt</option>
                  <option value="INACTIVE" {{ $st==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
                </select>
                @if($isCatCreateError && $errors->has('status'))
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
