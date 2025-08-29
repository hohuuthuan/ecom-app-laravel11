<!DOCTYPE html>
<html lang="vi">

<head>
  @include('layouts.user.component.head')
  @stack('styles')
</head>

<body class="@hasSection('auth_page') auth-page @else user-page @endif">
  <div id="wrapper">
    @hasSection('auth_page')
      <main class="auth-wrapper">
        @yield('content')
      </main>
    @else
      @include('layouts.user.component.nav')
      <div class="container-fluid">
        @hasSection('with_sidebar')
          <div class="row">
            <aside class="col-lg-3 mb-3 mb-lg-0">
              @include('layouts.user.component.sidebar')
            </aside>
            <main class="col-lg-9 py-3">
              @yield('content')
            </main>
          </div>
        @else
          <main class="container py-3">
            @yield('content')
          </main>
        @endif
      </div>

      @include('layouts.user.component.footer')
    @endif
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  @include('partials.flash-toasts')
  @stack('scripts')
</body>

</html>
