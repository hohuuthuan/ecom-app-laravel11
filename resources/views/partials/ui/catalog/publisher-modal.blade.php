{{-- Modal: Publisher --}}
<div class="modal fade" id="uiPublisherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content"
          id="uiPublisherForm"
          method="POST"
          action="{{ route('admin.publishers.store') }}"
          enctype="multipart/form-data"
          data-store-url="{{ route('admin.publishers.store') }}">
      @csrf
      <input type="hidden" name="_method" value="POST">
      <input type="hidden" name="__form" value="publisher">
      <input type="hidden" name="__mode" value="{{ old('__mode','create') }}">
      <input type="hidden" name="__update_action" value="{{ old('__update_action','') }}">
      <input type="hidden" name="__image" value="{{ old('__image','') }}">

      @php
        // Hiển thị lỗi cho cả create và edit của form "publisher"
        $isPublisherError = $errors->any() && old('__form')==='publisher';
        $st = $isPublisherError ? old('status','ACTIVE') : 'ACTIVE';
      @endphp

      <div class="modal-header">
        <h3 class="modal-title" id="authorModalTitle">Thêm nhà xuất bản</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 align-items-start">
          <div class="col-lg-2">
            <div class="ac-avatar position-relative">
              <img id="publisher_logo_preview" src="" class="d-none" />
              <div id="publisher_logo_placeholder" class="text-body text-center py-4">Chưa có logo</div>
            </div>

            <label class="form-label mt-2 label-select-image"><b>Tải lên logo <span class="text-danger">*</span></b></label>
            <input id="publisher_logo" name="logo" type="file" accept="image/*" class="visually-hidden">
            <label for="publisher_logo" class="btn btn-primary w-100" id="btnPickPublisherImage">
              <i class="fa fa-upload me-1"></i>
            </label>
            @if($isPublisherError && $errors->has('logo'))
              <div class="invalid-feedback d-block mt-1">{{ $errors->first('logo') }}</div>
            @endif
          </div>

          <div class="col-lg-10">
            <div data-slug-scope class="row g-3">
              <div class="col-md-6">
                <label for="publisher_name" class="form-label"><b>Tên nhà xuất bản</b> <span class="text-danger">*</span></label>
                <input id="publisher_name" type="text" name="name" placeholder="Tên nhà xuất bản" data-slug-source
                  value="{{ $isPublisherError ? old('name') : '' }}"
                  class="form-control {{ $isPublisherError && $errors->has('name') ? 'is-invalid' : '' }}">
                @if($isPublisherError && $errors->has('name'))
                  <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
              </div>

              <div class="col-md-6">
                <label for="publisher_slug" class="form-label"><b>Slug</b> <span class="text-danger">*</span></label>
                <input id="publisher_slug" type="text" name="slug" placeholder="Slug" data-slug-dest
                  value="{{ $isPublisherError ? old('slug') : '' }}"
                  class="form-control {{ $isPublisherError && $errors->has('slug') ? 'is-invalid' : '' }}">
                @if($isPublisherError && $errors->has('slug'))
                  <div class="invalid-feedback">{{ $errors->first('slug') }}</div>
                @endif
              </div>

              <div class="col-12">
                <label for="publisher_description" class="form-label"><b>Mô tả</b> <span class="text-danger">*</span></label>
                <textarea id="publisher_description" name="description" rows="4"
                  class="form-control {{ $isPublisherError && $errors->has('description') ? 'is-invalid' : '' }}">{{ $isPublisherError ? old('description') : '' }}</textarea>
                @if($isPublisherError && $errors->has('description'))
                  <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                @endif
              </div>

              <div class="col-md-6 select2CustomWidth">
                <label for="publisher_status" class="form-label"><b>Trạng thái</b></label>
                <select id="publisher_status" name="status" class="form-select setupSelect2 {{ $isPublisherError && $errors->has('status') ? 'is-invalid' : '' }}">
                  <option value="ACTIVE" {{ $st==='ACTIVE'?'selected':'' }}>Kích hoạt</option>
                  <option value="INACTIVE" {{ $st==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
                </select>
                @if($isPublisherError && $errors->has('status'))
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
