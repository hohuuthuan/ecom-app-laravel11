{{-- Modal: Category --}}
<div class="modal fade" id="uiCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="uiCategoryForm" method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="_method" value="POST">

      <div class="modal-header">
        <h3 class="modal-title">Category</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 align-items-start">
          <div class="col-lg-2">
            <div class="ac-avatar">
              <img id="cat_image_preview" src="" alt="Image">
            </div>
            <label class="form-label mt-2 label-select-image"><b>Chọn hình ảnh</b></label>
            <input id="cat_image" name="image" type="file" accept="image/*" class="visually-hidden">
            <label for="cat_image" class="btn btn-primary w-100" id="btnPickCatImage">
              <i class="fa fa-upload me-1"></i>
            </label>
            <div class="invalid-feedback d-none mt-1" data-err="image"></div>
          </div>

          <div class="col-lg-10">
            <div data-slug-scope class="row g-3">
              <div class="col-md-6">
                <label for="cat_name" class="form-label"><b>Tên danh mục</b> <span class="text-danger">*</span></label>
                <input id="cat_name" type="text" name="name"  class="form-control" placeholder="Tên danh mục" data-slug-source>
                <div class="invalid-feedback d-none" data-err="name"></div>
              </div>
              <div class="col-md-6">
                <label for="cat_slug" class="form-label"><b>Slug</b> <span class="text-danger">*</span></label>
                <input id="cat_slug" type="text" name="slug" class="form-control mt-2" placeholder="Slug" data-slug-dest>
                <div class="invalid-feedback d-none" data-err="slug"></div>
              </div>
              <div class="col-12">
                <label for="cat_description" class="form-label"><b>Mô tả</b> <span class="text-danger">*</span></label>
                <textarea id="cat_description" name="description" class="form-control" rows="4"></textarea>
                <div class="invalid-feedback d-none" data-err="description"></div>
              </div>
              <div class="col-md-6 select2CustomWidth">
                <label for="cat_status" class="form-label"><b>Trạng thái</b></label>
                <select id="cat_status" name="status" class="form-select setupSelect2">
                  <option value="ACTIVE" selected>Kích hoạt</option>
                  <option value="INACTIVE">Ngừng hoạt động</option>
                </select>
                <div class="invalid-feedback d-none" data-err="status"></div>
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
