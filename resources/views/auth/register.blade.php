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
          <h2 class="fw-bold mb-4">Trở thành thành viên của Ecom Perfume</h2>
          <p>Đăng ký để nhận ưu đãi, khuyến mãi độc quyền và gợi ý mùi hương phù hợp.</p>
          <p>Sản phẩm chính hãng, giao nhanh, trải nghiệm tuyệt vời.</p>
        </div>

        {{-- Cột form --}}
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

            <button type="submit" class="btn btn-primary w-100 mb-3">Đăng ký</button>

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