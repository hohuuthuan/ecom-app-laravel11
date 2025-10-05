// ========== ẢNH PREVIEW ==========
(function () {
  const fileInput = document.getElementById('productImageFile');
  const previewBox = document.getElementById('previewBox');
  const btnClear = document.getElementById('btnClearImage');

  function setPreview(file) {
    const reader = new FileReader();
    reader.onload = e => {
      previewBox.innerHTML = '<img alt="preview">';
      previewBox.querySelector('img').src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  fileInput?.addEventListener('change', e => {
    const f = e.target.files?.[0];
    if (f) setPreview(f);
  });

  btnClear?.addEventListener('click', () => {
    if (fileInput) fileInput.value = '';
    previewBox.innerHTML = '<span class="text-muted"><i class="fa-regular fa-image me-1"></i> Ảnh sản phẩm</span>';
  });
})();

// ========== TAGIFY DANH MỤC / TÁC GIẢ ==========
(function () {
  function readJSONAttr(el, attr, fallback = []) {
    if (!el) return fallback;
    const raw = el.getAttribute(attr);
    if (!raw) return fallback;
    try {
      const v = JSON.parse(raw);
      return Array.isArray(v) ? v : fallback;
    } catch {
      return fallback;
    }
  }

  function toTagifyList(items) {
    return items.map(x => ({ value: String(x.id), name: x.name }));
  }

  function preloadFromOld(inputEl, tagify) {
    const oldIds = readJSONAttr(inputEl, 'data-old', []);
    if (!oldIds.length) return;

    // map id -> name theo whitelist
    const wl = tagify.settings.whitelist || [];
    const nameMap = new Map(wl.map(x => [String(x.value), x.name]));
    const items = oldIds.map(id => {
      const val = String(id);
      return { value: val, name: nameMap.get(val) || val };
    });

    tagify.addTags(items);
  }

  function setupTagify(inputEl) {
    if (!inputEl) return null;

    const data = readJSONAttr(inputEl, 'data-source', []);
    const whitelist = toTagifyList(data);

    const tagify = new Tagify(inputEl, {
      enforceWhitelist: true,
      whitelist,
      tagTextProp: 'name',
      dropdown: {
        enabled: 0,
        maxItems: 50,
        closeOnSelect: false,
        highlightFirst: true,
        mapValueTo: 'name',
        searchKeys: ['name']
      },
      editTags: false
    });

    // mở dropdown khi focus/click
    const openDropdown = () => tagify.dropdown.show.call(tagify, inputEl.value);
    inputEl.addEventListener('focus', openDropdown);
    inputEl.addEventListener('click', openDropdown);

    // hiệu ứng chip "in" + tooltip
    tagify.on('add', e => {
      const tagEl = e.detail.tag;
      if (!tagEl) return;
      tagEl.title = e.detail.data?.name || e.detail.data?.value || '';
      tagEl.classList.remove('chip-anim-in'); void tagEl.offsetWidth;
      tagEl.classList.add('chip-anim-in');
      tagEl.addEventListener('animationend', () => {
        tagEl.classList.remove('chip-anim-in');
      }, { once: true });
    });

    // nạp lại từ old()
    preloadFromOld(inputEl, tagify);

    // lưu ref để lấy giá trị lúc submit
    inputEl.__tagify = tagify;
    return tagify;
  }

  setupTagify(document.getElementById('categoriesInput'));
  setupTagify(document.getElementById('authorsInput'));
})();

// ========== XÁC NHẬN + BƠM MẢNG UUID VÀO HIDDEN INPUTS ==========
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('productCreateForm');
  if (!form || typeof window.UIConfirm !== 'function') return;

  const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
  if (!submitBtn) return;

  function injectArrayHidden(form, name, values) {
    // xoá cũ
    form.querySelectorAll(`input[name="${name}[]"]`).forEach(e => e.remove());
    // bơm mới
    for (const v of values) {
      const hid = document.createElement('input');
      hid.type = 'hidden';
      hid.name = `${name}[]`;
      hid.value = v; // UUID
      form.appendChild(hid);
    }
  }

  submitBtn.addEventListener('click', async (e) => {
    e.preventDefault();              // chặn submit ngay từ click để tránh overlay sớm
    form.dataset.noLoading = '';     // layout đọc cờ này để không bật loading khi confirm

    const ok = await window.UIConfirm({
      title: 'Xác nhận lưu sản phẩm',
      message: 'Bạn có chắc muốn lưu sản phẩm này?',
      confirmText: 'Lưu',
      cancelText: 'Huỷ',
      size: 'md'
    });
    if (!ok) {
      delete form.dataset.noLoading;
      return;
    }

    const catEl = document.getElementById('categoriesInput');
    const autEl = document.getElementById('authorsInput');
    const catVals = (catEl?.__tagify?.value || []).map(x => x.value);
    const autVals = (autEl?.__tagify?.value || []).map(x => x.value);

    // Gỡ name trên input Tagify để tránh gửi chuỗi
    catEl?.removeAttribute('name');
    autEl?.removeAttribute('name');

    // Bơm đúng mảng [] cho backend
    injectArrayHidden(form, 'categoriesInput', catVals);
    injectArrayHidden(form, 'authorsInput', autVals);

    // Cho phép overlay và submit thật
    delete form.dataset.noLoading;
    form.requestSubmit();
  }, { passive: false });
});
