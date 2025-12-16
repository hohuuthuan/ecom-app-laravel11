document.addEventListener('DOMContentLoaded', function () {
  const itemsWrap = document.getElementById('cartItems');
  const selectAll = document.getElementById('selectAll');
  const lblSelected = document.getElementById('selectedCount');
  const elSubtotal = document.getElementById('subtotal');
  const elShipping = document.getElementById('shipping');
  const elTotal = document.getElementById('total');
  const proceedBtn = document.getElementById('proceedCheckout');
  const keysInput = document.getElementById('selectedKeys');

  if (!itemsWrap) { return; }

  function fmt(n) {
    try { return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(n); }
    catch { return (n || 0).toLocaleString('vi-VN') + '₫'; }
  }

  function getIntAttr(el, name, fallback) {
    const v = parseInt(el.getAttribute(name) || '', 10);
    return Number.isFinite(v) ? v : fallback;
  }

  function applyPlusState(row) {
    const qty = getIntAttr(row, 'data-qty', 0);
    const max = getIntAttr(row, 'data-max', 0);
    const incForm = row.querySelector('form[data-action="inc"]');
    const incBtn = incForm ? incForm.querySelector('button[type="submit"]') : null;

    if (!incBtn) { return; }

    const reachedMax = max > 0 && qty >= max;
    incBtn.disabled = reachedMax;
    incBtn.classList.toggle('disabled', reachedMax);
    incBtn.setAttribute('aria-disabled', reachedMax ? 'true' : 'false');

    if (reachedMax) {
      incBtn.setAttribute('title', 'Đã đạt tối đa theo tồn kho');
    } else {
      incBtn.removeAttribute('title');
    }
  }

  function applyAllPlusStates() {
    itemsWrap.querySelectorAll('.cart-item').forEach(function (row) {
      applyPlusState(row);
    });
  }

  function getSelectedItems() {
    const out = [];
    itemsWrap.querySelectorAll('.cart-item').forEach(function (row) {
      const cb = row.querySelector('.item-checkbox');
      if (cb && cb.checked) {
        out.push({
          key: row.getAttribute('data-key'),
          total: getIntAttr(row, 'data-total', 0),
          row
        });
      }
    });
    return out;
  }

  function updateSummary() {
    const selected = getSelectedItems();
    const count = selected.length;
    let subtotal = 0;

    for (let i = 0; i < selected.length; i++) {
      subtotal += selected[i].total;
    }

    const shipping = count > 0 ? 30000 : 0;
    const total = subtotal + shipping;

    if (lblSelected) { lblSelected.textContent = 'Đã chọn ' + count + ' sản phẩm'; }
    if (elSubtotal) { elSubtotal.textContent = fmt(subtotal); }
    if (elShipping) { elShipping.textContent = fmt(shipping); }
    if (elTotal) { elTotal.textContent = fmt(total); }
    if (proceedBtn) { proceedBtn.disabled = count === 0; }

    if (keysInput) {
      keysInput.value = selected.map(function (x) { return x.key; }).join(',');
    }
  }

  function refreshSelectedUI() {
    itemsWrap.querySelectorAll('.cart-item').forEach(function (row) {
      const cb = row.querySelector('.item-checkbox');
      if (cb && cb.checked) { row.classList.add('selected'); } else { row.classList.remove('selected'); }
    });
  }

  function syncSelectAllState() {
    const checks = itemsWrap.querySelectorAll('.item-checkbox');
    let total = 0;
    let tick = 0;

    checks.forEach(function (cb) {
      total += 1;
      if (cb.checked) { tick += 1; }
    });

    if (selectAll) {
      selectAll.checked = total > 0 && tick === total;
      selectAll.indeterminate = tick > 0 && tick < total;
    }
  }

  itemsWrap.addEventListener('change', function (e) {
    const cb = e.target.closest('.item-checkbox');
    if (!cb) { return; }
    refreshSelectedUI();
    syncSelectAllState();
    updateSummary();
  });

  if (selectAll) {
    selectAll.addEventListener('change', function () {
      const val = !!selectAll.checked;
      itemsWrap.querySelectorAll('.item-checkbox').forEach(function (cb) { cb.checked = val; });
      refreshSelectedUI();
      syncSelectAllState();
      updateSummary();
    });
  }

  itemsWrap.addEventListener('submit', function (e) {
    const form = e.target.closest('form[data-action="inc"]');
    if (!form) { return; }

    const row = form.closest('.cart-item');
    if (!row) { return; }

    const qty = getIntAttr(row, 'data-qty', 0);
    const max = getIntAttr(row, 'data-max', 0);

    if (max > 0 && qty >= max) {
      e.preventDefault();
      applyPlusState(row);
    }
  }, true);

  // Init
  refreshSelectedUI();
  syncSelectAllState();
  updateSummary();
  applyAllPlusStates();

  window.addEventListener('pageshow', function () {
    applyAllPlusStates();
  });
});
