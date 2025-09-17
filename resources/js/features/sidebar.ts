import { on, qs } from '../core/dom';

const KEY = 'sidebarMini';
const CLASS = 'sidebar-mini';

/**
 * Toggle sidebar mini khi click [data-sidebar-toggle], lưu trạng thái vào localStorage.
 * Yêu cầu layout có class CSS tương ứng. Nếu không có cũng không gây lỗi.
 */
export function init(doc: Document): void {
  const saved = localStorage.getItem(KEY);
  if (saved === '1') {
    document.body.classList.add(CLASS);
  }

  const toggleBtn = qs<HTMLElement>(doc, '[data-sidebar-toggle]');
  if (!toggleBtn) {
    return;
  }

  on(toggleBtn, 'click', (ev) => {
    ev.preventDefault();
    const active = document.body.classList.toggle(CLASS);
    localStorage.setItem(KEY, active ? '1' : '0');
  });
}
