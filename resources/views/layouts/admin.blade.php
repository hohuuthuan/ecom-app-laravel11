<!doctype html>
<html lang="vi">
  <head>
    @include('partials.head')
    <title>@yield('title','Bảng điều khiển')</title>
  </head>
  <body class="admin-layout" data-sidebar="expanded">
    <header class="admin-topbar">
      <div class="admin-topbar-inner">
        <div class="flex items-center gap-3">
          <button id="sidebarToggle" class="lg:hidden btn btn-secondary" type="button">
            <i class="fa-solid fa-bars"></i>
          </button>
          <span class="font-semibold">Ecom Admin</span>
        </div>
        <div class="flex items-center gap-3">
          <a href="#" class="nav-link"><i class="fa-regular fa-bell"></i></a>
          <a href="#" class="nav-link"><i class="fa-regular fa-user"></i></a>
        </div>
      </div>
    </header>

    <main class="admin-shell">
      <aside class="admin-sidebar">
        <div class="admin-sidebar-card">
          <nav class="space-y-1">
            <a class="nav-link {{ request()->is('admin') ? 'active' : '' }}" href="{{ url('/admin') }}">
              <i class="fa-solid fa-gauge-high"></i><span>Bảng điều khiển</span>
            </a>
            <a class="nav-link" href="#">
              <i class="fa-solid fa-box"></i><span>Sản phẩm</span>
            </a>
            <a class="nav-link" href="#">
              <i class="fa-solid fa-users"></i><span>Khách hàng</span>
            </a>
          </nav>
        </div>
      </aside>

      <section class="admin-content">
        @include('partials.flash-toasts')
        @yield('content')
      </section>
    </main>

    @include('partials.ui.confirm-modal')
    @include('partials.script')
  </body>
</html>
