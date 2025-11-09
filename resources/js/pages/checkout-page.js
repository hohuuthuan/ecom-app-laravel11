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
