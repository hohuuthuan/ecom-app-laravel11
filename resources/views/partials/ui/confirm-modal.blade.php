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
<script>
  (function() {
    const modalEl = document.getElementById('uiConfirmModal');
    if (!modalEl) return;
    const titleEl = modalEl.querySelector('#uiConfirmTitle');
    const msgEl = modalEl.querySelector('#uiConfirmMessage');
    const okBtn = modalEl.querySelector('#uiConfirmOkBtn');
    const cancelBtn = modalEl.querySelector('#uiConfirmCancelBtn');
    const dialogEl = modalEl.querySelector('.modal-dialog');

    let bsModal = new bootstrap.Modal(modalEl, {
      backdrop: true, // click ngoài sẽ đóng
      keyboard: true, // ESC để đóng
      focus: true
    });

    window.UIConfirm = function(options = {}) {
      const {
        title = 'Xác nhận',
          message = 'Bạn có chắc muốn thực hiện thao tác này?',
          confirmText = 'Xác nhận',
          cancelText = 'Huỷ',
          size = 'md'
      } = options;

      // set size
      // set size & position
      dialogEl.classList.remove('modal-sm', 'modal-md', 'modal-lg', 'modal-dialog-centered'); // nhớ remove luôn centered
      dialogEl.classList.add(`modal-${size}`);

      // (tùy chọn) gắn class lên modal để CSS định vị top
      modalEl.classList.add('modal-top');

      // set content
      titleEl.textContent = title;
      msgEl.innerHTML = message;
      okBtn.textContent = confirmText;
      cancelBtn.textContent = cancelText;

      return new Promise((resolve) => {
        let resolved = false;

        function cleanup(result) {
          if (resolved) return;
          resolved = true;
          // unbind all handlers
          okBtn.removeEventListener('click', onOk);
          modalEl.removeEventListener('hidden.bs.modal', onHidden);
          resolve(result);
        }

        function onOk() {
          cleanup(true);
          bsModal.hide();
        }

        function onHidden() {
          cleanup(false);
        }

        okBtn.addEventListener('click', onOk, {
          once: true
        });
        modalEl.addEventListener('hidden.bs.modal', onHidden, {
          once: true
        });

        bsModal.show();
      });
    };
  })();
</script>
@endpush