{{-- Modal: Chỉnh sửa tài khoản --}}
<div class="modal fade" id="uiAccountEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="uiAccountEditForm" method="POST" enctype="multipart/form-data" action="{{ old('__update_action') }}">
      @csrf @method('PUT')

      <div class="modal-header">
        <h3 class="modal-title">Chỉnh sửa thông tin</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 align-items-start">
          {{-- 2 cột: Avatar --}}
          <div class="col-lg-2">
            <div class="ac-avatar">
              <img id="ac_avatar_preview" src="" alt="Avatar">
            </div>

            <label class="form-label mt-2 label-select-image"><b>Chọn hình ảnh</b></label>

            <input id="ac_avatar" name="avatar" type="file" accept="image/*" class="visually-hidden">
            <label for="ac_avatar" class="btn btn-primary w-100 input-select-image" id="btnPickAvatar">
              <i class="fa fa-upload me-1"></i>
            </label>
            <div class="invalid-feedback d-none mt-1" data-err="avatar"></div>
          </div>

          {{-- 10 cột: Thông tin --}}
          <div class="col-lg-10">
            <div class="row g-3">
              <div class="col-md-4">
                <label for="ac_full_name" class="form-label"><b>Họ tên</b> <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="ac_full_name" name="full_name" value="{{ old('full_name') }}" placeholder="Nhập họ tên" required>
                <div class="invalid-feedback d-none" data-err="full_name"></div>
              </div>

              <div class="col-md-4">
                <label for="ac_email" class="form-label"><b>Email</b> <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="ac_email" name="email" value="{{ old('email') }}" placeholder="Nhập email" required>
                <div class="invalid-feedback d-none" data-err="email"></div>
              </div>

              <div class="col-md-4">
                <label for="ac_phone" class="form-label"><b>Số điện thoại</b> <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="ac_phone" name="phone" value="{{ old('phone') }}" placeholder="Nhập số điện thoại">
                <div class="invalid-feedback d-none" data-err="phone"></div>
              </div>

              <div class="col-12">
                <label for="ac_address" class="form-label"><b>Địa chỉ</b></label>
                <textarea id="ac_address" name="address" class="form-control" rows="3" placeholder="Nhập địa chỉ">{{ old('address') }}</textarea>
                <div class="invalid-feedback d-none" data-err="address"></div>
              </div>

              <div class="col-md-6 select2CustomWidth">
                <label for="ac_status" class="form-label"><b>Trạng thái</b></label>
                <select id="ac_status" name="status" class="form-select setupSelect2" data-placeholder="Chọn trạng thái" required>
                  <option value="" disabled {{ old('status') ? '' : 'selected' }}>Chọn trạng thái</option>
                  <option value="ACTIVE" {{ old('status')==='ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                  <option value="BAN"    {{ old('status')==='BAN'    ? 'selected' : '' }}>BAN</option>
                </select>
                <div class="invalid-feedback d-none" data-err="status"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label"><b>Phân quyền</b></label>
                <div id="ac_roles_tokens" class="ac-tags" data-placeholder="Chọn vai trò"></div>
                <div id="ac_roles_suggest" class="ac-suggest mt-2"></div>
                <div id="ac_roles_inputs"></div>
                <div class="invalid-feedback d-none mt-1" data-err="role_ids"></div>
              </div>

              <input type="hidden" name="__update_action" id="__update_action" value="{{ old('__update_action') }}">
              <input type="hidden" id="__old_role_ids" value='@json(old("role_ids", []))'>
            </div>
          </div>
        </div>
      </div>

      @if ($errors->any())
         @vite('resources/js/pages/ecom-app-laravel_partials_ui_account_account-edit-modal.js')
      @endif

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-cancel-modal" data-bs-dismiss="modal">Huỷ</button>
        <button type="submit" class="btn btn-success btn-submit-modal">Lưu</button>
      </div>
    </form>
  </div>
</div>

@vite('resources/js/pages/ecom-app-laravel_partials_ui_account_account-edit-modal.js')