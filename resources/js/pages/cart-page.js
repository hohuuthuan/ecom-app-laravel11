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

  function getSelectedItems() {
    const rows = itemsWrap.querySelectorAll('.cart-item');
    const out = [];
    rows.forEach(function (row) {
      const cb = row.querySelector('.item-checkbox');
      if (cb && cb.checked) {
        out.push({
          key: row.getAttribute('data-key'),
          price: parseInt(row.getAttribute('data-price') || '0', 10),
          qty: parseInt(row.getAttribute('data-qty') || '0', 10),
          total: parseInt(row.getAttribute('data-total') || '0', 10),
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
      const keys = selected.map(function (x) { return x.key; });
      keysInput.value = keys.join(',');
    }
  }

  function refreshSelectedUI() {
    const rows = itemsWrap.querySelectorAll('.cart-item');
    rows.forEach(function (row) {
      const cb = row.querySelector('.item-checkbox');
      if (cb && cb.checked) { row.classList.add('selected'); } else { row.classList.remove('selected'); }
    });
  }

  function syncSelectAllState() {
    const checks = itemsWrap.querySelectorAll('.item-checkbox');
    let total = 0; let tick = 0;
    checks.forEach(function (cb) { total += 1; if (cb.checked) { tick += 1; } });
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
      const checks = itemsWrap.querySelectorAll('.item-checkbox');
      const val = !!selectAll.checked;
      checks.forEach(function (cb) { cb.checked = val; });
      refreshSelectedUI();
      syncSelectAllState();
      updateSummary();
    });
  }

  // Khởi tạo lần đầu
  refreshSelectedUI();
  syncSelectAllState();
  updateSummary();
});
