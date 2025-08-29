@extends('layouts.user.app')
@section('title','Home')

@section('content')
<div class="py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="p-4 bg-white border rounded text-center shadow-sm">
        @auth
          <h1 class="fs-3 fw-semibold mb-0">
            Xin chào, {{ auth()->user()->full_name }}
          </h1>
        @else
          <h1 class="fs-3 fw-semibold mb-2">Chào mừng đến với Ecom Perfume</h1>
          <p class="text-muted mb-4">
            Vui lòng đăng nhập hoặc đăng ký để tiếp tục.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('login.form') }}" class="btn btn-primary px-4">
              <i class="fa-solid fa-right-to-bracket"></i> Login
            </a>
            <a href="{{ route('register.form') }}" class="btn btn-outline-primary px-4">
              <i class="fa-solid fa-user-plus"></i> Register
            </a>
          </div>
        @endauth
      </div>
    </div>
  </div>
</div>
@endsection
