import { dataBool, dataNum, qsa } from '../core/dom';

declare global {
  interface Window {
    bootstrap?: any;
  }
}

/**
 * Khởi tạo mọi .toast trong tài liệu.
 * Dựa vào Bootstrap Toast nếu có. Nếu không, bỏ qua để không gây lỗi.
 */
export function init(doc: Document): void {
  const list = qsa<HTMLElement>(doc, '.toast');
  if (list.length === 0) {
    return;
  }
  const hasBs = !!(window.bootstrap && window.bootstrap.Toast);
  for (const el of list) {
    const autohide = dataBool(el, 'autohide', true);
    const delay = dataNum(el, 'delay-ms', 3000);

    if (hasBs) {
      const inst = window.bootstrap.Toast.getOrCreateInstance(el, { autohide, delay });
      inst.show();
    }
  }
}
