/* global bootstrap */
(function () {
  'use strict';
  function getTab() {
    const t = new URL(location.href).searchParams.get('tab');
    return ['accounts','roles','stats'].includes(t) ? t : 'accounts';
  }
  function setTab(tab, replace) {
    const u = new URL(location.href);
    u.searchParams.set('tab', tab);
    replace ? history.replaceState(null, '', u) : history.pushState(null, '', u);
  }
  function showTab(tab) {
    const trigger = document.querySelector(`#accountTabs [data-bs-target="#${tab}-pane"]`);
    if (trigger) new bootstrap.Tab(trigger).show();
  }
  document.addEventListener('DOMContentLoaded', () => {
    const first = getTab();
    showTab(first);
    setTab(first, true);
    const tabs = document.getElementById('accountTabs');
    if (tabs) {
      tabs.addEventListener('shown.bs.tab', e => {
        const id = e.target?.getAttribute('data-bs-target') || '';
        const t = id.replace('#','').replace('-pane','');
        setTab(t, false);
      });
    }
    window.addEventListener('popstate', () => showTab(getTab()));
  });
})();
