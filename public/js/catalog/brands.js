/* global bootstrap */
(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', () => {
    const form  = document.getElementById('uiBrandForm');
    const modal = document.getElementById('uiBrandModal');
    const img   = document.getElementById('brand_image_preview');
    const ph    = document.getElementById('brand_image_placeholder');
    const inp   = document.getElementById('brand_image');
    let blobUrl = null;

    function clearValidation() {
      if (!form) return;
      form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      form.querySelectorAll('.invalid-feedback').forEach(el => { el.classList.add('d-none'); el.classList.remove('d-block'); });
    }
    function showPh() { if (img) { img.classList.add('d-none'); img.removeAttribute('src'); } if (ph) ph.classList.remove('d-none'); }
    function showImg(url) { if (img) { img.src = url; img.classList.remove('d-none'); } if (ph) ph.classList.add('d-none'); }

    // Ảnh: preview + reset
    inp?.addEventListener('change', () => {
      if (!inp.files || !inp.files[0]) { showPh(); return; }
      const f = inp.files[0];
      if (!/^image\//.test(f.type)) { alert('Tập tin phải là ảnh'); inp.value = ''; showPh(); return; }
      if (f.size > 2 * 1024 * 1024) { alert('Ảnh tối đa 2MB'); inp.value = ''; showPh(); return; }
      if (blobUrl) URL.revokeObjectURL(blobUrl);
      blobUrl = URL.createObjectURL(f);
      showImg(blobUrl);
    });

    modal?.addEventListener('show.bs.modal', () => {
      if (modal.dataset.openedByError === '1') return; // giữ lỗi khi mở tự động do validate
      clearValidation(); // mở thủ công: luôn sạch lỗi cũ
    });

    modal?.addEventListener('hidden.bs.modal', () => {
      if (blobUrl) { URL.revokeObjectURL(blobUrl); blobUrl = null; }
      if (inp) inp.value = '';
    });

    // Nút Edit
    document.querySelectorAll('.btnBrandEdit').forEach(btn => {
      btn.addEventListener('click', function () {
        if (!form || !modal) return;
        clearValidation(); // dọn lỗi create còn bám

        const updateUrl   = this.dataset.updateUrl || '';
        const name        = this.dataset.name || '';
        const slug        = this.dataset.slug || '';
        const description = this.dataset.description || '';
        const status      = this.dataset.status || 'ACTIVE';
        const imageUrl    = this.dataset.image || '';

        form.action = updateUrl;
        form.querySelector('input[name="_method"]').value = 'PUT';
        form.querySelector('input[name="__mode"]').value  = 'update';
        form.querySelector('input[name="__form"]').value  = 'brand';
        const upd = form.querySelector('input[name="__update_action"]');
        if (upd) upd.value = updateUrl;

        form.querySelector('input[name="name"]').value = name;
        form.querySelector('input[name="slug"]').value = slug;
        form.querySelector('textarea[name="description"]').value = description;
        const sel = form.querySelector('select[name="status"]');
        if (sel) { sel.value = status; sel.dispatchEvent(new Event('change')); }

        if (imageUrl) showImg(imageUrl); else showPh();

        document.getElementById('brandModalTitle').textContent = 'Cập nhật NSX';
        bootstrap.Modal.getOrCreateInstance(modal).show();
      });
    });
  });
})();
