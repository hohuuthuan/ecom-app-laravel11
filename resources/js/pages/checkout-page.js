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
        var calcTotal = Math.max(0, currSub - discount + currShip);
        totalEl.textContent = formatVND(calcTotal);
      }
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

  function hideWalletDiscountModal() {
    var el = document.getElementById('walletDiscountModal');
    if (!el || !window.bootstrap) {
      return;
    }

    var modal = window.bootstrap.Modal.getInstance(el)
      || window.bootstrap.Modal.getOrCreateInstance(el);

    modal.hide();
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

  function showNotification(message) {
    ensureNotificationStyle();

    var old = document.querySelector('.checkout-notification');
    if (old) {
      old.remove();
    }

    var n = document.createElement('div');
    n.className = 'checkout-notification';
    n.textContent = message;

    n.style.cssText =
      'position:fixed;top:20px;right:20px;background:#10b981;color:#fff;' +
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

  function copyText(text) {
    if (!text) {
      return Promise.reject(new Error('empty'));
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text);
    }

    return new Promise(function (resolve, reject) {
      var temp = document.createElement('textarea');
      temp.value = text;
      temp.style.position = 'fixed';
      temp.style.opacity = '0';
      document.body.appendChild(temp);
      temp.focus();
      temp.select();

      try {
        document.execCommand('copy');
        document.body.removeChild(temp);
        resolve();
      } catch (e) {
        document.body.removeChild(temp);
        reject(e);
      }
    });
  }

  var root = document.getElementById('checkoutPage');
  if (!root) {
    return;
  }

  var sub = parseInt(root.getAttribute('data-subtotal') || '0', 10);
  var ship = parseInt(root.getAttribute('data-shipping') || '0', 10);

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

        var discountDisplay = (data.discount_vnd || 0) + (data.shipping_discount_vnd || 0);

        setTotals(
          newSubtotal,
          newShipping,
          discountDisplay,
          data.total_vnd
        );

        msgEl.textContent = res.body.message || 'Áp dụng mã giảm giá thành công.';
        msgEl.classList.remove('discount-message-error');
        msgEl.classList.add('discount-message-success');

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

  document.addEventListener('click', function (e) {
    var copyBtn = e.target.closest('.js-wallet-discount-copy');
    if (copyBtn) {
      e.preventDefault();
      e.stopPropagation();

      var code = copyBtn.getAttribute('data-code') || '';
      copyText(code)
        .then(function () {
          showNotification('Đã copy mã: ' + code);
        })
        .catch(function () {
          showNotification('Không thể copy mã. Vui lòng thử lại.');
        });

      return;
    }

    var applyBtn = e.target.closest('.js-wallet-discount-apply');
    if (applyBtn) {
      e.preventDefault();
      e.stopPropagation();

      var codeApply = applyBtn.getAttribute('data-code') || '';
      var input = document.getElementById('discountCode');
      if (input && codeApply) {
        input.value = codeApply;
        showNotification('Đã chọn mã: ' + codeApply);
        window.applyDiscount();
        hideWalletDiscountModal();
      }

      return;
    }

    var card = e.target.closest('.js-wallet-discount-card');
    if (card) {
      var codeCard = card.getAttribute('data-code') || '';
      var inputCard = document.getElementById('discountCode');
      if (inputCard && codeCard) {
        inputCard.value = codeCard;
        showNotification('Đã chọn mã: ' + codeCard);
        window.applyDiscount();
        hideWalletDiscountModal();
      }
    }
  });

  setTotals(sub, ship, 0);
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

    var mainNode = document.createTextNode(fullText);
    selectedBody.appendChild(mainNode);

    if (note) {
      var br = document.createElement('br');
      var small = document.createElement('small');
      small.className = 'text-muted';
      small.textContent = 'Ghi chú: ' + note;
      selectedBody.appendChild(br);
      selectedBody.appendChild(small);
    }

    if (selectedTitle) {
      var titleText = fullText.split(',')[0].trim();
      selectedTitle.textContent = titleText;
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

      var fullText = card.getAttribute('data-text') || '';
      var note = card.getAttribute('data-note') || '';

      updateSelectedAddress(fullText, note);

      if (window.bootstrap) {
        var modal = window.bootstrap.Modal.getInstance(modalEl)
          || window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
      }
    });
  });
})();
