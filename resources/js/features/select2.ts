declare global {
  interface Window { jQuery?: any; $?: any; }
}

/** Khởi tạo Select2 cho tất cả .setupSelect2 trong scope (page hoặc modal) */
export function initSelect2(scope: Document | HTMLElement): void {
  const $ = window.jQuery || window.$;
  if (!$ || !$.fn || !$.fn.select2) return;

  const nodes: NodeListOf<HTMLSelectElement> =
    (scope as HTMLElement).querySelectorAll?.('.setupSelect2') ?? document.querySelectorAll('.setupSelect2');

  nodes.forEach((el) => {
    const $el = $(el);
    // tránh re-init
    if ($el.hasClass('select2-hidden-accessible')) return;

    const $parent = $el.closest('.modal');
    $el.select2({
      dropdownParent: $parent.length ? $parent : $(document.body),
      width: $el.data('width') || 'resolve',
    });
  });
}
