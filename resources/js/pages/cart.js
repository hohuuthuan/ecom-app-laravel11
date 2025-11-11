/* ===================== SHARED HELPERS (DÙNG CHUNG) ===================== */
(function () {
  if (!window.CartUI) { window.CartUI = {}; }

  window.CartUI.fmt = function (n) {
    return Math.floor(n || 0).toLocaleString('vi-VN') + 'đ';
  };

  window.CartUI.updateCartCount = function (count) {
    var badge = document.getElementById('cartCount');
    if (badge) { badge.textContent = String(Math.max(0, parseInt(count) || 0)); }
  };

  // Lấy các dòng đang được chọn
  window.CartUI.getSelectedRows = function () {
    var container = document.getElementById('cartItems');
    if (!container) { return []; }
    var rows = container.querySelectorAll('.cart-item');
    var selected = [];
    for (var i = 0; i < rows.length; i++) {
      var cb = rows[i].querySelector('.item-checkbox');
      if (cb && cb.checked) { selected.push(rows[i]); }
    }
    return selected;
  };

  // Tính tóm tắt theo lựa chọn hiện tại trên DOM
  window.CartUI.updateSummaryFromSelection = function () {
    var selected = window.CartUI.getSelectedRows();
    var selectedCountEl = document.getElementById('selectedCount');
    var subtotalEl = document.getElementById('subtotal');
    var totalEl = document.getElementById('total');
    var hiddenKeys = document.getElementById('selectedKeys');
    var checkoutBtn = document.getElementById('proceedCheckout');

    var sum = 0;
    var keys = [];

    for (var i = 0; i < selected.length; i++) {
      var row = selected[i];
      var t = parseInt(row.getAttribute('data-total') || '0', 10);
      if (!isNaN(t)) { sum += t; }
      var key = row.getAttribute('data-key') || '';
      if (key) { keys.push(key); }
    }

    if (selectedCountEl) { selectedCountEl.textContent = 'Đã chọn ' + selected.length + ' sản phẩm'; }
    if (hiddenKeys) { hiddenKeys.value = keys.join(','); }
    if (subtotalEl) { subtotalEl.textContent = window.CartUI.fmt(sum); }
    if (totalEl) { totalEl.textContent = window.CartUI.fmt(sum); }
    if (checkoutBtn) { checkoutBtn.disabled = selected.length === 0; }
  };

  // Đồng bộ trạng thái "Chọn tất cả"
  window.CartUI.syncSelectAll = function () {
    var container = document.getElementById('cartItems');
    var selectAll = document.getElementById('selectAll');
    if (!selectAll || !container) { return; }
    var totalRows = container.querySelectorAll('.cart-item').length;
    var selectedLen = window.CartUI.getSelectedRows().length;
    selectAll.checked = (selectedLen > 0 && selectedLen === totalRows);
    selectAll.indeterminate = (selectedLen > 0 && selectedLen < totalRows);
  };

  // Bind sự kiện tick/untick và chọn tất cả
  window.CartUI.bindSelectionEvents = function () {
    var container = document.getElementById('cartItems');
    var selectAll = document.getElementById('selectAll');

    if (selectAll) {
      selectAll.addEventListener('change', function () {
        if (!container) { return; }
        var cbs = container.querySelectorAll('.cart-item .item-checkbox');
        for (var i = 0; i < cbs.length; i++) {
          cbs[i].checked = selectAll.checked;
        }
        window.CartUI.updateSummaryFromSelection();
        window.CartUI.syncSelectAll();
      });
    }

    if (container) {
      container.addEventListener('change', function (e) {
        var cb = e.target.closest('.item-checkbox');
        if (!cb) { return; }
        window.CartUI.updateSummaryFromSelection();
        window.CartUI.syncSelectAll();
      });
    }

    // Khởi tạo UI lúc load trang
    document.addEventListener('DOMContentLoaded', function () {
      window.CartUI.updateSummaryFromSelection();
      window.CartUI.syncSelectAll();
    });
  };

  // Cập nhật lại inputs +/- theo qty mới
  window.CartUI.refreshQtyForms = function (rowEl, qty) {
    var minusForm = rowEl.querySelector('form.d-inline:nth-of-type(1)');
    var plusForm = rowEl.querySelector('form.d-inline:nth-of-type(2)');
    if (minusForm) {
      var minusInput = minusForm.querySelector('input[name="qty"]');
      if (minusInput) { minusInput.value = String(Math.max(1, qty - 1)); }
      var minusBtn = minusForm.querySelector('button[type="submit"]');
      if (minusBtn) { minusBtn.disabled = qty <= 1; }
    }
    if (plusForm) {
      var plusInput = plusForm.querySelector('input[name="qty"]');
      if (plusInput) { plusInput.value = String(qty + 1); }
    }
  };

  // Gọi lại sau mỗi tác động (tăng/giảm/xoá/thêm)
  window.CartUI.refreshSelectionUI = function () {
    window.CartUI.updateSummaryFromSelection();
    window.CartUI.syncSelectAll();
  };
})();
if (window.CartUI && typeof window.CartUI.bindSelectionEvents === 'function') {
  window.CartUI.bindSelectionEvents();
}

