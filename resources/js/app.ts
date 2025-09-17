import * as bootstrap from 'bootstrap';
(window as any).bootstrap = bootstrap;

import '../css/loading.css';

import { boot } from './core/registry';
import { init as initToasts } from './features/toast';
import { init as initSidebar } from './features/sidebar';
import { initSelect2 } from './features/select2';
import { show as showLoading, hide as hideLoading } from './features/loading';

function installNavLoading(): void {
  // Hiện loading khi rời trang
  window.addEventListener('beforeunload', () => {
    showLoading('Đang tải…');
  });

  // Hiện loading khi submit form
  document.addEventListener('submit', () => {
    showLoading('Đang gửi…');
  }, { capture: true });

  // Hiện loading khi click link điều hướng cùng origin
  document.addEventListener('click', (ev: MouseEvent) => {
    const target = ev.target as Element | null;
    if (!target) { return; }

    const a = (target as HTMLElement).closest?.('a[href]') as HTMLAnchorElement | null;
    if (!a) { return; }

    // Bỏ qua nếu mở tab mới / download / modifier keys
    if (a.target && a.target !== '' && a.target !== '_self') { return; }
    if (a.hasAttribute('download')) { return; }
    if (ev.defaultPrevented || ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey) { return; }

    // Bỏ qua anchor nội bộ (#hash) và khác origin
    let url: URL;
    try { url = new URL(a.href, location.href); } catch { return; }
    if (url.origin !== location.origin) { return; }
    if (url.hash && url.pathname === location.pathname && url.search === location.search) { return; }

    showLoading('Đang tải…');
  }, { capture: true });

  // Ẩn khi trang đã vẽ lại
  window.addEventListener('pageshow', () => {
    hideLoading();
  });
}

(() => {
  installNavLoading();

  initToasts(document);
  initSidebar(document);

  // Select2 ngay khi load
  initSelect2(document);

  // Select2 trong modal khi mở
  document.addEventListener('shown.bs.modal', (e: any) => {
    const modal = e.target as HTMLElement;
    initSelect2(modal);
  });

  const root = document.getElementById('app') as HTMLElement | null;
  if (!root) {
    return;
  }
  if (!root.hasAttribute('data-js-ready')) {
    root.setAttribute('data-js-ready', '1');
  }
  boot(root);
})();
