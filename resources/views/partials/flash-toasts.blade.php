@php
  $success = session('toast_success');
  $error   = session('toast_error');
  $info    = session('toast_info');
  $warn    = session('toast_warning');

  $asArray = fn($v) => is_array($v) ? $v : ($v ? [$v] : []);
  $success = $asArray($success);
  $error   = $asArray($error);
  $info    = $asArray($info);
  $warn    = $asArray($warn);
@endphp

@if($success || $error || $info || $warn)
  <div class="toast-container position-fixed top-0 end-0 p-3">
    {{-- SUCCESS --}}
    @foreach($success as $msg)
      <div class="toast fade toast-base toast--success mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-autohide="true" data-delay-ms="3500">
        <div class="toast-row">
          <i class="toast-icon bi bi-check-circle-fill"></i>
          <div class="toast-body">{{ $msg }} <small class="toast-countdown" data-countdown></small></div>
          <button type="button" class="btn-close ms-auto js-toast-close" aria-label="Close"></button>
        </div>
        <div class="toast-progress"><div class="toast-progress__bar"></div></div>
      </div>
    @endforeach

    {{-- ERROR --}}
    @foreach($error as $msg)
      <div class="toast fade toast-base toast--error mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-autohide="true" data-delay-ms="6000">
        <div class="toast-row">
          <i class="toast-icon bi bi-exclamation-triangle-fill"></i>
          <div class="toast-body">{{ $msg }} <small class="toast-countdown" data-countdown></small></div>
          <button type="button" class="btn-close ms-auto js-toast-close" aria-label="Close"></button>
        </div>
        <div class="toast-progress"><div class="toast-progress__bar"></div></div>
      </div>
    @endforeach

    {{-- INFO --}}
    @foreach($info as $msg)
      <div class="toast fade toast-base toast--info mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-autohide="true" data-delay-ms="4000">
        <div class="toast-row">
          <i class="toast-icon bi bi-info-circle-fill"></i>
          <div class="toast-body">{{ $msg }} <small class="toast-countdown" data-countdown></small></div>
          <button type="button" class="btn-close ms-auto js-toast-close" aria-label="Close"></button>
        </div>
        <div class="toast-progress"><div class="toast-progress__bar"></div></div>
      </div>
    @endforeach

    {{-- WARNING --}}
    @foreach($warn as $msg)
      <div class="toast fade toast-base toast--warning mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-autohide="true" data-delay-ms="5000">
        <div class="toast-row">
          <i class="toast-icon bi bi-exclamation-circle-fill"></i>
          <div class="toast-body">{{ $msg }} <small class="toast-countdown" data-countdown></small></div>
          <button type="button" class="btn-close ms-auto js-toast-close" aria-label="Close"></button>
        </div>
        <div class="toast-progress"><div class="toast-progress__bar"></div></div>
      </div>
    @endforeach
  </div>

  <script>
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
          if (remaining <= 0) clearInterval(timerId);
        };

        const beginAutoHide = () => {
          if (!shouldAutoHide) return;
          start = Date.now();
          timerId = setInterval(update, 100);
          hideTimeout = setTimeout(triggerLeave, delay);
        };

        const clearTimers = () => {
          if (timerId) { clearInterval(timerId); timerId = null; }
          if (hideTimeout) { clearTimeout(hideTimeout); hideTimeout = null; }
        };

        const triggerLeave = () => {
          if (leaving) return;
          leaving = true;
          clearTimers();
          el.classList.add('leaving');
          el.addEventListener('animationend', () => {
            try { t.dispose(); } catch(e) {}
            el.remove();
          }, { once: true });
        };

        t.show();
        requestAnimationFrame(() => {
          el.classList.add('is-in');
          beginAutoHide();
        });

        el.addEventListener('mouseenter', () => {
          clearTimers();
        });
        el.addEventListener('mouseleave', () => {
          if (!shouldAutoHide || leaving) return;
          start = Date.now() - (delay - remaining);
          timerId = setInterval(update, 100);
          hideTimeout = setTimeout(triggerLeave, remaining);
        });

        if (closeBtn) {
          closeBtn.addEventListener('click', (ev) => {
            ev.preventDefault();
            triggerLeave();
          });
        }
      });
    });
  </script>
@endif
