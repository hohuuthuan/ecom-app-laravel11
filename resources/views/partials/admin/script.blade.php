<script>
  const btnCollapse = document.getElementById('btnCollapseDesktop');
  const sidebarCol = document.getElementById('sidebarCol');
  const contentCol = document.getElementById('contentCol');
  const mqlLg = window.matchMedia('(min-width: 992px)');

  // Keep tooltip instances to dispose later
  let miniTooltips = [];

  function enableMiniTooltips() {
    // add tooltip to each top-level link using its text label
    const links = sidebarCol.querySelectorAll('.nav-item > .nav-link');
    links.forEach(a => {
      // get label
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

  function toMini() {
    sidebarCol.classList.add('sidebar-mini', 'col-lg-1');
    contentCol.classList.add('col-lg-11');
    sidebarCol.classList.remove('col-lg-2');
    contentCol.classList.remove('col-lg-10');
    enableMiniTooltips();
  }

  function toFull() {
    sidebarCol.classList.remove('sidebar-mini', 'col-lg-1');
    contentCol.classList.remove('col-lg-11');
    sidebarCol.classList.add('col-lg-2');
    contentCol.classList.add('col-lg-10');
    disableMiniTooltips();
  }

  function toggleDesktopSidebar() {
    if (!mqlLg.matches) return;
    const mini = sidebarCol.classList.contains('sidebar-mini');
    mini ? toFull() : toMini();
  }

  btnCollapse?.addEventListener('click', toggleDesktopSidebar);

  // Reset on breakpoint change
  mqlLg.addEventListener('change', (e) => {
    if (e.matches) {
      toFull();
    } else {
      disableMiniTooltips();
    }
  });
</script>