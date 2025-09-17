@extends('layouts.guest')
@section('title','Đăng ký')

@section('content')
<div class="auth-center">
  <div class="auth-card">
    <aside class="auth-hero">
      <div>
        <h2>Tạo tài khoản</h2>
        <p>Tham gia hệ thống để quản trị cửa hàng của bạn.</p>
      </div>
      <ul class="space-y-2 text-sm">
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-white/80"></span><span>Nhanh, gọn, dễ dùng</span></li>
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-white/80"></span><span>Bảo mật hiện đại</span></li>
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-white/80"></span><span>Hỗ trợ mở rộng</span></li>
      </ul>
    </aside>

    <section class="auth-form">
      <h1 class="auth-title">Đăng ký</h1>

      <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
          <label for="name" class="form-label">Họ tên</label>
          <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" class="form-control" />
          @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="phone" class="form-label">Số điện thoại</label>
          <input id="phone" name="phone" type="text" value="{{ old('phone') }}" autocomplete="phone" class="form-control" />
          @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="email" class="form-label">Email</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="form-control" />
          @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="password_reg" class="form-label">Mật khẩu</label>
          <div class="input-icon-wrap">
            <input id="password_reg" name="password" type="password" autocomplete="new-password" class="form-control input-icon-pad" />
            <button type="button" class="input-icon-btn" data-toggle-password data-target="password_reg" aria-label="Hiện/ẩn mật khẩu" aria-pressed="false">
              <svg data-eye class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><circle cx="12" cy="12" r="3" stroke-width="2" /></svg>
              <svg data-eye-off class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.04 10.04 0 012.642-4.205M6.1 6.1A9.99 9.99 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.043 5.122M3 3l18 18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 00-3-3"/></svg>
            </button>
          </div>
          @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="password_confirmation" class="form-label">Nhập lại mật khẩu</label>
          <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="form-control" />
        </div>

        <div class="flex items-center justify-between">
          <a href="{{ route('login.form') }}" class="text-sm text-sky-700 hover:underline">Đã có tài khoản? Đăng nhập</a>
        </div>

        <button type="submit" class="btn btn-primary w-full">Tạo tài khoản</button>
      </form>
    </section>
  </div>
</div>
@endsection