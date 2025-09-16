/* global bootstrap */
(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', () => {
    const modalEl  = document.getElementById('uiConfirmModal');
    if (!modalEl) return;

    const titleEl  = document.getElementById('uiConfirmTitle');
    const msgEl    = document.getElementById('uiConfirmMessage');
    let   okBtn    = document.getElementById('uiConfirmOkBtn');
    const modal    = bootstrap.Modal.getOrCreateInstance(modalEl);

    function bindOkOnce(onOk, okText) {
      // Xoá mọi listener cũ bằng cách clone nút
      const newBtn = okBtn.cloneNode(true);
      okBtn.parentNode.replaceChild(newBtn, okBtn);
      okBtn = newBtn;
      if (okText) okBtn.textContent = okText;
      okBtn.addEventListener('click', () => { modal.hide(); if (onOk) onOk(); }, { once: true });
    }

    function openConfirm({ title, message, okText, onOk }) {
      if (titleEl && title)   titleEl.textContent = title;
      if (msgEl && message)   msgEl.textContent   = message;
      bindOkOnce(onOk, okText || 'Xác nhận');
      modal.show();
    }

    // Public API nếu cần dùng nơi khác
    window.UIConfirm = { open: openConfirm };

    // === Form delete đơn lẻ ===
    function attachFormConfirm(selector, { title, message, okText }) {
      document.querySelectorAll(selector).forEach(form => {
        let confirmed = false;

        function confirmAndSubmit(e) {
          if (confirmed) return;
          e.preventDefault();
          openConfirm({
            title, message, okText,
            onOk: () => { confirmed = true; form.submit(); }
          });
        }

        form.addEventListener('submit', confirmAndSubmit);
        form.querySelector('button[type="submit"]')?.addEventListener('click', confirmAndSubmit);
      });
    }

    attachFormConfirm('.catDeleteForm',   { title: 'Xoá danh mục', message: 'Bạn có chắc muốn xoá danh mục này?', okText: 'Xoá' });
    attachFormConfirm('.brandDeleteForm', { title: 'Xoá NSX',      message: 'Bạn có chắc muốn xoá nhà sản xuất này?', okText: 'Xoá' });

    // === Bulk delete ===
    function setupBulk(btnId, tableId, bulkFormId, bulkIdsId, label) {
      const btn = document.getElementById(btnId);
      if (!btn) return;

      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const table = document.getElementById(tableId);
        const ids = table ? Array.from(table.querySelectorAll('tbody input[type=checkbox]:checked')).map(x => x.value) : [];
        if (!ids.length) { alert(`Chọn ít nhất 1 ${label}.`); return; }

        openConfirm({
          title: `Xoá ${label} đã chọn`,
          message: `Bạn có chắc muốn xoá ${ids.length} ${label}?`,
          okText: 'Xoá',
          onOk: () => {
            const f = document.getElementById(bulkFormId);
            const c = document.getElementById(bulkIdsId);
            if (!f || !c) return;
            c.innerHTML = '';
            for (const id of ids) {
              const i = document.createElement('input');
              i.type = 'hidden'; i.name = 'ids[]'; i.value = id;
              c.appendChild(i);
            }
            f.submit();
          }
        });
      });
    }

    setupBulk('catBtnBulkDelete',   'categoryTable', 'catBulkForm',   'catBulkIds',   'danh mục');
    setupBulk('brandBtnBulkDelete', 'brandTable',    'brandBulkForm', 'brandBulkIds', 'NSX');
  });
})();
