@extends('layouts.guest')
@section('title','Trang chủ')

@section('content')
  <div class="card-header">Trang chủ</div>
  <div class="card-body space-y-6">
    <div class="space-y-2">
      <h1 class="text-2xl font-semibold">Chào mừng đến Ecom</h1>
      <p class="text-gray-600 text-sm">Demo giao diện công khai. Đăng nhập để vào khu vực quản trị.</p>
    </div>

    @guest
      <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('login.form') }}" class="btn btn-primary sm:w-auto w-full">Đăng nhập</a>
        <a href="{{ route('register.form') }}" class="btn btn-secondary sm:w-auto w-full">Đăng ký</a>
      </div>
    @endguest

    @auth
      <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary sm:w-auto w-full">Vào bảng điều khiển</a>
        <form method="POST" action="{{ route('logout') }}" class="sm:w-auto w-full">
          @csrf
          <button type="submit" class="btn btn-secondary w-full">Đăng xuất</button>
        </form>
      </div>
      <p class="text-sm text-gray-500">Đăng nhập với quyền phù hợp để truy cập các trang quản trị.</p>
    @endauth
  </div>
@endsection
