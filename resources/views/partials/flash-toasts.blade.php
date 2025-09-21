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
      <div class="toast toast-base fade toast--success mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-autohide="true" data-delay-ms="3500">
        <div class="toast-row">
          <i class="toast-icon bi bi-check-circle-fill"></i>
          <div class="toast-body">
            {{ $msg }}
            <small class="toast-countdown" data-countdown></small>
          </div>
          <button type="button" class="btn-close ms-auto js-toast-close" aria-label="Close"></button>
        </div>
        <div class="toast-progress"><div class="toast-progress__bar"></div></div>
      </div>
    @endforeach

    {{-- ERROR --}}
    @foreach($error as $msg)
      <div class="toast toast-base fade toast--error mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-autohide="true" data-delay-ms="6000">
        <div class="toast-row">
          <i class="toast-icon bi bi-exclamation-triangle-fill"></i>
          <div class="toast-body">
            {{ $msg }}
            <small class="toast-countdown" data-countdown></small>
          </div>
          <button type="button" class="btn-close ms-auto js-toast-close" aria-label="Close"></button>
        </div>
        <div class="toast-progress"><div class="toast-progress__bar"></div></div>
      </div>
    @endforeach

    {{-- INFO --}}
    @foreach($info as $msg)
      <div class="toast toast-base fade toast--info mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-autohide="true" data-delay-ms="4000">
        <div class="toast-row">
          <i class="toast-icon bi bi-info-circle-fill"></i>
          <div class="toast-body">
            {{ $msg }}
            <small class="toast-countdown" data-countdown></small>
          </div>
          <button type="button" class="btn-close ms-auto js-toast-close" aria-label="Close"></button>
        </div>
        <div class="toast-progress"><div class="toast-progress__bar"></div></div>
      </div>
    @endforeach

    {{-- WARNING --}}
    @foreach($warn as $msg)
      <div class="toast toast-base fade toast--warning mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-autohide="true" data-delay-ms="5000">
        <div class="toast-row">
          <i class="toast-icon bi bi-exclamation-circle-fill"></i>
          <div class="toast-body">
            {{ $msg }}
            <small class="toast-countdown" data-countdown></small>
          </div>
          <button type="button" class="btn-close ms-auto js-toast-close" aria-label="Close"></button>
        </div>
        <div class="toast-progress"><div class="toast-progress__bar"></div></div>
      </div>
    @endforeach
  </div>

  @vite('resources/js/pages/ecom-app-laravel_partials_flash-toasts.js')
@endif
