(() => {
  const btn = document.getElementById('backTop');
  const onScroll = () => {
    if (window.scrollY > 220) {
      btn.classList.add('show');
    } else {
      btn.classList.remove('show');
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
})();

// Horizontal scrollers controls
(() => {
  const nextBtns = document.querySelectorAll('.sc-next');
  const prevBtns = document.querySelectorAll('.sc-prev');

  const scrollByAmount = (el, dir = 1) => {
    const amount = Math.max(240, el.clientWidth * 0.8);
    el.scrollBy({ left: amount * dir, behavior: 'smooth' });
  };

  const bind = (btns, dir) => {
    btns.forEach(btn => {
      btn.addEventListener('click', () => {
        const targetSel = btn.getAttribute('data-target');
        const target = document.querySelector(targetSel);
        if (target) { scrollByAmount(target, dir); }
      });
    });
  };

  bind(nextBtns, +1);
  bind(prevBtns, -1);
})();

// Add-to-cart demo (UX feedback only)
(() => {
  const toastContainer = document.createElement('div');
  toastContainer.className = 'position-fixed top-0 start-50 translate-middle-x p-3';
  toastContainer.style.zIndex = '1080';
  document.body.appendChild(toastContainer);

  const notify = (title) => {
    const toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center text-bg-success border-0 show';
    toastEl.setAttribute('role', 'status');
    toastEl.setAttribute('aria-live', 'polite');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = `
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-check2-circle me-2"></i>Đã thêm <strong>${title}</strong> vào giỏ</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Đóng"></button>
      </div>`;
    toastContainer.appendChild(toastEl);
    setTimeout(() => toastEl.remove(), 2200);
  };

  document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const card = e.currentTarget.closest('.product');
      const title = card?.querySelector('.product-title')?.textContent?.trim() || 'sản phẩm';
      notify(title);
    });
  });
})();
