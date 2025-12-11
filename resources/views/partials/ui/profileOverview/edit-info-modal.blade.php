<!-- Modal chỉnh sửa thông tin cá nhân -->
<div
  class="modal fade"
  id="editProfileModal"
  tabindex="-1"
  aria-labelledby="editProfileModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content profile-modal">
      <form method="POST" action="{{ route('user.profile.info.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
            <div class="col-12 text-center mb-3">
              <div class="d-flex flex-column align-items-center">
                <img id="avatarPreview" 
                     src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : asset('storage/avatars/base-avatar.jpg') }}" 
                     alt="Avatar"
                     data-original-src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : asset('storage/avatars/base-avatar.jpg') }}"
                     data-default-src="{{ asset('storage/avatars/base-avatar.jpg') }}"
                     class="rounded-circle mb-3" 
                     width="100" 
                     height="100" 
                     style="object-fit: cover;">
                <div>
                  <label for="avatarFile" class="btn btn-sm btn-primary">
                    <i class="bi bi-upload me-1"></i>Chọn ảnh đại diện
                  </label>
                  <input type="file" id="avatarFile" name="avatar" accept="image/*" class="d-none">
                  <small class="d-block mt-2 text-muted">Tối đa 2MB, định dạng: jpg, jpeg, png, webp</small>
                </div>
              </div>
            </div>

            {{-- Họ và tên --}}
            <div class="col-md-6">
              <label for="profileName" class="form-label profile-modal-label">
                Họ và tên <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                id="profileName"
                name="name"
                class="form-control profile-modal-input @error('name','profile') is-invalid @enderror"
                value="{{ old('name', $user->name) }}"
                data-original-value="{{ $user->name }}">
              @error('name','profile')
              <div class="invalid-feedback">
                {{ $message }}
              </div>
              @enderror
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
                class="form-control profile-modal-input @error('phone','profile') is-invalid @enderror"
                value="{{ old('phone', $user->phone) }}"
                data-original-value="{{ $user->phone }}"
                placeholder="Nhập số điện thoại">
              @error('phone','profile')
              <div class="invalid-feedback">
                {{ $message }}
              </div>
              @enderror
            </div>

            {{-- Email --}}
            <div class="col-md-6">
              <label for="profileEmail" class="form-label profile-modal-label">
                Email <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                id="profileEmail"
                name="email"
                class="form-control profile-modal-input @error('email','profile') is-invalid @enderror"
                value="{{ old('email', $user->email) }}"
                data-original-value="{{ $user->email }}">
              @error('email','profile')
              <div class="invalid-feedback">
                {{ $message }}
              </div>
              @enderror
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
                class="form-control profile-modal-input @error('birthday','profile') is-invalid @enderror"
                value="{{ old('birthday', $user->birthday ?? '') }}"
                data-original-value="{{ $user->birthday ?? '' }}">
              @error('birthday','profile')
              <div class="invalid-feedback">
                {{ $message }}
              </div>
              @enderror
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
                class="setupSelect2 profile-modal-select @error('gender','profile') is-invalid @enderror"
                data-original-value="{{ $user->gender ?? '' }}">
                <option value="">Chọn giới tính</option>
                <option value="male" @if($genderValue==='male' ) selected @endif>Nam</option>
                <option value="female" @if($genderValue==='female' ) selected @endif>Nữ</option>
                <option value="other" @if($genderValue==='other' ) selected @endif>Khác</option>
              </select>
              @error('gender','profile')
              <div class="invalid-feedback d-block">
                {{ $message }}
              </div>
              @enderror
            </div>

          </div>

          {{-- Lỗi chung (nếu có) --}}
          @error('general','profile')
          <div class="alert alert-danger mt-3 mb-0">
            {{ $message }}
          </div>
          @enderror
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