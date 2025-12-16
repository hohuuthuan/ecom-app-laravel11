document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-claim-url], [data-remove-url]');
  if (!root) return;

  const claimUrl = root.getAttribute('data-claim-url') || '';
  const removeUrl = root.getAttribute('data-remove-url') || '';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const showNotification = (type, message) => {
    const sdk = window.ElementSDK || window.elementSDK || window.sdk;
    if (sdk && typeof sdk.toast === 'function') {
      sdk.toast({ type, message });
      return;
    }

    const old = document.querySelector('.notification');
    if (old) old.remove();

    const el = document.createElement('div');
    el.className = `notification ${type === 'success' ? 'success' : 'error'}`;
    el.innerHTML = `
      <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'}"></i>
      <div class="notification-content">
        <p class="notification-title">${type === 'success' ? 'Thành công' : 'Thất bại'}</p>
        <p class="notification-message">${message || ''}</p>
      </div>
    `;
    document.body.appendChild(el);

    setTimeout(() => {
      el.style.animation = 'slideOut 0.3s ease';
      setTimeout(() => el.remove(), 300);
    }, 2500);
  };

  const updateVoucherWalletCount = (delta) => {
    const el = document.getElementById('voucherWalletCount');
    if (!el) return;

    const n = parseInt((el.textContent || '0').replace(/[^\d]/g, ''), 10);
    const next = (Number.isFinite(n) ? n : 0) + delta;
    el.textContent = String(next < 0 ? 0 : next);
  };

  const setBtnLoading = (btn, loading, text) => {
    if (loading) {
      if (!btn.dataset.originalHtml) btn.dataset.originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span class="js-btn-text">${text}</span>`;
      return;
    }

    if (btn.dataset.originalHtml) btn.innerHTML = btn.dataset.originalHtml;
    btn.disabled = false;
  };

  const setBtnStateSaved = (btn) => {
    btn.classList.add('saved');
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-check-circle"></i><span class="js-btn-text">Đã lưu vào ví</span>`;
  };

  root.addEventListener('click', async (e) => {
    const saveBtn = e.target.closest('.js-voucher-save');
    if (saveBtn && claimUrl) {
      if (saveBtn.disabled) return;

      const code = (saveBtn.getAttribute('data-code') || saveBtn.dataset.code || '').trim();
      if (!code) {
        showNotification('error', 'Không tìm thấy mã voucher.');
        return;
      }

      setBtnLoading(saveBtn, true, 'Đang lưu...');

      try {
        const res = await fetch(claimUrl, {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
          },
          body: JSON.stringify({ code })
        });

        const data = await res.json().catch(() => null);

        if (res.ok && data && data.ok) {
          setBtnStateSaved(saveBtn);

          const msg = data.message || 'Đã lưu voucher vào ví.';
          if (!/đã có trong ví/i.test(msg)) {
            updateVoucherWalletCount(1);
          }

          showNotification('success', msg);
          return;
        }

        const msg =
          (data && data.message) ||
          (data && data.errors && (data.errors.code?.[0] || data.errors?.[0])) ||
          'Không thể lưu voucher.';

        setBtnLoading(saveBtn, false, '');
        showNotification('error', msg);
      } catch (err) {
        setBtnLoading(saveBtn, false, '');
        showNotification('error', 'Lỗi kết nối. Vui lòng thử lại.');
      }

      return;
    }

    const removeBtn = e.target.closest('.js-voucher-remove');
    if (removeBtn && removeUrl) {
      if (removeBtn.disabled) return;

      const discountId = (removeBtn.getAttribute('data-id') || removeBtn.dataset.id || '').trim();
      if (!discountId) {
        showNotification('error', 'Không tìm thấy voucher để xóa.');
        return;
      }

      removeBtn.disabled = true;

      try {
        const res = await fetch(removeUrl, {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
          },
          body: JSON.stringify({ discount_id: discountId })
        });

        const data = await res.json().catch(() => null);

        if (res.ok && data && data.ok) {
          const card = removeBtn.closest('.voucher-card');
          if (card) card.remove();

          updateVoucherWalletCount(-1);
          showNotification('success', data.message || 'Đã xóa voucher khỏi ví.');

          const grid = root.querySelector('.voucher-grid');
          if (grid && grid.children.length === 0) {
            grid.innerHTML = `
              <div class="empty-state">
                <i class="fas fa-ticket-alt"></i>
                <p>Ví voucher của bạn đang trống</p>
              </div>
            `;
          }

          return;
        }

        removeBtn.disabled = false;

        const msg =
          (data && data.message) ||
          (data && data.errors && (data.errors.discount_id?.[0] || data.errors?.[0])) ||
          'Không thể xóa voucher.';

        showNotification('error', msg);
      } catch (err) {
        removeBtn.disabled = false;
        showNotification('error', 'Lỗi kết nối. Vui lòng thử lại.');
      }
    }
  });
});
