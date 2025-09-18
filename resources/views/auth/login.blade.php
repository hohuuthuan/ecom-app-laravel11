@extends('layouts.guest')
@section('title','Đăng nhập')

@section('content')
<div class="auth-center">
  <div class="auth-card">
    <aside class="auth-hero">
      <div>
        <h2>Xin chào trở lại</h2>
        <p>Quản trị sản phẩm, danh mục, khách hàng trong một nơi.</p>
      </div>
      <ul class="space-y-2 text-sm">
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-white/80"></span><span>Hiệu năng nhanh với Vite</span></li>
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-white/80"></span><span>Giao diện Tailwind gọn gàng</span></li>
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-white/80"></span><span>Bảo mật theo chuẩn Laravel</span></li>
      </ul>
    </aside>

    <section class="auth-form">
      <h1 class="auth-title">Đăng nhập</h1>

      @if (session('status'))
        <div class="mb-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
          <label for="email" class="form-label">Email</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus class="form-control" />
          @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="form-label">Mật khẩu</label>
          </div>
          <div class="input-icon-wrap">
            <input id="password" name="password" type="password" autocomplete="current-password" class="form-control input-icon-pad" />
            <button type="button" class="input-icon-btn" data-toggle-password data-target="password" aria-label="Hiện/ẩn mật khẩu" aria-pressed="false">
              {{-- eye on --}}
              <svg data-eye class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><circle cx="12" cy="12" r="3" stroke-width="2" /></svg>
              {{-- eye off --}}
              <svg data-eye-off class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.04 10.04 0 012.642-4.205M6.1 6.1A9.99 9.99 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.043 5.122M3 3l18 18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 00-3-3"/></svg>
            </button>
          </div>
          @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
          <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input value="1" type="checkbox" name="remember" class="rounded border-gray-300 text-sky-600 focus:ring-sky-600" />
            Ghi nhớ tôi
          </label>
          <a href="{{ route('register.form') }}" class="text-sm text-sky-700 hover:underline">Chưa có tài khoản?</a>
        </div>

        <button type="submit" class="btn btn-primary w-full">Đăng nhập</button>
      </form>
    </section>
  </div>
</div>
@endsection