/* global bootstrap */
(function () {
  'use strict';
  function $(sel, root) { return (root || document).querySelector(sel); }
  function $all(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }
  function stripHtml(html) { const d = document.createElement('div'); d.innerHTML = html; return d.textContent || d.innerText || ''; }
  async function confirmDialog(opts) {
    if (typeof window.UIConfirm === 'function') return await window.UIConfirm(opts);
    const title = (opts && opts.title) || 'Xác nhận'; const msg = (opts && opts.message) || 'Bạn có chắc không?';
    return window.confirm(title + '\n\n' + stripHtml(msg));
  }
  function statusText(v) { v = String(v || '').toUpperCase(); return v === 'ACTIVE' ? 'Kích hoạt' : (v === 'INACTIVE' ? 'Khoá' : '—'); }
  function toServerStatus(v) { v = String(v || '').toUpperCase().trim(); return v; } // cat/brand đã dùng ACTIVE/INACTIVE

  function initBulkSelection(tableSel, masterSel) {
    const table = $(tableSel); const master = $(masterSel);
    function getRowCheckboxes() { return table ? $all('tbody .row-checkbox', table) : []; }
    function markRow(cb) { const tr = cb?.closest('tr'); if (!tr) return; tr.classList.toggle('row-checked', !!cb.checked); tr.classList.remove('table-active'); }
    function refreshMaster() {
      if (!master) return; const cbs = getRowCheckboxes(); const total = cbs.length; const checked = cbs.filter(x => x.checked).length;
      master.indeterminate = false; master.checked = total > 0 && checked === total;
    }
    if (table) {
      table.addEventListener('change', e => {
        const t = e.target; if (!t.classList?.contains('row-checkbox')) return; markRow(t); refreshMaster();
      });
      table.addEventListener('click', e => {
        const td = e.target.closest('td'); if (!td) return; if (td.cellIndex !== 0) return; if (e.target.tagName === 'INPUT') return;
        const cb = td.querySelector('.row-checkbox'); if (!cb) return; cb.checked = !cb.checked; markRow(cb); refreshMaster();
      });
      getRowCheckboxes().forEach(markRow); refreshMaster();
    }
    if (master) {
      master.addEventListener('change', () => {
        const cbs = getRowCheckboxes(); for (const cb of cbs) { cb.checked = master.checked; markRow(cb); }
        master.indeterminate = false; refreshMaster();
      });
    }
  }

  function initBulkSubmit({ tableSel, btnOpenSel, statusSel, formSel, statusInputSel, idsContainerSel }) {
    const table = $(tableSel); const btnOpen = $(btnOpenSel); const select = $(statusSel);
    const bulkForm = $(formSel); const bulkStatusInput = $(statusInputSel); const bulkIdsContainer = $(idsContainerSel);
    function getCheckedIds() { return table ? $all('tbody .row-checkbox:checked', table).map(x => x.value) : []; }
    btnOpen?.addEventListener('click', async () => {
      const ids = getCheckedIds(); const raw = select && select.value ? select.value : ''; const val = toServerStatus(raw);
      if (!ids.length) { await confirmDialog({ title: 'Thiếu lựa chọn', message: 'Vui lòng chọn ít nhất <b>1</b> mục.' }); return; }
      if (val !== 'ACTIVE' && val !== 'INACTIVE') { await confirmDialog({ title: 'Chưa chọn trạng thái', message: 'Vui lòng chọn trạng thái đích.' }); return; }
      const ok = await confirmDialog({ title: 'Xác nhận cập nhật', message: `Bạn sắp cập nhật <b>${ids.length}</b> mục.<br>Trạng thái: <span class="badge ${val === 'ACTIVE' ? 'bg-success' : 'bg-danger'}">${statusText(val)}</span>`, confirmText: 'Xác nhận', cancelText: 'Huỷ' }); if (!ok) return;
      if (!bulkForm) return;
      bulkStatusInput.value = val; bulkIdsContainer.innerHTML = '';
      for (const id of ids) { const i = document.createElement('input'); i.type = 'hidden'; i.name = 'ids[]'; i.value = id; bulkIdsContainer.appendChild(i); }
      bulkForm.submit();
    });
  }


  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('uiCategoryForm');
    const modal = document.getElementById('uiCategoryModal');
    const img = document.getElementById('cat_image_preview');
    const ph = document.getElementById('cat_image_placeholder');
    const inp = document.getElementById('cat_image');
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
      if (modal.dataset.openedByError === '1') return;
      clearValidation();
    });

    modal?.addEventListener('hidden.bs.modal', () => {
      if (blobUrl) { URL.revokeObjectURL(blobUrl); blobUrl = null; }
      if (inp) inp.value = '';
    });

    // Nút Edit
    document.querySelectorAll('.btnCateEdit').forEach(btn => {
      btn.addEventListener('click', function () {
        if (!form || !modal) return;
        clearValidation();

        const updateUrl = this.dataset.updateUrl || '';
        const name = this.dataset.name || '';
        const slug = this.dataset.slug || '';
        const description = this.dataset.description || '';
        const status = this.dataset.status || 'ACTIVE';
        const imageUrl = this.dataset.image || '';

        form.action = updateUrl;
        form.querySelector('input[name="_method"]').value = 'PUT';
        form.querySelector('input[name="__mode"]').value = 'update';
        form.querySelector('input[name="__form"]').value = 'category';
        const upd = form.querySelector('input[name="__update_action"]');
        if (upd) upd.value = updateUrl;

        form.querySelector('input[name="name"]').value = name;
        form.querySelector('input[name="slug"]').value = slug;
        form.querySelector('textarea[name="description"]').value = description;
        const sel = form.querySelector('select[name="status"]');
        if (sel) { sel.value = status; sel.dispatchEvent(new Event('change')); }

        if (imageUrl) showImg(imageUrl); else showPh();

        document.getElementById('catModalTitle').textContent = 'Cập nhật danh mục';
        bootstrap.Modal.getOrCreateInstance(modal).show();
      });
    });
  });
})();
