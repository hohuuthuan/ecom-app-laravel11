<!-- Modal chỉnh sửa thông tin cá nhân -->
<div
  class="modal fade"
  id="editProfileModal"
  tabindex="-1"
  aria-labelledby="editProfileModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content profile-modal">
      <form method="POST" action="#">
        @csrf
        {{-- Sau này đổi action thành route update, ví dụ:
             action="{{ route('user.profile.update') }}" và thêm @method('PUT') --}}

        <div class="modal-header profile-modal-header">
          <h5 class="modal-title" id="editProfileModalLabel">
            Chỉnh sửa thông tin cá nhân
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>

        <div class="modal-body profile-modal-body">
          <div class="row g-3">
            {{-- Họ và tên --}}
            <div class="col-md-6">
              <label for="profileName" class="form-label profile-modal-label">
                Họ và tên <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                id="profileName"
                name="name"
                class="form-control profile-modal-input"
                value="{{ old('name', $user->name) }}"
                required>
            </div>

            {{-- Số điện thoại --}}
            <div class="col-md-6">
              <label for="profilePhone" class="form-label profile-modal-label">
                Số điện thoại
              </label>
              <input
                type="text"
                id="profilePhone"
                name="phone"
                class="form-control profile-modal-input"
                value="{{ old('phone', $user->phone) }}"
                placeholder="Nhập số điện thoại">
            </div>

            {{-- Email --}}
            <div class="col-md-6">
              <label for="profileEmail" class="form-label profile-modal-label">
                Email <span class="text-danger">*</span>
              </label>
              <input
                type="email"
                id="profileEmail"
                name="email"
                class="form-control profile-modal-input"
                value="{{ old('email', $user->email) }}"
                required>
            </div>

            {{-- Ngày sinh --}}
            <div class="col-md-3">
              <label for="profileBirthday" class="form-label profile-modal-label">
                Ngày sinh
              </label>
              <input
                type="date"
                id="profileBirthday"
                name="birthday"
                class="form-control profile-modal-input"
                value="{{ old('birthday', $user->birthday ?? '') }}">
            </div>

            {{-- Giới tính --}}
            <div class="col-md-3">
              <label for="profileGender" class="form-label profile-modal-label">
                Giới tính
              </label>
              @php
              $genderValue = old('gender', $user->gender ?? '');
              @endphp
              <select
                id="profileGender"
                name="gender"
                class="setupSelect2 profile-modal-select">
                <option value="">Chọn giới tính</option>
                <option value="male" @if($genderValue==='male' ) selected @endif>Nam</option>
                <option value="female" @if($genderValue==='female' ) selected @endif>Nữ</option>
                <option value="other" @if($genderValue==='other' ) selected @endif>Khác</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer profile-modal-footer">
          <button
            type="button"
            class="btn btn-outline-secondary rounded-pill px-4"
            data-bs-dismiss="modal">
            Hủy
          </button>
          <button
            type="submit"
            class="btn btn-primary rounded-pill px-4">
            Lưu thay đổi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>