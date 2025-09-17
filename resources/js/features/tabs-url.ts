import { on, qs, qsa } from '../core/dom';

declare global {
  interface Window {
    bootstrap?: any; // tránh lỗi type khi Bootstrap JS ở CDN
  }
}

export interface TabsUrlConfig {
  navSelector?: string; // mặc định auto tìm .nav-tabs
  param?: string;       // tên query, mặc định "tab"
}

/**
 * Đồng bộ Bootstrap Tab với query string ?tab=<key>.
 * - Khi người dùng đổi tab: cập nhật ?tab=...
 * - Khi load trang: đọc ?tab=... và kích hoạt tab tương ứng (nếu có).
 */
export function init(root: HTMLElement, cfg: TabsUrlConfig = {}): void {
  const param = cfg.param || 'tab';
  const nav = cfg.navSelector ? qs<HTMLElement>(root, cfg.navSelector) : qs<HTMLElement>(root, '.nav-tabs');
  if (!nav) {
    return;
  }

  // Khi đổi tab -> cập nhật URL
  on(nav, 'shown.bs.tab', '[data-bs-toggle="tab"]', (ev, el) => {
    const key = tabKeyFromControl(el);
    if (!key) {
      return;
    }
    const url = new URL(window.location.href);
    url.searchParams.set(param, key);
    window.history.replaceState({}, '', url.toString());
  });

  // Khi tải -> chọn tab theo URL
  const current = new URL(window.location.href).searchParams.get(param);
  if (!current) {
    return;
  }
  const controls = qsa<HTMLElement>(nav, '[data-bs-toggle="tab"]');
  for (const c of controls) {
    const key = tabKeyFromControl(c);
    if (key === current) {
      // Bootstrap 5 API
      if (window.bootstrap && window.bootstrap.Tab) {
        const inst = window.bootstrap.Tab.getOrCreateInstance(c);
        inst.show();
      } else {
        // Fallback: click
        (c as HTMLButtonElement).click();
      }
      break;
    }
  }
}

function tabKeyFromControl(el: Element): string | null {
  const explicit = el.getAttribute('data-tab');
  if (explicit) {
    return explicit;
  }
  const target = el.getAttribute('data-bs-target') || el.getAttribute('href') || '';
  if (target.startsWith('#')) {
    return target.replace(/^#/, '').replace(/-pane$/, ''); // ví dụ #brand-pane -> brand
  }
  return null;
}
