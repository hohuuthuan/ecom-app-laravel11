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

  <script src="{{ asset('js/layout/page-loading.js') }}"></script>
  <script src="{{ asset('js/forms/filter-clean.js') }}"></script>
</body>

</html>