<!DOCTYPE html>
<html lang="vi">

<head>
  @include('admin.component.head')
  @stack('styles')
</head>

<body class="@hasSection('auth_page') auth-page @else admin-page @endif">

  <div id="wrapper">
    @hasSection('auth_page')
    <main class="auth-wrapper">
      @yield('content')
    </main>
    @else
    @include('admin.component.sidebar')

    <div id="page-wrapper" class="gray-bg">
      @include('admin.component.nav')

      @hasSection('breadcrumb')
      <div class="container-fluid">@yield('breadcrumb')</div>
      @endif

      <div class="container-fluid">
        @yield('content')
      </div>

      @include('admin.component.footer')
    </div>
    @endif
  </div>

  @include('admin.component.script')
  @stack('scripts')
</body>

</html>