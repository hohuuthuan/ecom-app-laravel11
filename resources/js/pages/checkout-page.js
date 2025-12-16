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

  function setTotals(currSub, currShip, discount, total) {
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
      if (typeof total === 'number') {
        totalEl.textContent = formatVND(Math.max(0, total));
      } else {
        totalEl.textContent = formatVND(Math.max(0, currSub - discount + currShip));
      }
    }
  }

  var DISCOUNT_STATE_KEY = 'checkout_discount_state_v1';

  function saveDiscountState(state) {
    try {
      sessionStorage.setItem(DISCOUNT_STATE_KEY, JSON.stringify(state));
    } catch (e) {}
  }

  function readDiscountState() {
    try {
      var raw = sessionStorage.getItem(DISCOUNT_STATE_KEY);
      if (!raw) {
        return null;
      }
      return JSON.parse(raw);
    } catch (e) {
      return null;
    }
  }

  function clearDiscountState() {
    try {
      sessionStorage.removeItem(DISCOUNT_STATE_KEY);
    } catch (e) {}
  }

  function setDiscountMessage(msg, isError) {
    var msgEl = document.getElementById('discountMessage');
    if (!msgEl) {
      return;
    }

    msgEl.textContent = msg || '';
    msgEl.classList.remove('discount-message-error');
    msgEl.classList.remove('discount-message-success');

    if (!msg) {
      return;
    }

    msgEl.classList.add(isError ? 'discount-message-error' : 'discount-message-success');
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

  function setApplyBtnTextAndMode(text, mode) {
    var btn = document.getElementById('discountApplyButton');
    if (!btn) {
      return;
    }

    btn.dataset.mode = mode;

    var label = btn.querySelector('.apply-btn-label');
    var spinner = btn.querySelector('.apply-btn-spinner');
    var check = btn.querySelector('.apply-btn-check');

    btn.disabled = false;
    btn.classList.remove('apply-btn-success');

    if (label) {
      label.textContent = text;
      label.classList.remove('d-none');
    }
    if (spinner) {
      spinner.classList.add('d-none');
    }
    if (check) {
      check.classList.add('d-none');
    }
  }

  function ensureNotificationStyle() {
    if (document.getElementById('checkoutNotificationStyle')) {
      return;
    }

    var style = document.createElement('style');
    style.id = 'checkoutNotificationStyle';
    style.textContent = '@keyframes checkoutSlideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}';
    document.head.appendChild(style);
  }

  function showNotification(message, isError) {
    ensureNotificationStyle();

    var old = document.querySelector('.checkout-notification');
    if (old) {
      old.remove();
    }

    var n = document.createElement('div');
    n.className = 'checkout-notification';
    n.textContent = message;

    n.style.cssText =
      'position:fixed;top:20px;right:20px;background:' + (isError ? '#ef4444' : '#10b981') + ';color:#fff;' +
      'padding:14px 18px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);' +
      'z-index:9999;font-weight:600;animation:checkoutSlideIn 0.3s ease;';

    document.body.appendChild(n);

    setTimeout(function () {
      n.style.animation = 'checkoutSlideIn 0.3s ease reverse';
      setTimeout(function () {
        n.remove();
      }, 300);
    }, 2500);
  }

  function getCsrf() {
    var token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
  }

  function hideWalletDiscountModal() {
    var el = document.getElementById('walletDiscountModal');
    if (!el || !window.bootstrap) {
      return;
    }

    var modal = window.bootstrap.Modal.getInstance(el)
      || window.bootstrap.Modal.getOrCreateInstance(el);

    modal.hide();
  }

  function createWalletButtonController(btn) {
    if (!btn) {
      return null;
    }

    var labelEl = btn.querySelector('.js-wallet-btn-text')
      || btn.querySelector('#walletDiscountBtnText')
      || btn.querySelector('#buttonText');

    var textNode = null;

    if (!labelEl) {
      for (var i = 0; i < btn.childNodes.length; i++) {
        var node = btn.childNodes[i];
        if (node && node.nodeType === 3 && (node.nodeValue || '').trim() !== '') {
          textNode = node;
          break;
        }
      }
    }

    if (!btn.dataset.defaultText) {
      if (labelEl) {
        btn.dataset.defaultText = (labelEl.textContent || '').trim();
      } else if (textNode) {
        btn.dataset.defaultText = (textNode.nodeValue || '').trim();
      }
    }

    if (!btn.dataset.selectedText) {
      btn.dataset.selectedText = 'Chọn lại';
    }

    function setSelected(isSelected) {
      var nextText = isSelected ? btn.dataset.selectedText : btn.dataset.defaultText;
      if (!nextText) {
        return;
      }

      if (labelEl) {
        labelEl.textContent = nextText;
      } else if (textNode) {
        textNode.nodeValue = ' ' + nextText;
      }

      btn.classList.toggle('is-selected', !!isSelected);
    }

    return { setSelected: setSelected };
  }

  function restoreDiscountState() {
    var st = readDiscountState();
    if (!st || !st.code || st.mode !== 'clear') {
      return false;
    }

    var inputEl = document.getElementById('discountCode');
    if (inputEl) {
      inputEl.value = st.code;
    }

    setTotals(st.subtotal || 0, st.shipping || 0, st.discount || 0, st.total);
    setApplyBtnTextAndMode('Xóa mã', 'clear');
    setDiscountMessage('', false);

    if (walletBtnCtl) {
      walletBtnCtl.setSelected(true);
    }

    return true;
  }

  function clearDiscount() {
    var inputEl = document.getElementById('discountCode');
    var rootEl = document.getElementById('checkoutPage');

    if (!inputEl || !rootEl) {
      return;
    }

    var baseSubtotal = parseInt(rootEl.getAttribute('data-subtotal') || '0', 10);
    var baseShipping = parseInt(rootEl.getAttribute('data-shipping') || '0', 10);

    setDiscountMessage('', false);
    setApplyButtonState('loading');

    fetch('/checkout/discount', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': getCsrf(),
        'Accept': 'application/json'
      }
    })
      .then(function (res) {
        return res.json().catch(function () {
          return { ok: true, message: 'Đã xóa mã giảm giá.' };
        });
      })
      .then(function (res) {
        inputEl.value = '';
        setTotals(baseSubtotal, baseShipping, 0, baseSubtotal + baseShipping);

        clearDiscountState();

        if (walletBtnCtl) {
          walletBtnCtl.setSelected(false);
        }

        showNotification((res && res.message) ? res.message : 'Đã xóa mã giảm giá.', false);
        setApplyBtnTextAndMode('Áp Dụng', 'apply');
      })
      .catch(function () {
        setApplyBtnTextAndMode('Xóa mã', 'clear');
        setDiscountMessage('Không thể xóa mã. Vui lòng thử lại.', true);
        showNotification('Không thể xóa mã. Vui lòng thử lại.', true);
      });
  }

  var root = document.getElementById('checkoutPage');
  if (!root) {
    return;
  }

  var walletBtn = document.querySelector('.open-modal-voucher-walet-btn')
    || document.querySelector('[data-bs-target="#walletDiscountModal"]');

  var walletBtnCtl = createWalletButtonController(walletBtn);

  var sub = parseInt(root.getAttribute('data-subtotal') || '0', 10);
  var ship = parseInt(root.getAttribute('data-shipping') || '0', 10);

  var discountInput = document.getElementById('discountCode');
  if (discountInput) {
    discountInput.addEventListener('input', function () {
      setApplyButtonState('idle');
      setDiscountMessage('', false);

      clearDiscountState();

      var btn = document.getElementById('discountApplyButton');
      if (btn && btn.dataset.mode === 'clear') {
        setApplyBtnTextAndMode('Áp Dụng', 'apply');
      }

      if (walletBtnCtl) {
        walletBtnCtl.setSelected(discountInput.value.trim() !== '');
      }
    });
  }

  var applyBtn = document.getElementById('discountApplyButton');
  if (applyBtn && !applyBtn.dataset.mode) {
    applyBtn.dataset.mode = 'apply';
  }

  window.applyDiscount = function () {
    var btn = document.getElementById('discountApplyButton');
    if (btn && btn.dataset.mode === 'clear') {
      clearDiscount();
      return;
    }

    var inputEl = document.getElementById('discountCode');
    var rootEl = document.getElementById('checkoutPage');

    if (!inputEl || !rootEl) {
      return;
    }

    var baseSubtotal = parseInt(rootEl.getAttribute('data-subtotal') || '0', 10);
    var baseShipping = parseInt(rootEl.getAttribute('data-shipping') || '0', 10);
    var code = inputEl.value.trim();

    setDiscountMessage('', false);

    if (code === '') {
      setApplyButtonState('idle');
      setDiscountMessage('Cần phải nhập mã giảm giá.', true);
      inputEl.focus();
      return;
    }

    setApplyButtonState('loading');

    fetch('/checkout/apply-discount', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrf(),
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
          setTotals(baseSubtotal, baseShipping, 0, baseSubtotal + baseShipping);
          setApplyButtonState('idle');
          setDiscountMessage('Không thể áp dụng mã giảm giá. Vui lòng thử lại.', true);
          showNotification('Không thể áp dụng mã giảm giá. Vui lòng thử lại.', true);
          return;
        }

        if (!res.body.ok) {
          setTotals(baseSubtotal, baseShipping, 0, baseSubtotal + baseShipping);
          setApplyButtonState('idle');

          var errMsg = res.body.message || 'Mã giảm giá không hợp lệ.';
          setDiscountMessage(errMsg, true);
          showNotification(errMsg, true);
          return;
        }

        var data = res.body.data || {};
        var newSubtotal = typeof data.subtotal_vnd === 'number' ? data.subtotal_vnd : baseSubtotal;
        var newShipping = typeof data.shipping_vnd === 'number' ? data.shipping_vnd : baseShipping;
        var discountDisplay = (data.discount_vnd || 0) + (data.shipping_discount_vnd || 0);
        var finalTotal = typeof data.total_vnd === 'number'
          ? data.total_vnd
          : Math.max(0, newSubtotal - discountDisplay + newShipping);

        setTotals(newSubtotal, newShipping, discountDisplay, finalTotal);

        if (walletBtnCtl) {
          walletBtnCtl.setSelected(true);
        }

        var okMsg = res.body.message || 'Áp dụng mã giảm giá thành công.';
        setDiscountMessage(okMsg, false);
        showNotification(okMsg, false);

        setApplyButtonState('success');
        setTimeout(function () {
          setApplyBtnTextAndMode('Xóa mã', 'clear');

          saveDiscountState({
            mode: 'clear',
            code: code,
            subtotal: newSubtotal,
            shipping: newShipping,
            discount: discountDisplay,
            total: finalTotal
          });
        }, 700);
      })
      .catch(function () {
        setTotals(baseSubtotal, baseShipping, 0, baseSubtotal + baseShipping);
        setApplyButtonState('idle');
        setDiscountMessage('Có lỗi xảy ra khi áp dụng mã. Vui lòng thử lại.', true);
        showNotification('Có lỗi xảy ra khi áp dụng mã. Vui lòng thử lại.', true);
      });
  };

  document.addEventListener('click', function (e) {
    var modalApply = e.target.closest('.js-wallet-discount-apply');
    if (!modalApply) {
      return;
    }

    e.preventDefault();
    e.stopPropagation();

    var codeApply = modalApply.getAttribute('data-code') || '';
    var input = document.getElementById('discountCode');

    if (input && codeApply) {
      setApplyBtnTextAndMode('Áp Dụng', 'apply');
      input.value = codeApply;

      if (walletBtnCtl) {
        walletBtnCtl.setSelected(true);
      }

      window.applyDiscount();
      hideWalletDiscountModal();
    }
  });

  if (!restoreDiscountState()) {
    setTotals(sub, ship, 0, sub + ship);
  }

  window.addEventListener('pageshow', function () {
    restoreDiscountState();
  });
});