/* ===================== COUNT CART ITEMS (SIMPLE & SAFE) ===================== */
(function () {
  var badge = document.getElementById('cartCount');
  if (!badge) { return; }

  var COUNT_URL = badge.getAttribute('data-count-url') || '/cart/count';

  function set(n) {
    badge.textContent = String(Math.max(0, n | 0));
  }

  function sync() {
    fetch(COUNT_URL, {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) { if (d && typeof d.count === 'number') { set(d.count); } })
      .catch(function () { });
  }

  document.addEventListener('DOMContentLoaded', sync);
  window.addEventListener('pageshow', sync);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) { sync(); }
  });
})();


/* ============================ AJAX ADD TO CART ============================ */
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const cartCountBadge = document.getElementById('cartCount');

  function updateCartCount(count) {
    if (cartCountBadge) { cartCountBadge.textContent = String(Math.max(0, parseInt(count) || 0)); }
  }

  document.addEventListener('submit', async (e) => {
    const form = e.target.closest('.add-to-cart-form');
    if (!form) { return; }

    e.preventDefault();

    if (form.id === 'addToCartForm') {
      const qtyInput = document.getElementById('quantity');
      const hiddenQty = document.getElementById('addToCartQty');
      if (qtyInput && hiddenQty) {
        const n = Math.max(1, parseInt(qtyInput.value || '1', 10));
        hiddenQty.value = String(n);
      }
    }

    const submitButton = form.querySelector('button[type="submit"]');
    let htmlToRestore = '';
    if (submitButton) {
      htmlToRestore = submitButton.innerHTML;
      submitButton.disabled = true;
      submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang thêm...';
    }

    try {
      const formData = new FormData(form);
      const url = form.getAttribute('action') || '';

      const response = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        credentials: 'same-origin'
      });

      const data = await response.json();

      if (response.ok) {
        if (typeof data.count === 'number') { updateCartCount(data.count); }
        if (submitButton) {
          submitButton.classList.add('btn-success');
          submitButton.innerHTML = '<i class="fas fa-check me-2"></i> Đã thêm vào giỏ hàng!';
          setTimeout(function () {
            submitButton.innerHTML = htmlToRestore;
            submitButton.classList.remove('btn-success');
            submitButton.classList.add('btn-primary');
            submitButton.disabled = false;
          }, 1500);
        }
        // Không can thiệp tóm tắt ở đây; chỉ khi người dùng tick chọn mới tính.
        if (window.CartUI) { window.CartUI.refreshSelectionUI(); }
      } else {
        let errorMsg = (data && data.message) ? String(data.message) : 'Đã xảy ra lỗi';
        if (errorMsg.length > 25) { errorMsg = errorMsg.substring(0, 25) + '...'; }
        if (submitButton) {
          submitButton.classList.add('btn-danger');
          submitButton.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> ' + errorMsg;
          setTimeout(function () {
            submitButton.innerHTML = htmlToRestore;
            submitButton.classList.remove('btn-danger');
            submitButton.classList.add('btn-primary');
            submitButton.disabled = false;
          }, 3000);
        }
      }
    } catch (error) {
      console.error('Lỗi khi thêm vào giỏ hàng:', error);
      if (submitButton) {
        submitButton.classList.add('btn-danger');
        submitButton.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Lỗi kết nối';
        setTimeout(function () {
          submitButton.innerHTML = htmlToRestore;
          submitButton.classList.remove('btn-danger');
          submitButton.classList.add('btn-primary');
          submitButton.disabled = false;
        }, 3000);
      }
    }
  });
})();


