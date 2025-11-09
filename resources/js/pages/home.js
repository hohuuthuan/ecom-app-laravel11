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


/* =============================== FAVORITE PRODUCT =============================== */
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const loginUrl = '/login';

  async function api(url, { method = 'POST', body = null } = {}) {
    const opt = { method, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } };
    if (body && method !== 'DELETE') { opt.headers['Content-Type'] = 'application/json'; opt.body = JSON.stringify(body); }
    const res = await fetch(url, opt);
    if (res.status === 401) { window.location.href = loginUrl; return { ok: false, status: 401 }; }
    let data = null; try { data = await res.json(); } catch { }
    return { ok: res.ok && (data?.ok ?? true), status: res.status, data };
  }

  function bumpWishlist(delta) {
    const el = document.getElementById('wishlistCount'); if (!el) return;
    const n = parseInt(String(el.textContent).replace(/[^\d]/g, '')) || 0;
    el.textContent = String(Math.max(0, n + delta));
  }
  function setWishlist(count) {
    const el = document.getElementById('wishlistCount'); if (!el) return;
    el.textContent = String(Math.max(0, parseInt(count) || 0));
  }

  function setBtnState(btn, on) {
    btn.classList.toggle('btn-danger', on);
    btn.classList.toggle('btn-outline-danger', !on);
    const icon = btn.querySelector('i');
    if (icon) { icon.className = on ? 'fas fa-heart me-2' : 'far fa-heart me-2'; }
    const label = btn.querySelector('.js-fav-label');
    if (label) { label.textContent = on ? 'Bỏ thích' : 'Yêu thích'; }
    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
  }

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-fav-toggle'); if (!btn) return;
    const id = btn.dataset.id;
    const addUrl = btn.dataset.addUrl;
    const delUrl = btn.dataset.delUrl?.replace('__ID__', id);
    const isOn = btn.getAttribute('aria-pressed') === 'true';

    btn.disabled = true;
    try {
      if (!isOn) {
        const { ok, data } = await api(addUrl, { method: 'POST', body: { product_id: id } });
        if (ok) { setBtnState(btn, true); if (data?.count !== undefined) setWishlist(data.count); else bumpWishlist(+1); }
      } else {
        const { ok, data } = await api(delUrl, { method: 'DELETE' });
        if (ok) { setBtnState(btn, false); if (data?.count !== undefined) setWishlist(data.count); else bumpWishlist(-1); }
      }
    } finally {
      btn.disabled = false;
    }
  });
})();
/* ============================ END FAVORITE PRODUCT ============================ */


/* ========================= CHANGE QUANTITY (PRODUCT DETAIL) ========================= */
(function () {
  const box = document.getElementById('qtyBox');
  const input = document.getElementById('quantity');
  if (!box || !input) return;

  function getBounds() {
    const min = parseInt(input.getAttribute('min') || '1', 10);
    const maxAttr = input.getAttribute('max');
    const hasMax = maxAttr !== null && maxAttr !== '';
    let max = hasMax ? parseInt(maxAttr, 10) : Infinity;
    if (!Number.isFinite(max) || max < min) max = Infinity;
    return { min, max, hasMax };
  }
  function clamp(n, min, max) { return Number.isFinite(max) ? Math.min(Math.max(n, min), max) : Math.max(n, min); }
  function syncButtons() {
    const { min, max, hasMax } = getBounds();
    const val = parseInt(input.value, 10);
    const curr = Number.isFinite(val) ? val : min;
    const btns = box.querySelectorAll('button[data-delta]');
    if (btns[0]) btns[0].disabled = curr <= min;
    if (btns[1]) btns[1].disabled = hasMax && Number.isFinite(max) && curr >= max;
  }
  function applyClamp() {
    const { min, max } = getBounds();
    const raw = parseInt(input.value, 10);
    const curr = Number.isFinite(raw) ? raw : min;
    const next = clamp(curr, min, max);
    if (next !== curr) {
      input.value = String(next);
      input.dispatchEvent(new Event('change', { bubbles: true }));
      input.dispatchEvent(new Event('input', { bubbles: true }));
    }
    syncButtons();
  }
  function bump(delta) {
    const { min, max } = getBounds();
    const val = parseInt(input.value, 10);
    const curr = Number.isFinite(val) ? val : min;
    const next = clamp(curr + Number(delta), min, max);
    if (next !== curr) {
      input.value = String(next);
      input.dispatchEvent(new Event('change', { bubbles: true }));
      input.dispatchEvent(new Event('input', { bubbles: true }));
    }
    syncButtons();
  }

  box.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-delta]'); if (!btn) return;
    bump(btn.getAttribute('data-delta'));
  });
  input.addEventListener('input', applyClamp);
  input.addEventListener('blur', applyClamp);
  applyClamp();
})();
/* ======================= END CHANGE QUANTITY (PRODUCT DETAIL) ======================= */


/* ======================= SYNC ADD-TO-CART (COPY QTY ON SUBMIT) ====================== */
(function () {
  const form = document.getElementById('addToCartForm');
  if (!form) return;
  form.addEventListener('submit', function () {
    const qtyInput = document.getElementById('quantity');
    const hiddenQty = document.getElementById('addToCartQty');
    if (qtyInput && hiddenQty) {
      const n = Math.max(1, parseInt(qtyInput.value || '1', 10));
      hiddenQty.value = String(n);
    }
  });
})();

/* ===================== COUNT CART ITEMS (SIMPLE & SAFE) ===================== */
(function () {
  var badge = document.getElementById('cartCount');
  if (!badge) { return; }

  var COUNT_URL = badge.getAttribute('data-count-url') || '/cart/count';

  function set(n) {
    badge.textContent = String(Math.max(0, n | 0));
  }

  function sync() {
    fetch(COUNT_URL, {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
    .then(function (r) { return r.ok ? r.json() : null; })
    .then(function (d) { if (d && typeof d.count === 'number') { set(d.count); } })
    .catch(function () { });
  }

  document.addEventListener('DOMContentLoaded', sync);
  window.addEventListener('pageshow', sync);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) { sync(); }
  });
})();

