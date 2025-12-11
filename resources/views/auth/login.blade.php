@extends('layouts.user')

@section('title','Đăng nhập')
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
            <h2 class="fw-bold mb-4">Chào mừng đến với Ecom Books</h2>
            <p>Khám phá kho sách trực tuyến phong phú: tiểu thuyết, học thuật, kỹ năng sống và nhiều hơn nữa.</p>
            <p>Đăng nhập để tiếp tục mua sắm, quản lý đơn hàng và nhận ưu đãi hấp dẫn.</p>
          </div>
          <div class="text-center mt-4">
            <a href="{{ route('home') }}">
              <img src="{{ asset('storage/logo/e-com-book-logo.png') }}"
                alt="Ecom Books" class="logo-left" width="200">
            </a>
          </div>
        </div>

        {{-- RIGHT --}}
        <div class="col-md-6 bg-white p-5 d-flex align-items-start">
          <div class="auth-right-inner w-100">
            <h4 class="mb-4">Đăng nhập</h4>

            <form method="POST" action="{{ route('login') }}">
              @csrf

              <div class="mb-3">
                <input type="email"
                  name="email"
                  value="{{ old('email') }}"
                  class="form-control @error('email') is-invalid @enderror"
                  placeholder="Email"
                  autofocus>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              {{-- Password --}}
              <div class="mb-3 has-toggle">
                <div class="toggle-wrap position-relative">
                  <input type="password"
                    name="password"
                    id="password"
                    class="form-control pe-5 @error('password') is-invalid @enderror"
                    placeholder="Mật khẩu"
                    autocomplete="current-password">

                  <button type="button"
                    class="toggle-password bg-transparent border-0 position-absolute end-0 top-50 translate-middle-y me-3"
                    aria-label="Hiện/ẩn mật khẩu">
                    <i class="icon-eye fa fa-eye-slash"></i>
                  </button>
                </div>

                @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="form-check mb-0">
                  <input type="checkbox"
                    class="form-check-input"
                    id="remember"
                    name="remember"
                    value="1" {{ old('remember') ? 'checked' : '' }}>
                  <label class="form-check-label" for="remember">Ghi nhớ tôi</label>
                </div>

                <a href="{{ route('password.request') }}" class="small">Quên mật khẩu?</a>
              </div>

              <button type="submit" class="btn btn-primary btn-login w-100 mb-3">Đăng nhập</button>

              <a class="btn btn-outline-secondary w-100 mb-2" href="{{ route('register.form') }}">
                Tạo tài khoản mới
              </a>

              <div class="text-center auth-divider my-3"><span>hoặc</span></div>
              <a
                href="{{ route('auth.google.redirect') }}"
                class="">
                <button type="button" class="btn btn-google w-100 d-flex align-items-center justify-content-center gap-2">
                  <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#EA4335" d="M24 9.5c3.9 0 7.4 1.5 10.1 3.9l6.8-6.8C36.7 2.4 30.7 0 24 0 14.6 0 6.4 5.4 2.5 13.2l7.9 6.1C12.2 13.3 17.6 9.5 24 9.5z" />
                    <path fill="#4285F4" d="M46.1 24.5c0-1.6-.1-2.8-.4-4.1H24v8.1h12.5c-.6 3.4-2.5 6.2-5.3 8.1l8.1 6.2c4.7-4.3 6.8-10.7 6.8-18.3z" />
                    <path fill="#FBBC05" d="M10.4 28.6c-.5-1.4-.8-2.8-.8-4.4s.3-3 .8-4.4l-7.9-6.1C.9 16.1 0 19.9 0 24.2s.9 8.1 2.5 10.5l7.9-6.1z" />
                    <path fill="#34A853" d="M24 48c6.5 0 12-2.1 16-5.8l-8.1-6.2c-2.2 1.5-5 2.4-7.9 2.4-6.4 0-11.8-3.8-13.6-9.2l-7.9 6.1C6.4 42.6 14.6 48 24 48z" />
                  </svg>
                  <span>Đăng nhập bằng Google</span>
                </button>
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