import { on } from '../core/dom';

/** Bật/tắt type=password cho các nút có [data-toggle-password] */
export function mountPasswordToggle(root: Document | HTMLElement): void {
  on(root as any, 'click', '[data-toggle-password]', (ev, btn) => {
    ev.preventDefault();
    const target = btn.getAttribute('data-target') || '';
    if (!target) return;
    const input = (root as Document | HTMLElement).querySelector<HTMLInputElement>(target) || document.querySelector<HTMLInputElement>(target);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    // đổi icon nếu có
    const ic = btn.querySelector('[data-eye]');
    if (ic) {
      const isShow = input.type === 'text';
      ic.setAttribute('data-eye', isShow ? 'on' : 'off');
    }
  });
}
