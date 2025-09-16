/* global bootstrap */
(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', () => {
    const st = document.getElementById('__formState');
    if (!st) return;
    if (Number(st.dataset.hasErrors) !== 1) return;

    const which = st.dataset.which;              // 'brand' | 'category'
    const mode  = st.dataset.mode || 'create';   // 'create' | 'update'
    const map   = { brand: 'uiBrandModal', category: 'uiCategoryModal' };
    const id    = map[which];
    if (!id) return;

    const modalEl = document.getElementById(id);
    if (!modalEl) return;
    const modal   = bootstrap.Modal.getOrCreateInstance(modalEl);
    const form    = modalEl.querySelector('form');

    // Nếu là UPDATE: ép form sang PUT + action cập nhật
    if (form && mode === 'update') {
      const methodEl = form.querySelector('input[name="_method"]');
      const modeEl   = form.querySelector('input[name="__mode"]');
      const whichEl  = form.querySelector('input[name="__form"]');
      const actEl    = form.querySelector('input[name="__update_action"]');

      if (modeEl)  modeEl.value = 'update';
      if (whichEl) whichEl.value = which;
      if (methodEl) methodEl.value = 'PUT';
      if (actEl && actEl.value) form.action = actEl.value;

      // Tiêu đề UPDATE
      const ttlId = which === 'brand' ? 'brandModalTitle' : 'catModalTitle';
      const ttl   = document.getElementById(ttlId);
      if (ttl) ttl.textContent = which === 'brand' ? 'Cập nhật NSX' : 'Cập nhật danh mục';
    }

    // Nếu là CREATE: đặt tiêu đề tạo
    if (mode === 'create') {
      const ttlId = which === 'brand' ? 'brandModalTitle' : 'catModalTitle';
      const ttl   = document.getElementById(ttlId);
      if (ttl) ttl.textContent = which === 'brand' ? 'Thêm NSX' : 'Thêm danh mục';
    }

    // Đánh dấu mở do lỗi để không tự xoá lỗi khi show
    modalEl.dataset.openedByError = '1';

    // Khi đóng: xoá dấu lỗi để lần sau mở create/edit không dính nữa
    modalEl.addEventListener('hidden.bs.modal', () => {
      if (form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => { el.classList.add('d-none'); el.classList.remove('d-block'); });
      }
      delete modalEl.dataset.openedByError;
      st.dataset.hasErrors = '0'; // tắt auto-open cho lần sau
    }, { once: true });

    modal.show();
  });
})();
