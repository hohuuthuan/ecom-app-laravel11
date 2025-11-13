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

    document.getElementById('addAddressModal')
      ?.addEventListener('shown.bs.modal', function () { HHT.select2(this); });

    // ====== LOAD PHƯỜNG/XÃ KHI CHỌN TỈNH (DÙNG JQUERY + SELECT2) ======
    $(document).on('change', '#shippingProvince', function () {
      var $province = $(this);
      var provinceId = $province.val();
      var url = $province.data('wards-url');
      var $ward = $('#shippingWard');

      if (!url || !provinceId) {
        $ward
          .html('<option value="">Chọn Phường/Xã</option>')
          .val('')
          .trigger('change');
        return;
      }

      $ward
        .html('<option value="">Đang tải...</option>')
        .val('')
        .trigger('change');

      $.ajax({
        url: url,
        data: { province_id: provinceId },
        dataType: 'json'
      })
        .done(function (res) {
          if (!res || !res.success || !Array.isArray(res.wards)) {
            $ward
              .html('<option value="">Không tìm thấy Phường/Xã</option>')
              .val('')
              .trigger('change');
            return;
          }

          var html = '<option value="">Chọn Phường/Xã</option>';
          res.wards.forEach(function (ward) {
            var label = ward.name_with_type || ward.name || '';
            html += '<option value="' + ward.id + '">' + label + '</option>';
          });

          $ward
            .html(html)
            .val('')
            .trigger('change');
        })
        .fail(function () {
          $ward
            .html('<option value="">Không tải được danh sách</option>')
            .val('')
            .trigger('change');
        });
    });
  });
})(jQuery);
