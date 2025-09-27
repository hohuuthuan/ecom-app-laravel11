{{-- Reusable Confirm Modal (Bootstrap 5) --}}
<div class="modal fade" id="uiConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-top">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-semibold" id="uiConfirmTitle">Xác nhận</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <div class="modal-body pt-0">
        <div id="uiConfirmMessage" class="text-muted">
          Bạn có chắc muốn thực hiện thao tác này?
        </div>
      </div>

      <div class="modal-footer border-0 gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="uiConfirmCancelBtn">
          Huỷ
        </button>
        <button type="button" class="btn btn-primary" id="uiConfirmOkBtn">
          Xác nhận
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
@vite('resources/js/pages/ecom-app-laravel_partials_ui_confirm-modal.js')
@endpush