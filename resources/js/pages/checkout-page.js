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

  var root = document.getElementById('checkoutPage');
  if (!root) {
    return;
  }

  var sub = parseInt(root.getAttribute('data-subtotal') || '0', 10);
  var ship = parseInt(root.getAttribute('data-shipping') || '0', 10);

  function setTotals(currSub, currShip, discount) {
    var subtotalEl = document.getElementById('checkoutSubtotal');
    var shippingEl = document.getElementById('checkoutShipping');
    var discountEl = document.getElementById('checkoutDiscount');
    var totalEl = document.getElementById('checkoutTotal');

    if (subtotalEl) {
      subtotalEl.textContent = formatVND(currSub);
    }
    if (shippingEl) {
      shippingEl.textContent = formatVND(currShip);
    }
    if (discountEl) {
      discountEl.textContent = '-' + formatVND(discount);
    }
    if (totalEl) {
      var total = Math.max(0, currSub - discount + currShip);
      totalEl.textContent = formatVND(total);
    }
  }

  function setApplyButtonState(state) {
    var btn = document.getElementById('discountApplyButton');
    if (!btn) {
      return;
    }

    var label = btn.querySelector('.apply-btn-label');
    var spinner = btn.querySelector('.apply-btn-spinner');
    var check = btn.querySelector('.apply-btn-check');

    if (state === 'loading') {
      btn.disabled = true;
      btn.classList.remove('apply-btn-success');

      if (label) {
        label.classList.add('d-none');
      }
      if (spinner) {
        spinner.classList.remove('d-none');
      }
      if (check) {
        check.classList.add('d-none');
      }
      return;
    }

    if (state === 'success') {
      // Giữ icon tick, KHÔNG quay lại chữ "Áp Dụng"
      btn.disabled = false;
      btn.classList.add('apply-btn-success');

      if (label) {
        label.classList.add('d-none');
      }
      if (spinner) {
        spinner.classList.add('d-none');
      }
      if (check) {
        check.classList.remove('d-none');
      }
      return;
    }

    // state === 'idle' => trạng thái mặc định (khi chưa dùng mã / đã xoá mã)
    btn.disabled = false;
    btn.classList.remove('apply-btn-success');

    if (label) {
      label.classList.remove('d-none');
    }
    if (spinner) {
      spinner.classList.add('d-none');
    }
    if (check) {
      check.classList.add('d-none');
    }
  }

  window.applyDiscount = function () {
    var inputEl = document.getElementById('discountCode');
    var msgEl = document.getElementById('discountMessage');
    var rootEl = document.getElementById('checkoutPage');

    if (!inputEl || !msgEl || !rootEl) {
      return;
    }

    var baseSubtotal = parseInt(rootEl.getAttribute('data-subtotal') || '0', 10);
    var baseShipping = parseInt(rootEl.getAttribute('data-shipping') || '0', 10);
    var code = inputEl.value.trim();

    msgEl.textContent = '';
    msgEl.classList.remove('discount-message-error');
    msgEl.classList.remove('discount-message-success');

    // ====== XOÁ MÃ (code rỗng) ======
    if (code === '') {
      var tokenDelete = document.querySelector('meta[name="csrf-token"]');
      var csrfDelete = tokenDelete ? tokenDelete.getAttribute('content') : '';

      setApplyButtonState('loading');

      fetch('/checkout/discount', {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfDelete,
          'Accept': 'application/json'
        }
      })
        .then(function (res) {
          return res.json().catch(function () {
            return { ok: false };
          });
        })
        .then(function (res) {
          setTotals(baseSubtotal, baseShipping, 0);

          var msg = res.ok
            ? (res.message || 'Đã xoá mã giảm giá.')
            : 'Đã xoá mã giảm giá.';
          msgEl.textContent = msg;
          msgEl.classList.remove('discount-message-error');
          msgEl.classList.add('discount-message-success');

          // Xoá mã => quay về trạng thái nút mặc định
          setApplyButtonState('idle');
        })
        .catch(function () {
          setTotals(baseSubtotal, baseShipping, 0);
          msgEl.textContent = 'Không thể xoá mã giảm giá. Vui lòng thử lại.';
          msgEl.classList.remove('discount-message-success');
          msgEl.classList.add('discount-message-error');
          setApplyButtonState('idle');
        });

      return;
    }

    // ====== ÁP DỤNG MÃ ======
    var token = document.querySelector('meta[name="csrf-token"]');
    var csrf = token ? token.getAttribute('content') : '';

    setApplyButtonState('loading');

    fetch('/checkout/apply-discount', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        code: code,
        subtotal: baseSubtotal,
        shipping: baseShipping
      })
    })
      .then(function (res) {
        return res
          .json()
          .then(function (json) {
            return { status: res.status, body: json };
          })
          .catch(function () {
            return { status: res.status, body: null };
          });
      })
      .then(function (res) {
        if (!res.body) {
          msgEl.textContent = 'Không thể áp dụng mã giảm giá. Vui lòng thử lại.';
          msgEl.classList.remove('discount-message-success');
          msgEl.classList.add('discount-message-error');

          setTotals(baseSubtotal, baseShipping, 0);
          setApplyButtonState('idle');
          return;
        }

        if (!res.body.ok) {
          msgEl.textContent = res.body.message || 'Mã giảm giá không hợp lệ.';
          msgEl.classList.remove('discount-message-success');
          msgEl.classList.add('discount-message-error');

          setTotals(baseSubtotal, baseShipping, 0);
          setApplyButtonState('idle');
          return;
        }

        var data = res.body.data || {};
        var newSubtotal = typeof data.subtotal_vnd === 'number'
          ? data.subtotal_vnd
          : baseSubtotal;
        var newShipping = typeof data.shipping_vnd === 'number'
          ? data.shipping_vnd
          : baseShipping;
        var discountVnd = (data.discount_vnd || 0) + (data.shipping_discount_vnd || 0);

        setTotals(newSubtotal, newShipping, discountVnd);

        msgEl.textContent = res.body.message || 'Áp dụng mã giảm giá thành công.';
        msgEl.classList.remove('discount-message-error');
        msgEl.classList.add('discount-message-success');

        // Giữ nút ở trạng thái tick xanh
        setApplyButtonState('success');
      })
      .catch(function () {
        msgEl.textContent = 'Có lỗi xảy ra khi áp dụng mã. Vui lòng thử lại.';
        msgEl.classList.remove('discount-message-success');
        msgEl.classList.add('discount-message-error');

        setTotals(baseSubtotal, baseShipping, 0);
        setApplyButtonState('idle');
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
