@extends('layouts.base')
@section('title','Trang chủ')

@section('body')
  <div class="container py-5">
    <div class="text-center mb-4">
      <h1 class="h3 mb-2">E-Commerce</h1>
      <p class="text-muted mb-0">Trang chủ</p>
    </div>

    @auth
      <div class="d-flex justify-content-center gap-2">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
          Vào Dashboard
        </a>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-outline-secondary">
            Đăng xuất
          </button>
        </form>
      </div>
    @else
      <div class="d-flex justify-content-center gap-2">
        <a href="{{ route('login.form') }}" class="btn btn-primary">
          Đăng nhập
        </a>
        <a href="{{ route('register.form') }}" class="btn btn-outline-primary">
          Đăng ký
        </a>
      </div>
    @endauth
  </div>

  {{-- Toasts --}}
  @includeIf('partials.flash-toasts')
@endsection

@push('vendor_scripts')
  @vite('resources/js/vendor.ts')
@endpush
