<script>
  const btnCollapse = document.getElementById('btnCollapseDesktop');
  const sidebarCol  = document.getElementById('sidebarCol');
  const contentCol  = document.getElementById('contentCol');
  const mqlLg       = window.matchMedia('(min-width: 992px)');
  const LS_KEY      = 'adminSidebarMini'; // '1' = mini, '0' = full

  let miniTooltips = [];

  function enableMiniTooltips() {
    const links = sidebarCol.querySelectorAll('.nav-item > .nav-link');
    links.forEach(a => {
      const label = a.querySelector('.nav-label');
      const title = (label?.textContent || a.textContent || '').trim();
      if (!title) return;
      a.setAttribute('data-bs-toggle', 'tooltip');
      a.setAttribute('data-bs-placement', 'right');
      a.setAttribute('data-bs-title', title);
      const t = new bootstrap.Tooltip(a);
      miniTooltips.push(t);
    });
  }

  function disableMiniTooltips() {
    miniTooltips.forEach(t => t.dispose());
    miniTooltips = [];
    const links = sidebarCol.querySelectorAll('.nav-item > .nav-link');
    links.forEach(a => {
      a.removeAttribute('data-bs-toggle');
      a.removeAttribute('data-bs-placement');
      a.removeAttribute('data-bs-title');
    });
  }

  function toMini({persist = false} = {}) {
    sidebarCol.classList.add('sidebar-mini', 'col-lg-1');
    contentCol.classList.add('col-lg-11');
    sidebarCol.classList.remove('col-lg-2');
    contentCol.classList.remove('col-lg-10');
    enableMiniTooltips();
    if (persist) localStorage.setItem(LS_KEY, '1');
  }

  function toFull({persist = false} = {}) {
    sidebarCol.classList.remove('sidebar-mini', 'col-lg-1');
    contentCol.classList.remove('col-lg-11');
    sidebarCol.classList.add('col-lg-2');
    contentCol.classList.add('col-lg-10');
    disableMiniTooltips();
    if (persist) localStorage.setItem(LS_KEY, '0');
  }

  function toggleDesktopSidebar() {
    if (!mqlLg.matches) return;
    const mini = sidebarCol.classList.contains('sidebar-mini');
    mini ? toFull({persist:true}) : toMini({persist:true});
  }

  // Click nút toggle
  btnCollapse?.addEventListener('click', toggleDesktopSidebar);

  // Áp dụng trạng thái đã lưu khi load trang (giải quyết reset sau khi click link)
  document.addEventListener('DOMContentLoaded', () => {
    const pref = localStorage.getItem(LS_KEY);
    if (mqlLg.matches) {
      if (pref === '1') toMini(); else toFull();
    } else {
      // dưới lg: luôn full-width theo layout mobile
      disableMiniTooltips();
    }
  });

  // Khi đổi breakpoint
  mqlLg.addEventListener('change', (e) => {
    const pref = localStorage.getItem(LS_KEY);
    if (e.matches) {
      // quay lại màn to: áp dụng lại preference
      if (pref === '1') toMini(); else toFull();
    } else {
      // xuống màn nhỏ: tắt tooltip, để layout mobile lo phần width
      disableMiniTooltips();
      // không đổi localStorage
    }
  });
</script>