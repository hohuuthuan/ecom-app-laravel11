@extends('layouts.user')

@section('title','Đăng ký')
@section('body_class','auth-page')

@section('layout')
<a href="{{ route('home') }}" class="back-home-btn">
  <i class="fa fa-home me-1"></i> Trang chủ
</a>
<div class="container animated fadeInDown">
  <div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-lg-8">
      <div class="row custom-form-login-shadow overflow-hidden">
        <div class="text-justify col-md-6 bg-light p-5 col-left d-flex flex-column justify-content-center gap-4">
          <div class="intro">
            <h2 class="fw-bold mb-3">Gia nhập Ecom Books</h2>
            <p>Tạo tài khoản để mua sắm nhanh chóng, lưu trữ giỏ hàng và nhận khuyến mãi đặc biệt.</p>
            <p>Sách chính hãng, giao hàng tận nơi, trải nghiệm tiện lợi cho mọi độc giả.</p>
          </div>
          <div class="text-center">
            <img src="{{ asset('storage/e-com-book-logo.png') }}"
              alt="Ecom Books"
              class="logo-left"
              width="200">
          </div>
        </div>

        <div class="col-md-6 bg-white p-5">
          <h4 class="mb-4">Đăng ký</h4>

          <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
              <input type="text"
                name="full_name"
                value="{{ old('full_name') }}"
                class="form-control @error('full_name') is-invalid @enderror"
                placeholder="Họ và tên"
                autocomplete="name">
              @error('full_name')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <input type="text"
                name="phone"
                value="{{ old('phone') }}"
                class="form-control @error('phone') is-invalid @enderror"
                placeholder="Số điện thoại"
                inputmode="tel" autocomplete="tel">
              @error('phone')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <input type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                placeholder="Email"
                autocomplete="email">
              @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <input type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                placeholder="Mật khẩu"
                autocomplete="new-password">
              @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <input type="password"
                name="password_confirmation"
                class="form-control"
                placeholder="Xác nhận mật khẩu"
                autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary btn-login w-100 mb-3">Đăng ký</button>

            <p class="text-center mb-0"><small>Đã có tài khoản?</small></p>
            <a class="btn btn-outline-secondary w-100 mt-2" href="{{ route('login.form') }}">
              Đăng nhập
            </a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection