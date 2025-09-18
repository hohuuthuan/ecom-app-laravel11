import '../css/app.css';

import { init as initToasts } from './features/toast';
import { init as initSidebar } from './features/sidebar';
import { initAdminShell } from './features/admin-shell';
import { show as showLoading, hide as hideLoading } from './features/loading';
import { mountSelect2 } from './vendor';
import { bindPasswordToggles } from './features/password-toggle';

function installNavLoading(): void {
  window.addEventListener('beforeunload', () => { showLoading('Đang tải…'); });
}

window.addEventListener('pageshow', (e) => {
  if ((e as PageTransitionEvent).persisted) {
    document.querySelectorAll('.tw-toast, [data-toast]').forEach(n => n.remove());
  }
});

document.addEventListener('DOMContentLoaded', () => {
  installNavLoading();
  initSidebar(document);
  initAdminShell(document); 
  initToasts(document);
  bindPasswordToggles(document);
  mountSelect2(document);
  window.addEventListener('beforeunload', () => { showLoading('Đang tải…'); });
  document.addEventListener('modal:closed', () => hideLoading());
});
