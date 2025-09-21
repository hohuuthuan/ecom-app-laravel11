// Generated from inline <script> blocks in: ecom-app-laravel/resources/views/admin/catalog/index.blade.php
// Each section preserves original order and approximate line ranges.

/* ===== BEGIN inline script #1 (lines 287-320) ===== */
function getTabFromURL() {
  const u = new URL(location.href);
  const t = u.searchParams.get('tab');
  return ['category', 'brand'].includes(t) ? t : 'category';
}

function setTabInURL(tab, replace = false) {
  const u = new URL(location.href);
  u.searchParams.set('tab', tab);
  replace ? history.replaceState(null, '', u) : history.pushState(null, '', u);
}

function showTab(tab) {
  const trigger = document.querySelector(`[data-bs-target="#${tab}-pane"]`);
  if (!trigger) return;
  new bootstrap.Tab(trigger).show();
}

document.addEventListener('DOMContentLoaded', () => {
  // đúng tab theo URL
  showTab(getTabFromURL());
  const tabs = document.getElementById('catalogTabs');
  if (tabs) {
    tabs.addEventListener('shown.bs.tab', (e) => {
      const pane = e.target.getAttribute('data-bs-target');
      const tab = pane.includes('brand') ? 'brand' : 'category';
      setTabInURL(tab);
    });
  }
  window.addEventListener('popstate', () => showTab(getTabFromURL()));
  setTabInURL(getTabFromURL(), true);
});
/* ===== END inline script #1 ===== */

/* ===== BEGIN inline script #2 (lines 322-578) ===== */
// @ts-nocheck
function makeHiddenInputs(container, name, values) {
  container.innerHTML = '';
  values.forEach(v => {
    const i = document.createElement('input');
    i.type = 'hidden';
    i.name = name;
    i.value = v;
    container.appendChild(i);
  });
}

function getCheckedValues(tableSel) {
  const cbs = document.querySelectorAll(`${tableSel} tbody input[type=checkbox]:checked`);
  return Array.from(cbs).map(x => x.value);
}

function toggleMaster(masterSel, rowSel) {
  const master = document.querySelector(masterSel);
  const rows = document.querySelectorAll(rowSel);
  master?.addEventListener('change', () => {
    rows.forEach(cb => { cb.checked = master.checked; });
    master.indeterminate = false;
    updateBulkButtons();
  });
}

function updateBulkButtons() {
  const catAny = getCheckedValues('#categoryTable').length > 0;
  const brandAny = getCheckedValues('#brandTable').length > 0;
  const catBtn = document.getElementById('catBtnBulkDelete');
  const brandBtn = document.getElementById('brandBtnBulkDelete');
  if (catBtn) catBtn.disabled = !catAny;
  if (brandBtn) brandBtn.disabled = !brandAny;
}

