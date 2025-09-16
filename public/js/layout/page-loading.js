(function () {
  'use strict';
  const overlay = document.getElementById('pageLoading');
  function show() { overlay && overlay.classList.remove('d-none'); }
  function hide() { overlay && overlay.classList.add('d-none'); }
  document.addEventListener('click', function (e) {
    const a = e.target.closest && e.target.closest('a[href]');
    if (!a) { return; }
    const href = a.getAttribute('href');
    const newTab = a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey;
    if (!href || href.startsWith('#') || newTab) { return; }
    if (a.dataset.noLoading !== undefined) { return; }
    show();
  });
  window.addEventListener('pageshow', hide);
})();