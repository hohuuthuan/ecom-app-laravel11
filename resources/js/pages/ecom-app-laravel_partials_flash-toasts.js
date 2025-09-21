// Generated from inline <script> blocks in: ecom-app-laravel/resources/views/partials/flash-toasts.blade.php
// Each section preserves original order and approximate line ranges.

/* ===== BEGIN inline script #1 (lines 85-169) ===== */
document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.toast.toast-base').forEach(function (el) {
        const delay = parseInt(el.getAttribute('data-delay-ms') || '5000', 10);
        const shouldAutoHide = el.getAttribute('data-autohide') === 'true';
        const bar = el.querySelector('.toast-progress__bar');
        const cd  = el.querySelector('[data-countdown]');
        const closeBtn = el.querySelector('.js-toast-close');

        const t = new bootstrap.Toast(el, { autohide: false });

        let remaining = delay;
        let start = null;
        let timerId = null;
        let hideTimeout = null;
        let leaving = false;

        const update = () => {
          const elapsed = Date.now() - start;
          remaining = Math.max(0, delay - elapsed);
          const pct = (remaining / delay) * 100;
          if (bar) bar.style.width = pct + '%';
          if (cd)  cd.textContent = Math.ceil(remaining / 1000) + 's';
          if (remaining <= 0 && timerId) { clearInterval(timerId); timerId = null; }
        };

        const clearTimers = () => {
          if (timerId)     { clearInterval(timerId); timerId = null; }
          if (hideTimeout) { clearTimeout(hideTimeout); hideTimeout = null; }
        };

        const triggerLeave = () => {
          if (leaving) return;
          leaving = true;
          clearTimers();
          el.classList.add('leaving');
          el.addEventListener('animationend', () => {
            try { t.dispose(); } catch (e) {}
            el.remove();
          }, { once: true });
        };

        const beginAutoHide = () => {
          if (!shouldAutoHide) return;
          start = Date.now();
          timerId = setInterval(update, 100);
          hideTimeout = setTimeout(triggerLeave, delay);
        };

        t.show();
        requestAnimationFrame(() => {
          el.classList.add('is-in');
          beginAutoHide();
        });

        // Pause timer khi hover
        el.addEventListener('mouseenter', () => {
          clearTimers();
        });
        el.addEventListener('mouseleave', () => {
          if (!shouldAutoHide || leaving) return;
          // tiếp tục với thời gian còn lại
          start = Date.now() - (delay - remaining);
          timerId = setInterval(update, 100);
          hideTimeout = setTimeout(triggerLeave, remaining);
        });

        // Click anywhere to dismiss (trừ phần tử tương tác)
        const isInteractive = (target) => target.closest('a, button, .btn, input, textarea, select, label');
        el.addEventListener('click', (e) => {
          if (leaving) return;
          if (isInteractive(e.target)) return;
          triggerLeave();
        });

        // Nút close
        if (closeBtn) {
          closeBtn.addEventListener('click', (ev) => {
            ev.preventDefault();
            triggerLeave();
          });
        }
      });
    });
/* ===== END inline script #1 ===== */
