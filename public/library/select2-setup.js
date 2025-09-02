var HHT = {};
(function ($) {
  "use strict";

  HHT.select2 = (ctx) => {
    const $root = ctx ? $(ctx) : $(document);
    $root.find('.setupSelect2').each(function () {
      const $el = $(this);
      if ($el.hasClass('select2-hidden-accessible')) return;
      const $parent = $el.closest('.modal');
      $el.select2({
        dropdownParent: $parent.length ? $parent : $(document.body),
        width: $el.data('width') || 'resolve'
      });
    });
  };

  $(document).ready(function () {
    HHT.select2();
    document.getElementById('uiAccountEditModal')
      ?.addEventListener('shown.bs.modal', function () { HHT.select2(this); });
  });
})(jQuery);
