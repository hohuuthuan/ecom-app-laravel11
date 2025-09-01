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

  <script>
    (function() {
      const overlay = document.getElementById('pageLoading');
      const show = () => overlay.classList.remove('d-none');
      const hide = () => overlay.classList.add('d-none');

      document.addEventListener('click', function(e) {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');
        const newTab = a.target === '_blank' || e.metaKey || e.ctrlKey;
        const sameHash = href && href.startsWith('#');
        if (newTab || sameHash || a.dataset.noLoading !== undefined) return;
        show();
      });

      document.addEventListener('submit', function(e) {
        if (e.target.dataset.noLoading !== undefined) return;
        show();
      });

      window.addEventListener('pageshow', hide);
    })();
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      document.querySelectorAll("form.filter-form").forEach(form => {
        form.addEventListener("submit", function() {
          form.querySelectorAll("input, select, textarea").forEach(el => {
            if (!el.value) {
              el.removeAttribute("name");
            }
          });
        });
      });
    });
  </script>
</body>

</html>