/* ======================== AJAX REMOVE CART ITEM ========================= */
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  document.addEventListener('submit', async (e) => {
    const form = e.target.closest('.remove-cart-item-form');
    if (!form) { return; }

    e.preventDefault();

    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonHtml = submitButton ? submitButton.innerHTML : '';
    const itemElement = form.closest('.cart-item');
    if (!itemElement) { return; }

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    }

    itemElement.style.transition = 'opacity 0.3s, max-height 0.5s';
    itemElement.style.maxHeight = itemElement.offsetHeight + 'px';
    await new Promise(function (r) { setTimeout(r, 50); });
    itemElement.style.opacity = '0.5';

    try {
      const url = form.getAttribute('action') || '';
      const formData = new FormData(form);

      const response = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        credentials: 'same-origin'
      });

      const data = await response.json();

      if (response.ok && data && data.ok) {
        if (typeof data.count === 'number') { window.CartUI.updateCartCount(data.count); }
        itemElement.style.maxHeight = '0';
        itemElement.style.paddingTop = '0';
        itemElement.style.paddingBottom = '0';
        itemElement.addEventListener('transitionend', function () {
          itemElement.remove();
          // Tính tóm tắt theo tick hiện tại
          window.CartUI.refreshSelectionUI();
        }, { once: true });
      } else {
        console.error('Lỗi khi xoá sản phẩm:', (data && data.message) || 'Lỗi không xác định');
        if (submitButton) {
          submitButton.innerHTML = originalButtonHtml;
          submitButton.disabled = false;
        }
        itemElement.style.opacity = '1';
      }
    } catch (error) {
      console.error('Lỗi mạng khi xoá sản phẩm:', error);
      if (submitButton) {
        submitButton.innerHTML = originalButtonHtml;
        submitButton.disabled = false;
      }
      itemElement.style.opacity = '1';
    }
  });
})();


/* ========================= AJAX UPDATE CART QTY (PATCH) ========================= */
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  document.addEventListener('submit', async (e) => {
    const form = e.target.closest('form.d-inline');
    if (!form) { return; }
    const action = form.getAttribute('action') || '';
    if (action.indexOf('/cart/item/') === -1) { return; }
    if (!form.querySelector('input[name="_method"][value="PATCH"]')) { return; }

    e.preventDefault();

    const row = form.closest('.cart-item');
    if (!row) { return; }

    const submitBtn = form.querySelector('button[type="submit"]');
    const oldHtml = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    }

    try {
      const fd = new FormData(form);
      const res = await fetch(action, {
        method: 'POST',
        body: fd,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf
        },
        credentials: 'same-origin'
      });

      const data = await res.json();

      if (res.ok && data && data.ok && data.cart) {
        if (typeof data.count === 'number') { window.CartUI.updateCartCount(data.count); }

        const key = row.getAttribute('data-key');
        var updated = null;
        const items = Array.isArray(data.cart.items) ? data.cart.items : Object.values(data.cart.items || {});
        for (var i = 0; i < items.length; i++) {
          if (String(items[i].key) === String(key)) { updated = items[i]; break; }
        }

        // Nếu item đã bị remove (qty < 1)
        if (!updated) {
          row.style.transition = 'opacity .25s, max-height .35s, padding .25s';
          row.style.maxHeight = row.offsetHeight + 'px';
          requestAnimationFrame(function () {
            row.style.opacity = '0';
            row.style.maxHeight = '0';
            row.style.paddingTop = '0';
            row.style.paddingBottom = '0';
          });
          row.addEventListener('transitionend', function () {
            row.remove();
            window.CartUI.refreshSelectionUI();
          }, { once: true });
          return;
        }

        // Cập nhật lại DOM theo qty mới
        const qty = parseInt(updated.qty || 0, 10);
        const lineTotal = parseInt(updated.line_total || 0, 10);

        row.setAttribute('data-qty', String(qty));
        row.setAttribute('data-total', String(lineTotal));

        var qtyDisplay = row.querySelector('.quantity-display');
        if (qtyDisplay) { qtyDisplay.textContent = String(qty); }

        var lineTotalEl = row.querySelector('.line-total');
        if (lineTotalEl) { lineTotalEl.textContent = window.CartUI.fmt(lineTotal); }

        window.CartUI.refreshQtyForms(row, qty);
        // Không dùng tổng từ server; chỉ nếu dòng đang tick thì mới ảnh hưởng tổng
        window.CartUI.refreshSelectionUI();
      } else {
        console.error('Cập nhật số lượng thất bại:', data && data.message);
      }
    } catch (err) {
      console.error('Lỗi mạng khi cập nhật số lượng:', err);
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = oldHtml;
      }
    }
  });
})();
