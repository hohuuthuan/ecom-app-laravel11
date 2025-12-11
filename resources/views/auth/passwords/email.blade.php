@extends('layouts.user')

@section('title','Quên mật khẩu')
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
            <h2 class="fw-bold mb-4">Quên mật khẩu</h2>
            <p>
              Nhập địa chỉ email đã dùng để đăng ký tài khoản.
              Chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.
            </p>
            <p>
              Sau khi đặt lại thành công, bạn có thể tiếp tục mua sắm
              và quản lý đơn hàng như bình thường.
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
            <h4 class="mb-3">Quên mật khẩu</h4>
            <p class="text-muted small mb-4">
              Nhập email đăng ký tài khoản, chúng tôi sẽ gửi link đặt lại mật khẩu cho bạn.
            </p>

            @if (session('status'))
              <div class="alert alert-success small py-2">
                {{ session('status') }}
              </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
              @csrf

              <div class="mb-3">
                <input
                  type="email"
                  name="email"
                  value="{{ old('email') }}"
                  class="form-control @error('email') is-invalid @enderror"
                  placeholder="Email đã đăng ký"
                  autofocus>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                Gửi link đặt lại mật khẩu
              </button>

              <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">
                Đã nhớ mật khẩu? Đăng nhập lại
              </a>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
