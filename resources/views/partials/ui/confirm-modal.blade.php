{{-- Reusable Confirm Modal (Bootstrap 5) --}}
<div class="modal fade" id="uiConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
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
/**
 * UIConfirm(options) -> Promise<boolean>
 *  - title: string
 *  - message: string (có thể là HTML)
 *  - confirmText: string (mặc định: "Xác nhận")
 *  - cancelText: string (mặc định: "Huỷ")
 *  - size: 'sm' | 'md' | 'lg' (mặc định: 'md')
 *
 * Resolve(true) khi bấm Xác nhận.
 * Reject(false) hoặc Resolve(false) khi Huỷ / đóng / click ra ngoài.
 */
(function(){
  const modalEl   = document.getElementById('uiConfirmModal');
  if (!modalEl) return;
  const titleEl   = modalEl.querySelector('#uiConfirmTitle');
  const msgEl     = modalEl.querySelector('#uiConfirmMessage');
  const okBtn     = modalEl.querySelector('#uiConfirmOkBtn');
  const cancelBtn = modalEl.querySelector('#uiConfirmCancelBtn');
  const dialogEl  = modalEl.querySelector('.modal-dialog');

  let bsModal = new bootstrap.Modal(modalEl, {
    backdrop: true,      // click ngoài sẽ đóng
    keyboard: true,      // ESC để đóng
    focus: true
  });

  window.UIConfirm = function(options = {}){
    const {
      title = 'Xác nhận',
      message = 'Bạn có chắc muốn thực hiện thao tác này?',
      confirmText = 'Xác nhận',
      cancelText = 'Huỷ',
      size = 'md'
    } = options;

    // set size
    dialogEl.classList.remove('modal-sm','modal-md','modal-lg');
    dialogEl.classList.add('modal-dialog-centered', `modal-${size}`);

    // set content
    titleEl.textContent = title;
    msgEl.innerHTML = message;
    okBtn.textContent = confirmText;
    cancelBtn.textContent = cancelText;

    return new Promise((resolve) => {
      let resolved = false;

      function cleanup(result){
        if (resolved) return;
        resolved = true;
        // unbind all handlers
        okBtn.removeEventListener('click', onOk);
        modalEl.removeEventListener('hidden.bs.modal', onHidden);
        resolve(result);
      }

      function onOk(){
        cleanup(true);
        bsModal.hide();
      }

      function onHidden(){
        cleanup(false); // đóng bằng X, huỷ, ESC, click ra ngoài → false
      }

      okBtn.addEventListener('click', onOk, { once: true });
      modalEl.addEventListener('hidden.bs.modal', onHidden, { once: true });

      bsModal.show();
    });
  };
})();
</script>
@endpush
