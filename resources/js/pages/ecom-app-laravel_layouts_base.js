// Generated from inline <script> blocks in: ecom-app-laravel/resources/views/layouts/base.blade.php
// Each section preserves original order and approximate line ranges.

/* ===== BEGIN inline script #2 (lines 16-39) ===== */
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
/* ===== END inline script #2 ===== */

/* ===== BEGIN inline script #3 (lines 41-53) ===== */
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
/* ===== END inline script #3 ===== */
