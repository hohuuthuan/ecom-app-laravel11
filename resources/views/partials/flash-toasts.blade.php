@if (session('success') || session('error') || session('warning') || session('info'))
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1060">
    @if (session('success'))
      <div class="toast align-items-center text-bg-success border-0" role="status"
           data-autohide="true" data-delay-ms="3000" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">{{ session('success') }}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Đóng"></button>
        </div>
      </div>
    @endif

    @if (session('error'))
      <div class="toast align-items-center text-bg-danger border-0" role="status"
           data-autohide="true" data-delay-ms="4000" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">{{ session('error') }}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Đóng"></button>
        </div>
      </div>
    @endif

    @if (session('warning'))
      <div class="toast align-items-center text-bg-warning border-0" role="status"
           data-autohide="true" data-delay-ms="3500" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">{{ session('warning') }}</div>
          <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Đóng"></button>
        </div>
      </div>
    @endif

    @if (session('info'))
      <div class="toast align-items-center text-bg-primary border-0" role="status"
           data-autohide="true" data-delay-ms="3000" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">{{ session('info') }}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Đóng"></button>
        </div>
      </div>
    @endif
  </div>
@endif
