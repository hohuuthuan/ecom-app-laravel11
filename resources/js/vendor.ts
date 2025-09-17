import $ from 'jquery';
(window as any).$ = $;
(window as any).jQuery = $;

import * as bootstrap from 'bootstrap';
(window as any).bootstrap = bootstrap;

// CSS Select2
import 'select2/dist/css/select2.css';
import '../css/vendor/select2-custom.css';

async function ensureSelect2() {
  const jq: any = (window as any).jQuery || (window as any).$;
  if (!jq.fn || !jq.fn.select2) {
    await import('select2');
  }
}

function mountSelect2(ctx?: Document | HTMLElement) {
  const jq: any = (window as any).jQuery || (window as any).$;
  if (!jq || !jq.fn || !jq.fn.select2) return;

  const $root = ctx ? jq(ctx) : jq(document);
  $root.find('.setupSelect2').each((_i: number, el: Element) => {
    const $el = jq(el as HTMLSelectElement);
    if ($el.hasClass('select2-hidden-accessible')) return;
    const $parent = $el.closest('.modal');
    $el.select2({
      dropdownParent: $parent.length ? $parent : jq(document.body),
      width: $el.data('width') || 'resolve',
    });
  });
}

(async () => {
  await ensureSelect2();
  mountSelect2(document);
  document.addEventListener('DOMContentLoaded', () => mountSelect2(document));
  document.addEventListener('shown.bs.modal', (e: any) => mountSelect2(e.target as HTMLElement));
  (window as any).__mountSelect2 = mountSelect2;
})();
