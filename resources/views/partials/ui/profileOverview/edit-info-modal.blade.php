<div
  class="modal fade"
  id="editProfileModal"
  tabindex="-1"
  aria-labelledby="editProfileModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form method="POST" action="#">
        @csrf
        {{-- Sau này đổi action thành route update thật, ví dụ:
             action="{{ route('user.profile.update') }}" và thêm @method('PUT') --}}
        <div class="modal-header">
          <h2 class="modal-title" id="editProfileModalLabel">
            Chỉnh sửa thông tin cá nhân
          </h2>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="profile-info-grid profile-info-grid-edit">
            <div>
              <div class="profile-info-item-label">Họ và tên</div>
              <div class="profile-info-item-value">
                <input
                  type="text"
                  id="profileName"
                  name="name"
                  class="form-control"
                  value="{{ old('name', $user->name) }}"
                  required>
              </div>
            </div>

            <div>
              <div class="profile-info-item-label">Số điện thoại</div>
              <div class="profile-info-item-value">
                <input
                  type="text"
                  id="profilePhone"
                  name="phone"
                  class="form-control"
                  value="{{ old('phone', $user->phone) }}"
                  placeholder="Nhập số điện thoại">
              </div>
            </div>

            <div>
              <div class="profile-info-item-label">Email</div>
              <div class="profile-info-item-value">
                <input
                  type="email"
                  id="profileEmail"
                  name="email"
                  class="form-control"
                  value="{{ old('email', $user->email) }}"
                  required>
              </div>
            </div>

            <div>
              <div class="profile-info-item-label">Ngày sinh</div>
              <div class="profile-info-item-value">
                <input
                  type="date"
                  id="profileBirthday"
                  name="birthday"
                  class="form-control"
                  value="{{ old('birthday', $user->birthday ?? '') }}">
              </div>
            </div>

            <div>
              <div class="profile-info-item-label">Giới tính</div>
              <div class="profile-info-item-value">
                @php
                $genderValue = old('gender', $user->gender ?? '');
                @endphp
                <select
                  id="profileGender"
                  name="gender"
                  class="form-select">
                  <option value="">Chọn giới tính</option>
                  <option value="male" @if($genderValue==='male' ) selected @endif>Nam</option>
                  <option value="female" @if($genderValue==='female' ) selected @endif>Nữ</option>
                  <option value="other" @if($genderValue==='other' ) selected @endif>Khác</option>
                </select>
              </div>
            </div>
          </div>
        </div>


        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-outline-secondary"
            data-bs-dismiss="modal">
            Hủy
          </button>
          <button
            type="submit"
            class="btn btn-primary">
            Lưu thay đổi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>