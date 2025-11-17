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
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrf = csrfMeta ? csrfMeta.getAttribute('content') || '' : '';

  function showLoginRequiredModal() {
    const el = document.getElementById('loginRequiredModal');
    if (!el || !window.bootstrap || !bootstrap.Modal) {
      return;
    }
    const modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.show();
  }

  async function api(url, options) {
    const method = options && options.method ? options.method : 'POST';
    let body = options && options.body ? options.body : null;

    const headers = { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
    if (body && method !== 'DELETE') {
      headers['Content-Type'] = 'application/json';
      body = JSON.stringify(body);
    }

    const res = await fetch(url, { method: method, headers: headers, body: body });
    let data = null;
    try { data = await res.json(); } catch { }

    const okFlag = data && typeof data.ok === 'boolean' ? data.ok : res.ok;
    return { ok: okFlag, status: res.status, data: data };
  }

  function bumpWishlist(delta) {
    const el = document.getElementById('wishlistCount');
    if (!el) { return; }
    const current = parseInt(String(el.textContent).replace(/[^\d]/g, '')) || 0;
    el.textContent = String(Math.max(0, current + delta));
  }

  function setWishlist(count) {
    const el = document.getElementById('wishlistCount');
    if (!el) { return; }
    el.textContent = String(Math.max(0, parseInt(count) || 0));
  }

  function setBtnState(btn, on) {
    btn.classList.toggle('btn-danger', on);
    btn.classList.toggle('btn-outline-danger', !on);

    const icon = btn.querySelector('i');
    if (icon) {
      icon.className = on ? 'fas fa-heart me-2' : 'far fa-heart me-2';
    }

    const label = btn.querySelector('.js-fav-label');
    if (label) {
      label.textContent = on ? 'Bỏ thích' : 'Yêu thích';
    }

    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
  }

  document.addEventListener('click', async function (e) {
    // Guest: chỉ hiện modal
    const guestBtn = e.target.closest('.js-fav-login-required');
    if (guestBtn) {
      e.preventDefault();
      showLoginRequiredModal();
      return;
    }

    // User đã login: xử lý toggle
    const btn = e.target.closest('.js-fav-toggle');
    if (!btn) { return; }

    const id = btn.dataset.id;
    const addUrl = btn.dataset.addUrl;
    const rawDelUrl = btn.dataset.delUrl || '';
    const delUrl = rawDelUrl.replace('__ID__', id);
    const isOn = btn.getAttribute('aria-pressed') === 'true';

    if (!id || (!addUrl && !delUrl)) { return; }

    btn.disabled = true;
    try {
      if (!isOn) {
        const res = await api(addUrl, { method: 'POST', body: { product_id: id } });
        if (res.ok) {
          setBtnState(btn, true);
          if (res.data && res.data.count !== undefined) {
            setWishlist(res.data.count);
          } else {
            bumpWishlist(1);
          }
        }
      } else {
        const res = await api(delUrl, { method: 'DELETE' });
        if (res.ok) {
          setBtnState(btn, false);
          if (res.data && res.data.count !== undefined) {
            setWishlist(res.data.count);
          } else {
            bumpWishlist(-1);
          }
        }
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
/* ======================= LOAD IMAGE + SORT/FILTER/PAGINATION ======================= */
document.addEventListener('DOMContentLoaded', function () {
  // ================== HÀM KHỞI TẠO SKELETON ẢNH ==================
  function initBookCovers(root) {
    var scope = root || document;
    var imgs = scope.querySelectorAll('.book-cover .book-cover-img');

    imgs.forEach(function (img) {
      if (img.dataset.skeletonBound === '1') {
        return;
      }
      img.dataset.skeletonBound = '1';

      function markLoaded() {
        var wrapper = img.closest('.book-cover');
        if (wrapper && !wrapper.classList.contains('is-loaded')) {
          wrapper.classList.add('is-loaded');
        }
      }

      if (img.complete && img.naturalWidth > 0) {
        markLoaded();
      } else {
        img.addEventListener('load', markLoaded, { once: true });
        img.addEventListener('error', markLoaded, { once: true });
      }
    });
  }

  // Gọi lần đầu cho DOM hiện tại
  initBookCovers(document);

  // ================== PHẦN SORT / FILTER / PAGINATION ==================
  var productListWrapper = document.getElementById('product-list-wrapper');
  var sortBySelect = document.getElementById('sort_by');
  var filterForm = document.getElementById('product-filter-form');
  var listUrlBase = productListWrapper
    ? productListWrapper.getAttribute('data-list-url') || ''
    : '';

  function buildUrlFromParams(params) {
    var qs = params.toString();
    if (!listUrlBase) {
      // fallback: dùng URL hiện tại
      return qs ? (window.location.pathname + '?' + qs) : window.location.pathname;
    }
    return qs ? (listUrlBase + '?' + qs) : listUrlBase;
  }

  function getCurrentFilters() {
    var params = new URLSearchParams(window.location.search);

    if (filterForm) {
      var formData = new FormData(filterForm);
      formData.forEach(function (value, key) {
        if (value) {
          params.set(key, value);
        } else {
          params.delete(key);
        }
      });
    }

    if (sortBySelect) {
      params.set('sort_by', sortBySelect.value);
    }

    return params;
  }

  async function fetchProducts(url, isPagination) {
    if (!productListWrapper) {
      return;
    }

    productListWrapper.style.opacity = '0.5';

    try {
      var response = await fetch(url, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/html'
        }
      });

      if (!response.ok) {
        console.error('fetchProducts status:', response.status, response.statusText);
        throw new Error('Network response was not ok');
      }

      var html = await response.text();
      var parser = new DOMParser();
      var doc = parser.parseFromString(html, 'text/html');

      var newWrapper = doc.getElementById('product-list-wrapper');

      if (newWrapper) {
        productListWrapper.innerHTML = newWrapper.innerHTML;
        window.history.pushState({}, '', url);
        initBookCovers(productListWrapper);
      } else {
        var container = doc.querySelector('.container');
        if (container) {
          productListWrapper.innerHTML = container.innerHTML;
          window.history.pushState({}, '', url);
          initBookCovers(productListWrapper);
        }
      }
    } catch (error) {
      console.error('Error fetching products:', error);
      alert('Đã xảy ra lỗi khi tải sản phẩm. Vui lòng thử lại.');
    } finally {
      productListWrapper.style.opacity = '1';
      if (!isPagination) {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      }
    }
  }

  // 1. Sắp xếp
  if (sortBySelect) {
    sortBySelect.addEventListener('change', function () {
      var params = getCurrentFilters();
      params.delete('page');
      var url = buildUrlFromParams(params);
      fetchProducts(url, false);
    });
  }

  // 2. Phân trang (event delegation)
  if (productListWrapper) {
    productListWrapper.addEventListener('click', function (e) {
      var paginationLink = e.target.closest('.pagination a');
      if (!paginationLink) {
        return;
      }
      e.preventDefault();
      var pageUrl = paginationLink.href;
      if (pageUrl) {
        fetchProducts(pageUrl, true);
      }
    });
  }

  // 3. Filter form
  if (filterForm) {
    filterForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var params = getCurrentFilters();
      params.delete('page');
      var url = buildUrlFromParams(params);
      fetchProducts(url, false);
    });
  }
});
/* ======================= END LOAD IMAGE + SORT/FILTER/PAGINATION ======================= */


document.addEventListener('DOMContentLoaded', function () {
  var minInput = document.getElementById('priceRangeMin');
  var maxInput = document.getElementById('priceRangeMax');
  var bubbleMin = document.getElementById('priceBubbleMin');
  var bubbleMax = document.getElementById('priceBubbleMax');
  var selected = document.getElementById('priceRangeSelected');
  var hiddenMin = document.querySelector('input[name="price_min"]');
  var hiddenMax = document.querySelector('input[name="price_max"]');

  if (!minInput || !maxInput || !bubbleMin || !bubbleMax || !selected) {
    return;
  }

  var min = parseInt(minInput.min) || 0;
  var max = parseInt(minInput.max) || 1;

  function fmt(v) {
    return Number(v || 0).toLocaleString('vi-VN') + 'đ';
  }

  function sync() {
    var vMin = parseInt(minInput.value) || 0;
    var vMax = parseInt(maxInput.value) || 0;

    if (vMin > vMax) {
      var tmp = vMin;
      vMin = vMax;
      vMax = tmp;
    }

    minInput.value = vMin;
    maxInput.value = vMax;

    var pMin = (vMin - min) * 100 / (max - min);
    var pMax = (vMax - min) * 100 / (max - min);

    bubbleMin.style.left = pMin + '%';
    bubbleMax.style.left = pMax + '%';
    bubbleMin.textContent = fmt(vMin);
    bubbleMax.textContent = fmt(vMax);

    selected.style.left = pMin + '%';
    selected.style.right = (100 - pMax) + '%';

    if (hiddenMin) hiddenMin.value = vMin;
    if (hiddenMax) hiddenMax.value = vMax;
  }

  minInput.addEventListener('input', sync);
  maxInput.addEventListener('input', sync);
  sync();
});