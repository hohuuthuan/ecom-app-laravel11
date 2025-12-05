// ================== TÍNH TIỀN + MÃ GIẢM GIÁ ==================
document.addEventListener('DOMContentLoaded', function () {
  function formatVND(n) {
    try {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(n);
    } catch (e) {
      return (n || 0).toLocaleString('vi-VN') + '₫';
    }
  }

  // ID trong blade là "checkoutPage" (KHÔNG phải checkoutRoot)
  var root = document.getElementById('checkoutPage');
  if (!root) { return; }

  var sub = parseInt(root.getAttribute('data-subtotal') || '0', 10);
  var ship = parseInt(root.getAttribute('data-shipping') || '0', 10);

  function setTotals(currSub, currShip, discount) {
    var subtotalEl = document.getElementById('checkoutSubtotal');
    var shippingEl = document.getElementById('checkoutShipping');
    var discountEl = document.getElementById('checkoutDiscount');
    var totalEl = document.getElementById('checkoutTotal');

    if (subtotalEl) { subtotalEl.textContent = formatVND(currSub); }
    if (shippingEl) { shippingEl.textContent = formatVND(currShip); }
    if (discountEl) { discountEl.textContent = '-' + formatVND(discount); }
    if (totalEl) {
      var total = Math.max(0, currSub - discount + currShip);
      totalEl.textContent = formatVND(total);
    }
  }

  window.applyDiscount = function () {
    var inputEl = document.getElementById('discountCode');
    var msgEl = document.getElementById('discountMessage');
    var rootEl = document.getElementById('checkoutPage');
    var btnEl = document.getElementById('discountApplyButton');

    if (!inputEl || !msgEl || !rootEl || !btnEl) {
      return;
    }

    var code = inputEl.value.trim();
    msgEl.textContent = '';
    msgEl.classList.remove('discount-message-error');

    if (code === '') {
      var tokenDelete = document.querySelector('meta[name="csrf-token"]');
      var csrfDelete = tokenDelete ? tokenDelete.getAttribute('content') : '';

      btnEl.disabled = true;
      btnEl.classList.remove('apply-btn-success');
      btnEl.innerHTML = 'Đang xoá...';

      fetch('/checkout/discount', {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfDelete,
          'Accept': 'application/json',
        },
      })
        .then(function (res) {
          return res.json().catch(function () {
            return { ok: false };
          });
        })
        .then(function (res) {
          setTotals(sub, ship, 0);
          rootEl.setAttribute('data-subtotal', String(sub));
          rootEl.setAttribute('data-shipping', String(ship));
          msgEl.textContent = res.ok ? (res.message || 'Đã xoá mã giảm giá.') : 'Đã xoá mã giảm giá.';
        })
        .catch(function () {
          setTotals(sub, ship, 0);
          rootEl.setAttribute('data-subtotal', String(sub));
          rootEl.setAttribute('data-shipping', String(ship));
          msgEl.textContent = 'Đã xoá mã giảm giá.';
        })
        .finally(function () {
          btnEl.disabled = false;
          btnEl.classList.remove('apply-btn-success');
          btnEl.innerHTML = 'Áp Dụng';
        });

      return;
    }

    var subtotal = parseInt(rootEl.getAttribute('data-subtotal') || '0', 10);
    var shipping = parseInt(rootEl.getAttribute('data-shipping') || '0', 10);
    var token = document.querySelector('meta[name="csrf-token"]');
    var csrf = token ? token.getAttribute('content') : '';

    btnEl.disabled = true;
    btnEl.classList.remove('apply-btn-success');
    btnEl.innerHTML = 'Đang áp dụng...';

    fetch('/checkout/apply-discount', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        code: code,
        subtotal: subtotal,
        shipping: shipping,
      }),
    })
      .then(function (res) {
        return res.json().then(function (json) {
          return { status: res.status, body: json };
        }).catch(function () {
          return { status: res.status, body: null };
        });
      })
      .then(function (res) {
        if (!res.body) {
          msgEl.textContent = 'Không thể áp dụng mã giảm giá. Vui lòng thử lại.';
          msgEl.classList.add('discount-message-error');
          setTotals(subtotal, shipping, 0);
          rootEl.setAttribute('data-subtotal', String(subtotal));
          rootEl.setAttribute('data-shipping', String(shipping));
          btnEl.disabled = false;
          btnEl.classList.remove('apply-btn-success');
          btnEl.innerHTML = 'Áp Dụng';
          return;
        }

        if (!res.body.ok) {
          msgEl.textContent = res.body.message || 'Mã giảm giá không hợp lệ.';
          msgEl.classList.add('discount-message-error');
          setTotals(subtotal, shipping, 0);
          rootEl.setAttribute('data-subtotal', String(subtotal));
          rootEl.setAttribute('data-shipping', String(shipping));
          btnEl.disabled = false;
          btnEl.classList.remove('apply-btn-success');
          btnEl.innerHTML = 'Áp Dụng';
          return;
        }

        var data = res.body.data || {};
        var newSubtotal = typeof data.subtotal_vnd === 'number' ? data.subtotal_vnd : subtotal;
        var newShipping = typeof data.shipping_vnd === 'number' ? data.shipping_vnd : shipping;
        var discountVnd = (data.discount_vnd || 0) + (data.shipping_discount_vnd || 0);

        setTotals(newSubtotal, newShipping, discountVnd);
        rootEl.setAttribute('data-subtotal', String(newSubtotal));
        rootEl.setAttribute('data-shipping', String(newShipping));

        msgEl.textContent = res.body.message || 'Áp dụng mã giảm giá thành công.';
        btnEl.disabled = false;
        btnEl.classList.add('apply-btn-success');
        btnEl.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
      })
      .catch(function () {
        msgEl.textContent = 'Có lỗi xảy ra khi áp dụng mã. Vui lòng thử lại.';
        msgEl.classList.add('discount-message-error');
        setTotals(subtotal, shipping, 0);
        rootEl.setAttribute('data-subtotal', String(subtotal));
        rootEl.setAttribute('data-shipping', String(shipping));
        btnEl.disabled = false;
        btnEl.classList.remove('apply-btn-success');
        btnEl.innerHTML = 'Áp Dụng';
      });
  };

  setTotals(sub, ship, 0);
});

