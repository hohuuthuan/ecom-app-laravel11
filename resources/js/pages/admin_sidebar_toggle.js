document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnCollapseDesktop');
  const sb  = document.getElementById('sidebarCol');
  const ct  = document.getElementById('contentCol');
  if (!btn || !sb || !ct) { return; }

  const setCookie = (on) => {
    try { document.cookie = 'adminSidebarMini=' + (on ? '1' : '0') + ';path=/;max-age=31536000'; } catch {}
  };

  const toMini = () => {
    sb.classList.add('sidebar-mini');
    sb.classList.remove('col-lg-2'); sb.classList.add('col-lg-1');
    ct.classList.remove('col-lg-10'); ct.classList.add('col-lg-11');
    try { localStorage.setItem('adminSidebarMini','1'); } catch {}
    setCookie(true);
  };

  const toFull = () => {
    sb.classList.remove('sidebar-mini');
    sb.classList.remove('col-lg-1'); sb.classList.add('col-lg-2');
    ct.classList.remove('col-lg-11'); ct.classList.add('col-lg-10');
    try { localStorage.setItem('adminSidebarMini','0'); } catch {}
    setCookie(false);
  };

  btn.addEventListener('click', () => {
    sb.classList.contains('sidebar-mini') ? toFull() : toMini();
  });
});
