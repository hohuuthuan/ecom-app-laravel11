@extends('layouts.base')

@section('title','Đăng nhập')
@section('body_class','auth-page')

@section('layout')
<div class="container animated fadeInDown">
  <div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-lg-8">
      <div class="row shadow rounded overflow-hidden">
        {{-- Cột giới thiệu --}}
        <div class="col-md-6 bg-light p-5">
          <h2 class="fw-bold mb-4">Chào mừng đến với Ecom Perfume</h2>
          <p>
            Khám phá thế giới hương thơm tinh tế tại <strong>Ecom Perfume</strong> với các dòng nước hoa cao cấp từ
            thương hiệu danh tiếng.
          </p>
          <p>Nước hoa là ngôn ngữ của sự tự tin. Chọn mùi hương mang dấu ấn riêng của bạn.</p>
        </div>

        {{-- Cột form --}}
        <div class="col-md-6 bg-white p-5">
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

            <div class="mb-3">
              <input type="password"
                     name="password"
                     class="form-control @error('password') is-invalid @enderror"
                     placeholder="Mật khẩu"
                     autocomplete="current-password">
              @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox"
                     class="form-check-input"
                     id="remember"
                     name="remember"
                     value="1" {{ old('remember') ? 'checked' : '' }}>
              <label class="form-check-label" for="remember">Ghi nhớ tôi</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Đăng nhập</button>

            @if (Route::has('password.request'))
              <div class="text-end mb-3">
                <a href="{{ route('password.request') }}" class="small">Quên mật khẩu?</a>
              </div>
            @endif

            <p class="text-center mb-0"><small>Chưa có tài khoản?</small></p>
            <a class="btn btn-outline-secondary w-100 mt-2" href="{{ route('register.form') }}">
              Tạo tài khoản mới
            </a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
