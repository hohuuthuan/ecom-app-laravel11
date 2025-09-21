// Generated from inline <script> blocks in: ecom-app-laravel/resources/views/partials/ui/confirm-modal.blade.php
// Each section preserves original order and approximate line ranges.

/* ===== BEGIN inline script #1 (lines 29-100) ===== */
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
/* ===== END inline script #1 ===== */
