
(function () {
  function toSlug(s) {
    return String(s || '')
      .toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '')
      .replace(/-+/g, '-');
  }

  function bindScope(scope) {
    const src = scope.querySelector('[data-slug-source]');
    const dst = scope.querySelector('[data-slug-dest]');
    if (!src || !dst) return;

    let touched = false;
    dst.addEventListener('input', () => { touched = true; });
    src.addEventListener('input', () => {
      if (touched) return;
      dst.value = toSlug(src.value);
    });

    if (dst.value) dst.value = toSlug(dst.value);
  }

  function init() {
    document.querySelectorAll('[data-slug-scope]').forEach(bindScope);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
