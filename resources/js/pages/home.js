/*  ====================================================================== HOME ANIMATE NUMBER ====================================================================== */
document.addEventListener('DOMContentLoaded', () => {
  animateStats();
});

function animateStats() {
  const nodes = document.querySelectorAll('.stat-number');
  if (!nodes.length) return;

  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const run = (el) => {
    const target = parseNumber(el.getAttribute('data-target'));
    const duration = parseInt(el.getAttribute('data-duration') || '1200', 10);
    if (!Number.isFinite(target) || target <= 0 || reduce) {
      el.textContent = formatNumber(target);
      return;
    }
    animateNumber(el, target, duration);
  };

  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        run(entry.target);
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.2 });

  nodes.forEach(n => io.observe(n));
}

function animateNumber(el, target, duration) {
  const start = performance.now();
  const from = 0;

  function tick(now) {
    const elapsed = now - start;
    const p = Math.min(elapsed / duration, 1);
    const eased = 1 - Math.pow(1 - p, 3);
    const value = from + (target - from) * eased;
    el.textContent = formatNumber(value);
    if (p < 1) requestAnimationFrame(tick);
    else el.textContent = formatNumber(target);
  }

  requestAnimationFrame(tick);
}

// Helpers
function parseNumber(s) {
  if (!s) return 0;
  return Number(String(s).replace(/[^\d.-]/g, '')) || 0;
}

function formatNumber(n) {
  return Math.floor(n).toLocaleString('vi-VN');
}
/*  ====================================================================== END HOME ANIMATE NUMBER ====================================================================== */



/*  ====================================================================== FAVORITE PRODUCT ====================================================================== */
(function () {
  // === Config ===
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const loginUrl = '/login'; // chỉnh nếu route đăng nhập khác

  // === Helpers ===
  async function api(url, { method = 'POST', body = null } = {}) {
    const opt = {
      method,
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf
      }
    };
    if (body && method !== 'DELETE') {
      opt.headers['Content-Type'] = 'application/json';
      opt.body = JSON.stringify(body);
    }
    const res = await fetch(url, opt);
    if (res.status === 401) { window.location.href = loginUrl; return { ok: false, status: 401 }; }
    let data = null;
    try { data = await res.json(); } catch { }
    return { ok: res.ok && (data?.ok ?? true), status: res.status, data };
  }

  function bumpWishlist(delta) {
    const el = document.getElementById('wishlistCount');
    if (!el) return;
    const n = parseInt(String(el.textContent).replace(/[^\d]/g, '')) || 0;
    el.textContent = String(Math.max(0, n + delta));
  }
  function setWishlist(count) {
    const el = document.getElementById('wishlistCount');
    if (!el) return;
    el.textContent = String(Math.max(0, parseInt(count) || 0));
  }

  function setBtnState(btn, favOn) {
    btn.classList.toggle('btn-danger', favOn);
    btn.classList.toggle('btn-outline-danger', !favOn);
    const icon = btn.querySelector('i');
    if (icon) icon.className = favOn ? 'bi bi-heart-fill' : 'bi bi-heart';
    const label = btn.querySelector('.js-fav-label');
    if (label) label.textContent = favOn ? 'Bỏ thích' : 'Thích';
    btn.setAttribute('aria-pressed', favOn ? 'true' : 'false');
  }

  // === Events ===
  document.addEventListener('click', async (e) => {
    // Toggle on product cards
    const toggleBtn = e.target.closest('.js-fav-toggle');
    if (toggleBtn) {
      const id = toggleBtn.dataset.id;
      const addUrl = toggleBtn.dataset.addUrl;
      const delUrlTpl = toggleBtn.dataset.delUrl; // chứa "__ID__"
      const isFav = toggleBtn.getAttribute('aria-pressed') === 'true';

      toggleBtn.disabled = true;
      try {
        if (!isFav) {
          const { ok, data } = await api(addUrl, { method: 'POST', body: { product_id: id } });
          if (ok) {
            setBtnState(toggleBtn, true);
            if (data?.count !== undefined) setWishlist(data.count); else bumpWishlist(+1);
          }
        } else {
          const delUrl = delUrlTpl.replace('__ID__', id);
          const { ok, data } = await api(delUrl, { method: 'DELETE' });
          if (ok) {
            setBtnState(toggleBtn, false);
            if (data?.count !== undefined) setWishlist(data.count); else bumpWishlist(-1);
          }
        }
      } finally {
        toggleBtn.disabled = false;
      }
      return;
    }

    // Remove on favorites page
    const rmBtn = e.target.closest('.js-fav-remove');
    if (rmBtn) {
      const id = rmBtn.dataset.id;
      const delUrl = rmBtn.dataset.delUrl.replace('__ID__', id);
      rmBtn.disabled = true;
      try {
        const { ok, data } = await api(delUrl, { method: 'DELETE' });
        if (ok) {
          const card = rmBtn.closest('.card');
          if (card) card.remove();
          if (data?.count !== undefined) setWishlist(data.count); else bumpWishlist(-1);
          // nếu hết mục, có thể hiển thị empty-state hoặc reload
          if (!document.querySelector('.js-fav-remove')) location.reload();
        }
      } finally {
        rmBtn.disabled = false;
      }
    }
  });
})();
/*  ===================================================================== END FAVORITE PRODUCT =================================================================== */