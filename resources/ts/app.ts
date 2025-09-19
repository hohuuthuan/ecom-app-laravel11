import '../css/app.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

import { init as initToasts } from './features/toast';
import { initSidebarUI } from './features/sidebar';
import { initAdminShell } from './features/admin-shell';
import { show as showLoading, hide as hideLoading } from './features/loading';
import { mountSelect2 } from './vendor';
import { bindPasswordToggles } from './features/password-toggle';

function installNavLoading(): void {
  let t: number | undefined;
  let pageHidden = false;

  window.addEventListener('pagehide', (e: PageTransitionEvent) => {
    // Đánh dấu đã rời trang; nếu vào bfcache sẽ có persisted=true
    pageHidden = !e.persisted;
  });

  function schedule(ms: number) {
    clearTimeout(t);
    pageHidden = false; // reset mỗi tương tác
    t = window.setTimeout(() => {
      if (!pageHidden && document.visibilityState === 'visible') {
        showLoading('Đang tải…');
      }
    }, ms);
  }

  function shouldLoad(a: HTMLAnchorElement | null): boolean {
    if (!a) return false;
    const href = a.getAttribute('href') || '';
    if (a.target === '_blank') return false;
    if (a.hasAttribute('download')) return false;
    if (href.startsWith('#') || href.startsWith('javascript:')) return false;
    return true;
  }

  document.addEventListener('click', (e) => {
    const a = (e.target as Element).closest('a') as HTMLAnchorElement | null;
    if (!shouldLoad(a)) return;
    schedule(300); // tăng delay để tránh chớp
  }, true);

  document.addEventListener('submit', () => {
    schedule(200);
  }, true);

  window.addEventListener('pageshow', () => {
    clearTimeout(t);
    hideLoading();
  });

  window.addEventListener('unload', () => { clearTimeout(t); });
}


document.addEventListener('DOMContentLoaded', () => {
  installNavLoading();
  initSidebarUI(document);
  initAdminShell(document);
  initToasts(document);
  bindPasswordToggles(document);
  mountSelect2(document);

  document.addEventListener('modal:closed', () => hideLoading());
});
