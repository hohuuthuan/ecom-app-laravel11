
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

/* ============================ AJAX ADD TO CART (ĐÃ GỘP) ============================ */
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const cartCountBadge = document.getElementById('cartCount');

  function updateCartCount(count) {
    if (cartCountBadge) {
      cartCountBadge.textContent = String(Math.max(0, parseInt(count) || 0));
    }
  }

  document.addEventListener('submit', async (e) => {
    const form = e.target.closest('.add-to-cart-form');
    if (!form) return;

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
    const originalButtonHtml = '<i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ';
    if (submitButton) {
      const currentButtonHtml = submitButton.innerHTML;
      submitButton.disabled = true;
      submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang thêm...';
      const htmlToRestore = (currentButtonHtml.trim() === originalButtonHtml.trim()) ? originalButtonHtml : currentButtonHtml;
    }

    try {
      const formData = new FormData(form);
      const url = form.getAttribute('action');

      const response = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf
        }
      });

      const data = await response.json();

      if (response.ok) {
        // --- XỬ LÝ THÀNH CÔNG (HTTP 200) ---
        if (data.count !== undefined) {
          updateCartCount(data.count);
        }
        submitButton.classList.add('btn-success');
        submitButton.innerHTML = '<i class="fas fa-check me-2"></i> Đã thêm vào giỏ hàng!';

        setTimeout(() => {
          submitButton.innerHTML = htmlToRestore;
          submitButton.classList.remove('btn-success');
          submitButton.classList.add('btn-primaryy');
          submitButton.disabled = false;
        }, 1500);

      } else {
        // --- XỬ LÝ LỖI (HTTP 4xx, 5xx) ---
        submitButton.classList.add('btn-danger');

        let errorMsg = data.message || 'Đã xảy ra lỗi';
        if (errorMsg.length > 25) errorMsg = errorMsg.substring(0, 25) + '...';

        submitButton.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> ${errorMsg}`;

        setTimeout(() => {
          submitButton.innerHTML = htmlToRestore;
          submitButton.classList.remove('btn-danger');
          submitButton.classList.add('btn-primaryy');
          submitButton.disabled = false;
        }, 3000);
      }

    } catch (error) {
      console.error('Lỗi khi thêm vào giỏ hàng:', error);

      if (submitButton) {
        submitButton.classList.add('btn-danger');
        submitButton.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Lỗi kết nối';

        setTimeout(() => {
          submitButton.innerHTML = htmlToRestore;
          submitButton.classList.remove('btn-danger');
          submitButton.classList.add('btn-primaryy');
          submitButton.disabled = false;
        }, 3000);
      }
    }
  });
})();
/* ========================= END AJAX ADD TO CART ========================= */


/* ======================== AJAX REMOVE CART ITEM ========================= */
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const cartCountBadge = document.getElementById('cartCount');
  const cartItemsContainer = document.getElementById('cartItems');

  function updateCartCount(count) {
    if (cartCountBadge) {
      cartCountBadge.textContent = String(Math.max(0, parseInt(count) || 0));
    }
  }
  function updateCartSummary(cartData) {
    console.log('Giỏ hàng được cập nhật:', cartData);
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');

    if (subtotalEl) {
      const formatNumber = (n) => Math.floor(n).toLocaleString('vi-VN') + 'đ';
      subtotalEl.textContent = formatNumber(cartData.subtotal || 0);
      totalEl.textContent = formatNumber(cartData.subtotal || 0);
    }

    if (cartData.items && Object.keys(cartData.items).length === 0) {
      window.location.reload();
    }
  }

  document.addEventListener('submit', async (e) => {
    const form = e.target.closest('.remove-cart-item-form');
    if (!form) return;

    e.preventDefault();

    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonHtml = submitButton.innerHTML;
    const itemElement = form.closest('.cart-item');

    if (!itemElement) return;
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    }

    itemElement.style.transition = 'opacity 0.3s, max-height 0.5s';
    itemElement.style.maxHeight = itemElement.offsetHeight + 'px';
    await new Promise(r => setTimeout(r, 50));

    itemElement.style.opacity = '0.5';

    try {
      const url = form.getAttribute('action');
      const formData = new FormData(form);

      const response = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
        }
      });

      const data = await response.json();

      if (response.ok && data.ok) {
        // --- XỬ LÝ THÀNH CÔNG (HTTP 200) ---
        if (data.count !== undefined) {
          updateCartCount(data.count);
        }
        itemElement.style.maxHeight = '0';
        itemElement.style.paddingTop = '0';
        itemElement.style.paddingBottom = '0';

        itemElement.addEventListener('transitionend', () => {
          itemElement.remove();
          if (data.cart) {
            updateCartSummary(data.cart);
          }
        }, { once: true });

      } else {
        // --- XỬ LÝ LỖI ---
        console.error('Lỗi khi xoá sản phẩm:', data.message || 'Lỗi không xác định');
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
/* ===================== END AJAX REMOVE CART ITEM ======================== */