import $ from 'jquery';
(window as any).$ = $;
(window as any).jQuery = $;

import 'select2/dist/css/select2.css';
import '../css/vendor/select2-custom.css';

async function ensureSelect2() {
  const jq: any = (window as any).jQuery || (window as any).$;
  if (!jq.fn?.select2) await import('select2');
}

export async function mountSelect2(scope: Document | HTMLElement = document) {
  await ensureSelect2();
  const jq: any = (window as any).jQuery || (window as any).$;
  const root = scope instanceof HTMLElement ? scope : document;

  root.querySelectorAll<HTMLSelectElement>('.setupSelect2').forEach((el) => {
    const $el = jq(el);
    if ($el.hasClass('select2-hidden-accessible')) return;
    const $parent = $el.closest('.tw-modal');
    $el.select2({
      dropdownParent: $parent.length ? $parent : jq(document.body),
      width: $el.data('width') || 'resolve',
      placeholder: $el.attr('placeholder') || undefined,
      allowClear: $el.hasAttribute('data-allow-clear'),
    });
  });
}

// Tự gắn khi DOM thay đổi
(function observe() {
  const obs = new MutationObserver(() => { mountSelect2(document); });
  obs.observe(document.documentElement, { childList: true, subtree: true });
  document.addEventListener('DOMContentLoaded', () => mountSelect2(document));
})();
