@extends('layouts.user')

@section('title','Đặt lại mật khẩu')
@section('body_class','auth-page')

@section('layout')
<a href="{{ route('home') }}" class="back-home-btn">
  <i class="fa fa-home me-1"></i> Trang chủ
</a>

<div class="container animated fadeInDown">
  <div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-lg-8">
      <div class="row custom-form-login-shadow overflow-hidden align-items-stretch">
        {{-- LEFT --}}
        <div class="text-justify col-md-6 bg-light p-5 d-flex flex-column justify-content-between">
          <div>
            <h2 class="fw-bold mb-4">Đặt lại mật khẩu</h2>
            <p>
              Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản Ecom Books.
              Hãy tạo một mật khẩu mới an toàn và dễ nhớ đối với bạn.
            </p>
            <p>
              Sau khi cập nhật thành công, bạn sẽ có thể đăng nhập và tiếp tục
              quản lý đơn hàng, thông tin cá nhân như bình thường.
            </p>
          </div>

          <div class="text-center mt-4">
            <a href="{{ route('home') }}">
              <img
                src="{{ asset('storage/logo/e-com-book-logo.png') }}"
                alt="Ecom Books"
                class="logo-left"
                width="200">
            </a>
          </div>
        </div>

        {{-- RIGHT --}}
        <div class="col-md-6 bg-white p-5 d-flex align-items-start">
          <div class="auth-right-inner w-100">
            <h4 class="mb-3">Đặt lại mật khẩu</h4>

            <form method="POST" action="{{ route('password.update') }}">
              @csrf

              <input type="hidden" name="token" value="{{ $token }}">
              <input type="hidden" name="email" value="{{ $email }}">

              {{-- Mật khẩu mới --}}
              <div class="mb-3 has-toggle">
                <label for="password" class="form-label small mb-1">Mật khẩu mới</label>
                <div class="toggle-wrap position-relative">
                  <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control pe-5 @error('password') is-invalid @enderror"
                    placeholder="Nhập mật khẩu mới"
                    required>

                  <button
                    type="button"
                    class="toggle-password bg-transparent border-0 position-absolute end-0 top-50 translate-middle-y me-3"
                    aria-label="Hiện/ẩn mật khẩu">
                    <i class="icon-eye fa fa-eye-slash"></i>
                  </button>
                </div>
                @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              {{-- Nhập lại mật khẩu mới --}}
              <div class="mb-3 has-toggle">
                <label for="password_confirmation" class="form-label small mb-1">
                  Nhập lại mật khẩu mới
                </label>
                <div class="toggle-wrap position-relative">
                  <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control pe-5"
                    placeholder="Nhập lại mật khẩu mới"
                    required>

                  <button
                    type="button"
                    class="toggle-password bg-transparent border-0 position-absolute end-0 top-50 translate-middle-y me-3"
                    aria-label="Hiện/ẩn mật khẩu">
                    <i class="icon-eye fa fa-eye-slash"></i>
                  </button>
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                Cập nhật mật khẩu
              </button>

              <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">
                Quay lại trang đăng nhập
              </a>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/pages/ecom-app-laravel_auth_login.js')
@endpush
