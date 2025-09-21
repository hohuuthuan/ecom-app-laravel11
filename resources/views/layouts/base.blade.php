<!DOCTYPE html>
<html lang="vi">

<head>
  @yield('head')
</head>

<body class="@yield('body_class','app-page')">
  <div id="wrapper">@yield('layout')</div>

  @include('partials.loading-overlay')
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @include('partials.flash-toasts')
  @stack('scripts')

  @vite('resources/js/pages/ecom-app-laravel_layouts_base.js')
</body>

</html>