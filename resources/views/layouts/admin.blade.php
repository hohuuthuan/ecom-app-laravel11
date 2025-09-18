{{-- resources/views/layouts/admin.blade.php --}}
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Admin')</title>
  @vite(['resources/css/app.css','resources/ts/app.ts'])
</head>
<body class="admin-layout">
  <div class="admin-shell">
    {{-- Sidebar trái --}}
    @include('partials.admin.sidebar')

    {{-- Khối phải --}}
    <div class="admin-main">
      {{-- Header --}}
      <header class="admin-header">
        <div class="admin-header-inner">
          <button id="btnSidebarToggle" type="button" class="icon-btn" aria-label="Thu/phóng menu">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 6h16M4 12h10M4 18h16"/>
            </svg>
          </button>

          <div class="flex-1"></div>

          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-secondary">Đăng xuất</button>
          </form>
        </div>
      </header>

      {{-- Content --}}
      <main class="admin-content">
        @yield('content')
      </main>
    </div>
  </div>

  {{-- Toasts --}}
  @include('partials.flash-toasts')
</body>
</html>
