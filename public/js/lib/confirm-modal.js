/* global bootstrap */
(function () {
  'use strict';
  window.UIConfirm = async function (opts) {
    const {
      title = 'Xác nhận',
      message = 'Bạn có chắc muốn thực hiện thao tác này?',
      confirmText = 'Xác nhận',
      cancelText = 'Huỷ',
      size = 'md'
    } = opts || {};


    const modalEl = document.getElementById('uiConfirmModal');
    const titleEl = document.getElementById('uiConfirmTitle');
    const msgEl = document.getElementById('uiConfirmMessage');
    const okBtn = document.getElementById('uiConfirmOkBtn');
    const cancelBtn = document.getElementById('uiConfirmCancelBtn');
    const dialogEl = modalEl ? modalEl.querySelector('.modal-dialog') : null;
    if (!modalEl || !titleEl || !msgEl || !okBtn || !cancelBtn || !dialogEl) { return window.confirm(title + '\n\n' + message); }


    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    dialogEl.classList.remove('modal-sm', 'modal-md', 'modal-lg', 'modal-dialog-centered');
    dialogEl.classList.add('modal-' + size);
    modalEl.classList.add('modal-top');


    titleEl.textContent = title;
    msgEl.innerHTML = message;
    okBtn.textContent = confirmText;
    cancelBtn.textContent = cancelText;


    return new Promise((resolve) => {
      let decided = false;
      function onOk() { decided = true; cleanup(true); }
      function onHidden() { if (!decided) { cleanup(false); } }
      function cleanup(val) {
        okBtn.removeEventListener('click', onOk);
        modalEl.removeEventListener('hidden.bs.modal', onHidden);
        resolve(val);
      }
      okBtn.addEventListener('click', onOk, { once: true });
      modalEl.addEventListener('hidden.bs.modal', onHidden, { once: true });
      bsModal.show();
    });
  };
})();