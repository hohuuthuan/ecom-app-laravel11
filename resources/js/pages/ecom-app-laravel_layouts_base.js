(function () {
  const overlay = document.getElementById('pageLoading');
  if (!overlay) return;

  const DELAY_MS = 600;
  const MIN_SHOW_MS = 0;
  let showTimer = null;
  let shownAt = 0;

  const showDelayed = () => {
    if (showTimer || !overlay.classList.contains('d-none')) return;
    showTimer = setTimeout(() => {
      overlay.classList.remove('d-none');
      shownAt = Date.now();
      showTimer = null;
    }, DELAY_MS);
  };

  const hideNow = () => {
    if (showTimer) { clearTimeout(showTimer); showTimer = null; }
    if (!overlay.classList.contains('d-none')) {
      const elapsed = Date.now() - shownAt;
      if (elapsed < MIN_SHOW_MS) {
        setTimeout(() => overlay.classList.add('d-none'), MIN_SHOW_MS - elapsed);
      } else {
        overlay.classList.add('d-none');
      }
    }
  };

  document.addEventListener('click', function (e) {
    const a = e.target.closest('a[href]');
    if (!a) return;
    const href = a.getAttribute('href');
    const newTab = a.target === '_blank' || e.metaKey || e.ctrlKey;
    const sameHash = href && href.startsWith('#');
    if (newTab || sameHash || a.dataset.noLoading !== undefined) return;
    showDelayed();
  }, true);

  document.addEventListener('submit', function (e) {
    if (e.defaultPrevented) return; 
    if (e.target?.dataset?.noLoading !== undefined) return;
    showDelayed();
  }, true);

  window.addEventListener('pageshow', hideNow);
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') hideNow();
  });
})();
  
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("form.filter-form").forEach(form => {
    form.addEventListener("submit", function () {
      form.querySelectorAll("input, select, textarea").forEach(el => {
        if (!el.value) el.removeAttribute("name");
      });
    });
  });
});
