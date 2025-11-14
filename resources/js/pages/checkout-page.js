document.addEventListener('DOMContentLoaded', function () {
  function formatVND(n) {
    try { return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(n); }
    catch { return (n || 0).toLocaleString('vi-VN') + '₫'; }
  }

  var root = document.getElementById('checkoutRoot');
  if (!root) { return; }

  var sub = parseInt(root.getAttribute('data-subtotal') || '0', 10);
  var ship = parseInt(root.getAttribute('data-shipping') || '0', 10);

  function setTotals(currSub, currShip, discount) {
    document.getElementById('checkoutSubtotal').textContent = formatVND(currSub);
    document.getElementById('checkoutShipping').textContent = formatVND(currShip);
    document.getElementById('checkoutDiscount').textContent = '-' + formatVND(discount);
    document.getElementById('checkoutTotal').textContent = formatVND(Math.max(0, currSub - discount + currShip));

    var shipInput = document.getElementById('shipping_fee');
    var totalInput = document.getElementById('total_client');
    if (shipInput) { shipInput.value = String(currShip); }
    if (totalInput) { totalInput.value = String(Math.max(0, currSub - discount + currShip)); }
  }

  window.applyDiscount = function () {
    var code = (document.getElementById('discountCode').value || '').trim().toUpperCase();
    var msg = document.getElementById('discountMessage');
    var discount = 0;
    var currShip = ship;

    if (code === '') {
      document.getElementById('discount_code').value = '';
      if (msg) { msg.textContent = 'Đã xoá mã giảm giá.'; }
      setTotals(sub, currShip, 0);
      return;
    }
    if (code === 'HOMESTYLE10') {
      discount = Math.round(sub * 0.10);
      document.getElementById('discount_code').value = code;
      if (msg) { msg.textContent = 'Áp dụng mã HOMESTYLE10 thành công (-10%).'; }
      setTotals(sub, currShip, discount);
      return;
    }
    if (code === 'FREESHIP') {
      currShip = 0;
      document.getElementById('discount_code').value = code;
      if (msg) { msg.textContent = 'Áp dụng mã FREESHIP thành công (miễn phí vận chuyển).'; }
      setTotals(sub, currShip, 0);
      return;
    }
    if (msg) { msg.textContent = 'Mã không hợp lệ. Dùng: HOMESTYLE10 hoặc FREESHIP.'; }
  };

  window.placeOrder = function () {
    var need = ['full_name', 'phone', 'address', 'city', 'district', 'ward'];
    for (var i = 0; i < need.length; i++) {
      var el = document.querySelector('[name="'+need[i]+'"]');
      if (!el || !el.value.trim()) { alert('Vui lòng điền đầy đủ thông tin bắt buộc.'); return; }
    }
    var phone = document.querySelector('[name="phone"]').value.trim();
    if (!/^0[0-9]{9,10}$/.test(phone)) { alert('SĐT không hợp lệ.'); return; }

    document.getElementById('placeOrderForm').submit();
  };

  setTotals(sub, ship, 0);
});

document.addEventListener('DOMContentLoaded', function () {
  var options   = document.querySelectorAll('.js-payment-option');
  var methodInp = document.getElementById('paymentMethodInput');
  var submitBtn = document.getElementById('paymentSubmitButton');

  function updateButton(method) {
    if (!submitBtn) return;

    if (method === 'momo') {
      submitBtn.innerHTML = 'Thanh toán với MOMO';
    } else if (method === 'vnpay') {
      submitBtn.innerHTML = 'Thanh toán VNPAY';
    } else {
      submitBtn.innerHTML = '<i class="bi bi-truck"></i> Thanh toán COD';
    }
  }

  options.forEach(function (opt) {
    opt.addEventListener('click', function () {
      var method = opt.getAttribute('data-method') || 'cod';

      options.forEach(function (o) {
        o.classList.remove('selected');
        var r = o.querySelector('.radio-custom');
        var d = o.querySelector('.radio-dot');
        if (r) r.classList.remove('checked');
        if (d) d.classList.remove('show');
      });

      opt.classList.add('selected');
      var radio = opt.querySelector('.radio-custom');
      var dot   = opt.querySelector('.radio-dot');
      if (radio) radio.classList.add('checked');
      if (dot)   dot.classList.add('show');

      if (methodInp) methodInp.value = method;
      updateButton(method);
    });
  });

  if (methodInp) {
    updateButton(methodInp.value || 'cod');
  }
});


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

  cards.forEach(function (card) {
    card.addEventListener('click', function () {
      var id = card.getAttribute('data-id');
      if (!id) { return; }

      if (hiddenInput) {
        hiddenInput.value = id;
      }

      cards.forEach(function (c) {
        c.classList.remove('is-active');
      });
      card.classList.add('is-active');

      var text = card.getAttribute('data-text') || '';
      var note = card.getAttribute('data-note') || '';

      if (selectedTitle) {
        selectedTitle.textContent = text;
      }
      if (selectedBody) {
        selectedBody.textContent = text;
        if (note) {
          var br = document.createElement('br');
          var small = document.createElement('small');
          small.className = 'text-muted';
          small.textContent = 'Ghi chú: ' + note;
          selectedBody.appendChild(br);
          selectedBody.appendChild(small);
        }
      }

      if (window.bootstrap) {
        var modal = window.bootstrap.Modal.getInstance(modalEl)
          || window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
      }
    });
  });
})();
