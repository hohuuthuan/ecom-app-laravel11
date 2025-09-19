<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <title>@yield('title', 'Admin Panel')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script>
    (function() {
      var s = localStorage.getItem('sb_state_v1');
      if (s === 'collapsed' || s === 'expanded') {
        document.documentElement.setAttribute('data-sb', s);
      }
    })();
  </script>
  @vite(['resources/css/admin.css','resources/ts/app.ts'])
</head>

<body class="min-h-dvh bg-gray-50 text-gray-900" data-sb="{{ session('sb_state', 'expanded') }}">
  <div class="layout">
    @include('partials.admin.sidebar')
    <main class="main">
      <header class="p-2 border-b border-gray-200 bg-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <button id="toggleSidebar" type="button" aria-label="Toggle sidebar"
              class="rounded-md bg-gray-100 hover:bg-gray-200"
              aria-pressed="false">
              <i class="bi bi-layout-sidebar-inset icon-collapsed align-middle" data-icon="collapsed"></i>
            </button>
            <a href="{{ route('home') }}">Trang chủ</a>
          </div>
          <div>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">
                <i class="bi bi-box-arrow-right text-base align-middle" aria-hidden="true"></i>
                <span>Đăng xuất</span>
              </button>
            </form>
          </div>

        </div>
      </header>

      <section class="p-4">
        @yield('breadcrumb')
        @yield('content')
      </section>
    </main>
  </div>
  @include('partials.flash-toasts')
</body>

</html>