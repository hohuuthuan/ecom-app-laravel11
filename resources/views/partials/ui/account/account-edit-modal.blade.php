{{-- Modal: Chỉnh sửa tài khoản --}}
<div class="modal fade" id="uiAccountEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content"
          id="uiAccountEditForm"
          method="POST"
          enctype="multipart/form-data"
          action="{{ old('__update_action') }}">
      @csrf
      @method('PUT')

      @php $hasErr = $errors->any(); @endphp

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
            <div class="invalid-feedback {{ $errors->has('avatar') ? 'd-block' : 'd-none' }}" data-err="avatar">
              {{ $errors->first('avatar') }}
            </div>
          </div>

          {{-- 10 cột: Thông tin --}}
          <div class="col-lg-10">
            <div class="row g-3">
              <div class="col-md-4">
                <label for="ac_full_name" class="form-label"><b>Họ tên</b> <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control {{ $errors->has('full_name') ? 'is-invalid' : '' }}"
                       id="ac_full_name" name="full_name"
                       value="{{ old('full_name') }}" placeholder="Nhập họ tên">
                <div class="invalid-feedback {{ $errors->has('full_name') ? 'd-block' : 'd-none' }}" data-err="full_name">
                  {{ $errors->first('full_name') }}
                </div>
              </div>

              <div class="col-md-4">
                <label for="ac_email" class="form-label"><b>Email</b> <span class="text-danger">*</span></label>
                <input type="email"
                       class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       id="ac_email" name="email"
                       value="{{ old('email') }}" placeholder="Nhập email">
                <div class="invalid-feedback {{ $errors->has('email') ? 'd-block' : 'd-none' }}" data-err="email">
                  {{ $errors->first('email') }}
                </div>
              </div>

              <div class="col-md-4">
                <label for="ac_phone" class="form-label"><b>Số điện thoại</b> <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                       id="ac_phone" name="phone"
                       value="{{ old('phone') }}" placeholder="Nhập số điện thoại">
                <div class="invalid-feedback {{ $errors->has('phone') ? 'd-block' : 'd-none' }}" data-err="phone">
                  {{ $errors->first('phone') }}
                </div>
              </div>

              <div class="col-12">
                <label for="ac_address" class="form-label"><b>Địa chỉ</b></label>
                <textarea id="ac_address" name="address"
                          class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                          rows="3" placeholder="Nhập địa chỉ">{{ old('address') }}</textarea>
                <div class="invalid-feedback {{ $errors->has('address') ? 'd-block' : 'd-none' }}" data-err="address">
                  {{ $errors->first('address') }}
                </div>
              </div>

              <div class="col-md-6 select2CustomWidth">
                <label for="ac_status" class="form-label"><b>Trạng thái</b></label>
                <select id="ac_status" name="status"
                        class="form-select setupSelect2 {{ $errors->has('status') ? 'is-invalid' : '' }}"
                        data-placeholder="Chọn trạng thái">
                  <option value="" disabled {{ old('status') ? '' : 'selected' }}>Chọn trạng thái</option>
                  <option value="ACTIVE" {{ old('status')==='ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                  <option value="BAN"    {{ old('status')==='BAN'    ? 'selected' : '' }}>BAN</option>
                </select>
                <div class="invalid-feedback {{ $errors->has('status') ? 'd-block' : 'd-none' }}" data-err="status">
                  {{ $errors->first('status') }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label"><b>Phân quyền</b></label>
                <div id="ac_roles_tokens" class="ac-tags" data-placeholder="Chọn vai trò"></div>
                <div id="ac_roles_suggest" class="ac-suggest mt-2"></div>
                <div id="ac_roles_inputs"></div>
                <div class="invalid-feedback {{ $errors->has('role_ids') ? 'd-block' : 'd-none' }} mt-1" data-err="role_ids">
                  {{ $errors->first('role_ids') }}
                </div>
              </div>

              {{-- Giữ URL update + role_ids để JS khôi phục --}}
              <input type="hidden" name="__update_action" id="__update_action" value="{{ old('__update_action') }}">
              <input type="hidden" id="__old_role_ids" value='@json(old("role_ids", []))'>
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

{{-- State để JS tự mở lại modal khi có lỗi --}}
<div id="__accountFormState" data-has-errors="{{ $hasErr ? 1 : 0 }}" style="display:none"></div>
