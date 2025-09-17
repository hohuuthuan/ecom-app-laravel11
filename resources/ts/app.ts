import '../css/app.css';

import { init as initToasts } from './features/toast';
import { init as initSidebar } from './features/sidebar';
import { show as showLoading, hide as hideLoading } from './features/loading';
import { mountSelect2 } from './vendor';
import { bindPasswordToggles } from './features/password-toggle';

function installNavLoading(): void {
  window.addEventListener('beforeunload', () => { showLoading('Đang tải…'); });
}

document.addEventListener('DOMContentLoaded', () => {
  installNavLoading();
  initSidebar(document);
  initToasts(document);
  bindPasswordToggles(document);
  mountSelect2(document);
  document.addEventListener('modal:closed', () => hideLoading());
});