document.addEventListener('DOMContentLoaded', function() {
  // ================== Master & bulk ==================
  toggleMaster('#cat_check_all', '#categoryTable tbody .cat-row-checkbox');
  toggleMaster('#brand_check_all', '#brandTable tbody .brand-row-checkbox');
  document.querySelector('#categoryTable')?.addEventListener('change', (e) => {
    if (e.target.classList?.contains('cat-row-checkbox')) updateBulkButtons();
  });
  document.querySelector('#brandTable')?.addEventListener('change', (e) => {
    if (e.target.classList?.contains('brand-row-checkbox')) updateBulkButtons();
  });
  updateBulkButtons();

  // Bulk delete Category
  document.getElementById('catBtnBulkDelete')?.addEventListener('click', async () => {
    const ids = getCheckedValues('#categoryTable');
    if (!ids.length) return;
    const ok = await (window.UIConfirm ? UIConfirm({
      title: 'Xác nhận xoá',
      message: `Bạn sắp xoá <b>${ids.length}</b> category.`
    }) : Promise.resolve(confirm('Xoá các category đã chọn?')));
    if (!ok) return;
    const form = document.getElementById('catBulkForm');
    const box = document.getElementById('catBulkIds');
    makeHiddenInputs(box, 'ids[]', ids);
    form.submit();
  });
  // Xoá category đơn
  document.querySelectorAll('.btnCateDelete').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const form = btn.closest('form');
      const ok = await (window.UIConfirm ? UIConfirm({
        title: 'Xác nhận xoá',
        message: 'Bạn có chắc chắn muốn xoá category này?'
      }) : Promise.resolve(confirm('Xoá category này?')));
      if (ok) form.submit();
    });
  });

  // Bulk delete Brand
  document.getElementById('brandBtnBulkDelete')?.addEventListener('click', async () => {
    const ids = getCheckedValues('#brandTable');
    if (!ids.length) return;
    const ok = await (window.UIConfirm ? UIConfirm({
      title: 'Xác nhận xoá',
      message: `Bạn sắp xoá <b>${ids.length}</b> brand.`
    }) : Promise.resolve(confirm('Xoá các brand đã chọn?')));
    if (!ok) return;
    const form = document.getElementById('brandBulkForm');
    const box = document.getElementById('brandBulkIds');
    makeHiddenInputs(box, 'ids[]', ids);
    form.submit();
  });
  // Xoá brand đơn
  document.querySelectorAll('.btnBrandDelete').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const form = btn.closest('form');
      const ok = await (window.UIConfirm ? UIConfirm({
        title: 'Xác nhận xoá',
        message: 'Bạn có chắc chắn muốn xoá brand này?'
      }) : Promise.resolve(confirm('Xoá brand này?')));
      if (ok) form.submit();
    });
  });

  // ================== CATEGORY MODAL ==================
  const catModal = document.getElementById('uiCategoryModal');
  const catForm  = document.getElementById('uiCategoryForm');
  const catImg   = document.getElementById('cat_image');
  const catPrev  = document.getElementById('cat_image_preview');
  const catPH    = document.getElementById('cat_image_placeholder');
  const catTitle = document.getElementById('catModalTitle');

  function setCatPreview(src) {
    if (src) {
      catPrev.src = src;
      catPrev.classList.remove('d-none');
      catPH.classList.add('d-none');
    } else {
      catPrev.src = '';
      catPrev.classList.add('d-none');
      catPH.classList.remove('d-none');
    }
  }

  // Nút "Thêm category"
  document.querySelectorAll('[data-bs-target="#uiCategoryModal"]').forEach(btn => {
    btn.addEventListener('click', () => {
      catForm.querySelector('[name="__mode"]').value = 'create';
      catForm.action = catForm.dataset.storeUrl;
      catForm.querySelector('[name=_method]').value = 'POST';
      catForm.querySelector('[name="__form"]').value = 'category';
      const upd = catForm.querySelector('[name="__update_action"]');
      if (upd) upd.value = '';
      const img = catForm.querySelector('[name="__image"]');
      if (img) img.value = '';
      if (catTitle) catTitle.textContent = 'Thêm category';
      setCatPreview('');
    });
  });

  // Nút "Sửa category"
  document.querySelectorAll('.btnCateEdit').forEach(btn => {
    btn.addEventListener('click', () => {
      catForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      catForm.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');

      catForm.querySelector('[name="__mode"]').value = 'edit';
      catForm.action = btn.dataset.updateUrl;
      catForm.querySelector('[name=_method]').value = 'PUT';
      catForm.querySelector('[name="__form"]').value = 'category';
      const upd = catForm.querySelector('[name="__update_action"]');
      if (upd) upd.value = btn.dataset.updateUrl || '';
      const img = catForm.querySelector('[name="__image"]');
      if (img) img.value = btn.dataset.image || '';

      catForm.querySelector('#cat_name').value        = btn.dataset.name || '';
      catForm.querySelector('#cat_slug').value        = btn.dataset.slug || '';
      catForm.querySelector('#cat_description').value = btn.dataset.description || '';
      catForm.querySelector('#cat_status').value      = (btn.dataset.status || 'ACTIVE');
      if (window.jQuery && $.fn?.select2) $('#cat_status').trigger('change.select2');

      setCatPreview(btn.dataset.image || '');
      try { if (catImg) catImg.value = ''; } catch(_) {}

      if (catTitle) catTitle.textContent = 'Cập nhật category';
      bootstrap.Modal.getOrCreateInstance(catModal).show();
    });
  });

  catImg?.addEventListener('change', () => {
    const f = catImg.files?.[0];
    setCatPreview(f ? URL.createObjectURL(f) : '');
  });

  // ================== BRAND MODAL ==================
  const brandModal = document.getElementById('uiBrandModal');
  const brandForm  = document.getElementById('uiBrandForm');
  const brandImg   = document.getElementById('brand_image');
  const brandPrev  = document.getElementById('brand_image_preview');
  const brandPH    = document.getElementById('brand_image_placeholder');
  const brandTitle = document.getElementById('brandModalTitle');

  function setBrandPreview(src) {
    if (!brandPrev || !brandPH) return;
    if (src) {
      brandPrev.src = src;
      brandPrev.classList.remove('d-none');
      brandPH.classList.add('d-none');
    } else {
      brandPrev.src = '';
      brandPrev.classList.add('d-none');
      brandPH.classList.remove('d-none');
    }
  }

  // Nút "Thêm brand"
  document.querySelectorAll('[data-bs-target="#uiBrandModal"]').forEach(btn => {
    btn.addEventListener('click', () => {
      brandForm.querySelector('[name="__mode"]').value = 'create';
      brandForm.action = brandForm.dataset.storeUrl;
      brandForm.querySelector('[name=_method]').value = 'POST';
      brandForm.querySelector('[name="__form"]').value = 'brand';
      const upd = brandForm.querySelector('[name="__update_action"]');
      if (upd) upd.value = '';
      const img = brandForm.querySelector('[name="__image"]');
      if (img) img.value = '';
      if (brandTitle) brandTitle.textContent = 'Thêm brand';
      setBrandPreview('');
    });
  });

  // Nút "Sửa brand"
  document.querySelectorAll('.btnBrandEdit').forEach(btn => {
    btn.addEventListener('click', () => {
      brandForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      brandForm.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');

      brandForm.querySelector('[name="__mode"]').value = 'edit';
      brandForm.action = btn.dataset.updateUrl;
      brandForm.querySelector('[name=_method]').value = 'PUT';
      brandForm.querySelector('[name="__form"]').value = 'brand';
      const upd = brandForm.querySelector('[name="__update_action"]');
      if (upd) upd.value = btn.dataset.updateUrl || '';
      const img = brandForm.querySelector('[name="__image"]');
      if (img) img.value = btn.dataset.image || '';

      brandForm.querySelector('#brand_name').value        = btn.dataset.name || '';
      brandForm.querySelector('#brand_slug').value        = btn.dataset.slug || '';
      brandForm.querySelector('#brand_description').value = btn.dataset.description || '';
      brandForm.querySelector('#brand_status').value      = btn.dataset.status || 'ACTIVE';
      if (window.jQuery && $.fn?.select2) $('#brand_status').trigger('change.select2');

      setBrandPreview(btn.dataset.image || '');
      try { if (brandImg) brandImg.value = ''; } catch(_) {}

      if (brandTitle) brandTitle.textContent = 'Cập nhật brand';
      bootstrap.Modal.getOrCreateInstance(brandModal).show();
    });
  });

  brandImg?.addEventListener('change', () => {
    const f = brandImg.files?.[0];
    setBrandPreview(f ? URL.createObjectURL(f) : '');
  });

  // ================== RE-OPEN MODALS WHEN VALIDATION ERROR ==================
  // <div id="__formState" data-has-errors="1|0" data-which="category|brand" data-mode="create|edit"
  //      data-update-action="..." data-image="..."></div>
  const __stateEl   = document.getElementById('__formState');
  const __hasErrors = __stateEl?.dataset.hasErrors === '1';
  const __which     = (__stateEl?.dataset.which || null);
  const __mode      = (__stateEl?.dataset.mode || 'create');

  if (__hasErrors && __which === 'category') {
    const __updateAction = __stateEl?.dataset.updateAction || '';
    const __image = __stateEl?.dataset.image || '';

    catForm.querySelector('[name="__form"]').value = 'category';
    if (__mode === 'edit' && __updateAction) {
      catForm.action = __updateAction;
      catForm.querySelector('[name=_method]').value = 'PUT';
      if (catTitle) catTitle.textContent = 'Cập nhật category';
    } else {
      catForm.action = catForm.dataset.storeUrl;
      catForm.querySelector('[name=_method]').value = 'POST';
      if (catTitle) catTitle.textContent = 'Thêm category';
    }
    setCatPreview(__image || '');
    bootstrap.Modal.getOrCreateInstance(catModal).show();
    const trigger = document.querySelector('[data-bs-target="#category-pane"]');
    if (trigger) new bootstrap.Tab(trigger).show();
  }

  if (__hasErrors && __which === 'brand') {
    const __updateAction = __stateEl?.dataset.updateAction || '';
    const __image = __stateEl?.dataset.image || '';

    brandForm.querySelector('[name="__form"]').value = 'brand';
    if (__mode === 'edit' && __updateAction) {
      brandForm.action = __updateAction;
      brandForm.querySelector('[name=_method]').value = 'PUT';
      if (brandTitle) brandTitle.textContent = 'Cập nhật brand';
    } else {
      brandForm.action = brandForm.dataset.storeUrl;
      brandForm.querySelector('[name=_method]').value = 'POST';
      if (brandTitle) brandTitle.textContent = 'Thêm brand';
    }
    setBrandPreview(__image || '');
    bootstrap.Modal.getOrCreateInstance(brandModal).show();
    const trigger = document.querySelector('[data-bs-target="#brand-pane"]');
    if (trigger) new bootstrap.Tab(trigger).show();
  }
});
/* ===== END inline script #2 ===== */