// ================== CHỌN PHƯƠNG THỨC THANH TOÁN ==================
document.addEventListener('DOMContentLoaded', function () {
  var options = document.querySelectorAll('.js-payment-option');
  var methodInp = document.getElementById('paymentMethodInput');
  var submitBtn = document.getElementById('paymentSubmitButton');

  function updateButton(method) {
    if (!submitBtn) {
      return;
    }

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
        if (r) {
          r.classList.remove('checked');
        }
        if (d) {
          d.classList.remove('show');
        }
      });

      opt.classList.add('selected');
      var radio = opt.querySelector('.radio-custom');
      var dot = opt.querySelector('.radio-dot');
      if (radio) {
        radio.classList.add('checked');
      }
      if (dot) {
        dot.classList.add('show');
      }

      if (methodInp) {
        methodInp.value = method;
      }
      updateButton(method);
    });
  });

  if (methodInp) {
    updateButton(methodInp.value || 'cod');
  }
});

// ================== CHỌN LẠI ĐỊA CHỈ ==================
(function () {
  var modalEl = document.getElementById('selectAddressModal');
  if (!modalEl) {
    return;
  }

  var cards = modalEl.querySelectorAll('.js-address-select-card');
  var hiddenInput = document.getElementById('shippingAddressId');
  var selectedCard = document.getElementById('selectedAddressCard');
  var selectedBody = document.getElementById('selectedAddressBody');
  var selectedTitle = selectedCard
    ? selectedCard.querySelector('.selected-address-text')
    : null;

  function updateSelectedAddress(fullText, note) {
    if (!selectedBody) {
      return;
    }

    selectedBody.innerHTML = '';
    selectedBody.appendChild(document.createTextNode(fullText));

    if (note) {
      var br = document.createElement('br');
      var small = document.createElement('small');
      small.className = 'text-muted';
      small.textContent = 'Ghi chú: ' + note;
      selectedBody.appendChild(br);
      selectedBody.appendChild(small);
    }

    if (selectedTitle) {
      selectedTitle.textContent = fullText.split(',')[0].trim();
    }
  }

  cards.forEach(function (card) {
    card.addEventListener('click', function () {
      var id = card.getAttribute('data-id');
      if (!id) {
        return;
      }

      if (hiddenInput) {
        hiddenInput.value = id;
      }

      cards.forEach(function (c) {
        c.classList.remove('is-active');
      });
      card.classList.add('is-active');

      updateSelectedAddress(card.getAttribute('data-text') || '', card.getAttribute('data-note') || '');

      if (window.bootstrap) {
        (window.bootstrap.Modal.getInstance(modalEl) || window.bootstrap.Modal.getOrCreateInstance(modalEl)).hide();
      }
    });
  });
})();
