(function () {
  'use strict';
  const btnCollapse = document.getElementById('btnCollapseDesktop');
  const sidebarCol = document.getElementById('sidebarCol');
  const contentCol = document.getElementById('contentCol');
  const mqlLg = window.matchMedia('(min-width: 992px)');
  const LS_KEY = 'adminSidebarMini';


  function toMini() { sidebarCol?.classList.add('mini'); contentCol?.classList.add('expand'); }
  function toFull() { sidebarCol?.classList.remove('mini'); contentCol?.classList.remove('expand'); }
  function disableMiniTooltips() { document.querySelectorAll('#sidebarCol [data-bs-toggle="tooltip"]').forEach(el => { const tip = bootstrap.Tooltip.getInstance(el); if (tip) { tip.dispose(); } }); }


  document.addEventListener('DOMContentLoaded', function () {
    const pref = localStorage.getItem(LS_KEY);
    if (mqlLg.matches) { if (pref === '1') { toMini(); } else { toFull(); } }
    btnCollapse?.addEventListener('click', function () {
      const isMini = sidebarCol?.classList.contains('mini');
      if (isMini) { toFull(); localStorage.setItem(LS_KEY, '0'); }
      else { toMini(); localStorage.setItem(LS_KEY, '1'); }
    });


    mqlLg.addEventListener('change', (e) => {
      const pref2 = localStorage.getItem(LS_KEY);
      if (e.matches) { if (pref2 === '1') { toMini(); } else { toFull(); } }
      else { disableMiniTooltips(); }
    });
  });
})();