// ================== CHỌN PHƯƠNG THỨC THANH TOÁN ==================
document.addEventListener('DOMContentLoaded', function () {
  var options = document.querySelectorAll('.js-payment-option');
  var methodInp = document.getElementById('paymentMethodInput');
  var submitBtn = document.getElementById('paymentSubmitButton');

  function updateButton(method) {
    if (!submitBtn) { return; }

    if (method === 'momo') {
      submitBtn.innerHTML = 'Thanh toán với MOMO';
    } else if (method === 'vnpay') {
      submitBtn.innerHTML = 'Thanh toán VNPAY';
    } else {
      submitBtn.innerHTML = '<i class="bi bi-truck"></i> Đặt hàng';
    }
  }

  options.forEach(function (opt) {
    opt.addEventListener('click', function () {
      var method = opt.getAttribute('data-method') || 'cod';

      options.forEach(function (o) {
        o.classList.remove('selected');
        var r = o.querySelector('.radio-custom');
        var d = o.querySelector('.radio-dot');
        if (r) { r.classList.remove('checked'); }
        if (d) { d.classList.remove('show'); }
      });

      opt.classList.add('selected');
      var radio = opt.querySelector('.radio-custom');
      var dot = opt.querySelector('.radio-dot');
      if (radio) { radio.classList.add('checked'); }
      if (dot) { dot.classList.add('show'); }

      if (methodInp) { methodInp.value = method; }
      updateButton(method);
    });
  });

  if (methodInp) {
    updateButton(methodInp.value || 'cod');
  }
});

// ================== CHỌN LẠI ĐỊA CHỈ (KHÔNG NỐI LẠI PROVINCE/WARD VÀO TITLE) ==================
(function () {
  var modalEl = document.getElementById('selectAddressModal');
  if (!modalEl) { return; }

  var cards = modalEl.querySelectorAll('.js-address-select-card');
  var hiddenInput = document.getElementById('shippingAddressId');
  var selectedCard = document.getElementById('selectedAddressCard');
  var selectedBody = document.getElementById('selectedAddressBody');
  var selectedTitle = selectedCard
    ? selectedCard.querySelector('.selected-address-text')
    : null;

  function updateSelectedAddress(fullText, note) {
    if (!selectedBody) { return; }

    // Xóa sạch nội dung cũ (tránh nhân đôi "Ghi chú")
    selectedBody.innerHTML = '';

    // Nội dung chính: fullText từ data-text (thường là "địa chỉ, phường, tỉnh")
    var mainNode = document.createTextNode(fullText);
    selectedBody.appendChild(mainNode);

    // Thêm note nếu có
    if (note) {
      var br = document.createElement('br');
      var small = document.createElement('small');
      small.className = 'text-muted';
      small.textContent = 'Ghi chú: ' + note;
      selectedBody.appendChild(br);
      selectedBody.appendChild(small);
    }

    // TITLE (dòng trên) LUÔN CHỈ LÀ PHẦN ĐỊA CHỈ CHÍNH (phần trước dấu phẩy)
    if (selectedTitle) {
      var titleText = fullText.split(',')[0].trim();
      selectedTitle.textContent = titleText;
    }
  }

  cards.forEach(function (card) {
    card.addEventListener('click', function () {
      var id = card.getAttribute('data-id');
      if (!id) { return; }

      if (hiddenInput) {
        hiddenInput.value = id;
      }

      // Đánh dấu card đang chọn
      cards.forEach(function (c) {
        c.classList.remove('is-active');
      });
      card.classList.add('is-active');

      // data-text: full string hiển thị (địa chỉ + ward + province)
      var fullText = card.getAttribute('data-text') || '';
      var note = card.getAttribute('data-note') || '';

      updateSelectedAddress(fullText, note);

      // Đóng modal
      if (window.bootstrap) {
        var modal = window.bootstrap.Modal.getInstance(modalEl)
          || window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
      }
    });
  });
})();
