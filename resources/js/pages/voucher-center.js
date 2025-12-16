document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-claim-url]');
  if (!root) {
    return;
  }

  const claimUrl = root.getAttribute('data-claim-url');
  if (!claimUrl) {
    return;
  }

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const showToast = (type, message) => {
    const sdk = window.ElementSDK || window.elementSDK || window.sdk;
    if (sdk && typeof sdk.toast === 'function') {
      sdk.toast({ type, message });
      return;
    }

    const existing = document.querySelector('.notification');
    if (existing) {
      existing.remove();
    }

    const notification = document.createElement('div');
    notification.className = 'notification ' + (type === 'success' ? 'success' : 'error');
    notification.innerHTML = `
      <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'}"></i>
      <div class="notification-content">
        <p class="notification-title">${type === 'success' ? 'Thành công' : 'Thất bại'}</p>
        <p class="notification-message">${message || ''}</p>
      </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.animation = 'slideOut 0.3s ease';
      setTimeout(() => {
        notification.remove();
      }, 300);
    }, 3000);
  };

  const getVoucherCodeFromCard = (btn) => {
    const direct = (btn.getAttribute('data-code') || btn.dataset.code || '').trim();
    if (direct) {
      return direct;
    }

    const card = btn.closest('[data-code], .voucher-card, .voucher-item');
    const cardCode = (card?.getAttribute('data-code') || card?.dataset?.code || '').trim();
    if (cardCode) {
      return cardCode;
    }

    const codeEl = card ? card.querySelector('.voucher-code, .voucher-code-text, [data-voucher-code]') : null;
    const text = (codeEl?.textContent || '').trim();
    return text;
  };

  const setButtonLoading = (btn, loading) => {
    if (loading) {
      if (!btn.dataset.originalHtml) {
        btn.dataset.originalHtml = btn.innerHTML;
      }
      btn.disabled = true;
      btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Đang lưu...</span>`;
      return;
    }

    btn.innerHTML = btn.dataset.originalHtml || btn.innerHTML;
    btn.disabled = false;
  };

  const setButtonSaved = (btn) => {
    btn.classList.add('saved');
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-check-circle"></i><span>Đã lưu vào ví</span>`;

    const card = btn.closest('.voucher-card, .voucher-item');
    if (card) {
      const note = card.querySelector('.voucher-note, .voucher-disabled-reason, .note-disabled');
      if (note) {
        note.textContent = 'Đã lưu vào ví.';
        note.classList.remove('text-danger');
        note.classList.add('text-success');
      }
    }
  };

  root.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-voucher-save');
    if (!btn) {
      return;
    }

    if (btn.disabled) {
      return;
    }

    const code = getVoucherCodeFromCard(btn);
    if (!code) {
      showToast('error', 'Không tìm thấy mã voucher.');
      return;
    }

    setButtonLoading(btn, true);

    try {
      const res = await fetch(claimUrl, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
        },
        body: JSON.stringify({ code })
      });

      const data = await res.json().catch(() => null);

      if (res.ok && data && data.ok) {
        setButtonSaved(btn);
        showToast('success', data.message || 'Đã lưu voucher vào ví.');
        return;
      }

      const message =
        (data && data.message) ||
        (data && data.errors && (data.errors.code?.[0] || data.errors?.[0])) ||
        'Không thể lưu voucher.';

      setButtonLoading(btn, false);
      showToast('error', message);
    } catch (err) {
      setButtonLoading(btn, false);
      showToast('error', 'Lỗi kết nối. Vui lòng thử lại.');
    }
  });
});
