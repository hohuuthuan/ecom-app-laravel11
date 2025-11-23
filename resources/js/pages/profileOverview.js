(function () {
  // ==== TAB HỒ SƠ (TRÁI) ====
  var navLinks = document.querySelectorAll('.profile-nav-link');
  var sections = document.querySelectorAll('.profile-section');

  if (navLinks.length && sections.length) {
    function setActiveTab(target) {
      if (!target) {
        return;
      }

      navLinks.forEach(function (link) {
        var isActive = link.getAttribute('data-target') === target;
        link.classList.toggle('active', isActive);
      });

      sections.forEach(function (section) {
        var isActive = section.getAttribute('data-section') === target;
        section.classList.toggle('active', isActive);
      });
    }

    navLinks.forEach(function (link) {
      link.addEventListener('click', function (e) {
        var target = link.getAttribute('data-target');
        if (!target) {
          return;
        }

        e.preventDefault();

        setActiveTab(target);

        if (window.history && window.history.replaceState) {
          var url = new URL(window.location.href);
          url.searchParams.set('tab', target);
          window.history.replaceState(null, '', url.toString());
        }
      });
    });
  }

  // ==== RESET MODAL EDIT PROFILE ====
  var profileModalEl = document.getElementById('editProfileModal');
  if (profileModalEl) {
    profileModalEl.addEventListener('hidden.bs.modal', function () {
      profileModalEl.querySelectorAll('.is-invalid').forEach(function (el) {
        el.classList.remove('is-invalid');
      });

      profileModalEl.querySelectorAll('.invalid-feedback').forEach(function (el) {
        el.classList.add('d-none');
      });

      profileModalEl.querySelectorAll('.alert').forEach(function (el) {
        el.classList.add('d-none');
      });

      profileModalEl.querySelectorAll('input[name], textarea[name], select[name]').forEach(function (field) {
        var originalValue = field.getAttribute('data-original-value');
        if (originalValue !== null) {
          field.value = originalValue;
        }
      });
    });
  }

  // ==== RESET MODAL UPDATE ADDRESS ====
  var updateAddressModalEl = document.getElementById('updateAddressModal');
  if (updateAddressModalEl) {
    updateAddressModalEl.addEventListener('hidden.bs.modal', function () {
      // xóa trạng thái lỗi
      updateAddressModalEl.querySelectorAll('.is-invalid').forEach(function (el) {
        el.classList.remove('is-invalid');
      });

      updateAddressModalEl.querySelectorAll('.invalid-feedback').forEach(function (el) {
        el.classList.add('d-none');
      });

      // clear các field để lần sau mở lại sẽ fill theo data-* của nút Sửa
      var addrInput = updateAddressModalEl.querySelector('#updateShippingAddress');
      if (addrInput) {
        addrInput.value = '';
      }

      var provinceSelect = updateAddressModalEl.querySelector('#updateShippingProvince');
      if (provinceSelect) {
        provinceSelect.value = '';
        if (
          window.jQuery &&
          jQuery.fn.select2 &&
          jQuery(provinceSelect).hasClass('select2-hidden-accessible')
        ) {
          jQuery(provinceSelect).val('').trigger('change.select2');
        }
      }

      var wardSelect = updateAddressModalEl.querySelector('#updateShippingWard');
      if (wardSelect) {
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        if (
          window.jQuery &&
          jQuery.fn.select2 &&
          jQuery(wardSelect).hasClass('select2-hidden-accessible')
        ) {
          jQuery(wardSelect).val('').trigger('change.select2');
        }
      }

      var noteTextarea = updateAddressModalEl.querySelector('#updateAddressNote');
      if (noteTextarea) {
        noteTextarea.value = '';
      }

      var defaultCheckbox = updateAddressModalEl.querySelector('#updateAddressDefault');
      if (defaultCheckbox) {
        defaultCheckbox.checked = false;
      }

      var hiddenId = updateAddressModalEl.querySelector('#updateAddressId');
      if (hiddenId) {
        hiddenId.value = '';
      }
    });

    // ==== HANDLE ĐỔI TỈNH TRONG MODAL UPDATE (DÙNG SELECT2) ====
    if (window.jQuery) {
      jQuery(document).on('change', '#updateShippingProvince', function () {
        if (!updateAddressModalEl) {
          return;
        }

        var provinceSelect = this;
        var wardSelect = updateAddressModalEl.querySelector('#updateShippingWard');
        if (!wardSelect) {
          return;
        }

        var provinceId = provinceSelect.value;
        var wardsUrl = provinceSelect.getAttribute('data-wards-url');

        // wardId ban đầu (khi mở modal lần đầu), để chọn đúng phường hiện tại
        var initialWardId = provinceSelect.getAttribute('data-initial-ward-id') || '';

        // Sau lần đầu, không dùng lại ward cũ nữa
        provinceSelect.removeAttribute('data-initial-ward-id');

        // B1: reset phường ngay lập tức
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        if (
          window.jQuery &&
          jQuery.fn.select2 &&
          jQuery(wardSelect).hasClass('select2-hidden-accessible')
        ) {
          jQuery(wardSelect).val('').trigger('change.select2');
        }

        // Không có tỉnh hoặc URL -> dừng
        if (!provinceId || !wardsUrl) {
          return;
        }

        // B2: hiển thị trạng thái loading
        wardSelect.innerHTML = '<option value="">Đang tải...</option>';
        if (
          window.jQuery &&
          jQuery.fn.select2 &&
          jQuery(wardSelect).hasClass('select2-hidden-accessible')
        ) {
          jQuery(wardSelect).val('').trigger('change.select2');
        }

        // B3: gọi API lấy danh sách phường/xã
        fetch(wardsUrl + '?province_id=' + encodeURIComponent(provinceId))
          .then(function (res) {
            if (!res.ok) {
              return null;
            }
            return res.json();
          })
          .then(function (data) {
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';

            if (!data || !data.wards) {
              if (
                window.jQuery &&
                jQuery.fn.select2 &&
                jQuery(wardSelect).hasClass('select2-hidden-accessible')
              ) {
                jQuery(wardSelect).val('').trigger('change.select2');
              }
              return;
            }

            data.wards.forEach(function (ward) {
              var opt = document.createElement('option');
              opt.value = ward.id;
              opt.textContent = ward.name_with_type || ward.name;
              if (initialWardId && String(ward.id) === String(initialWardId)) {
                opt.selected = true;
              }
              wardSelect.appendChild(opt);
            });

            if (
              window.jQuery &&
              jQuery.fn.select2 &&
              jQuery(wardSelect).hasClass('select2-hidden-accessible')
            ) {
              if (initialWardId) {
                jQuery(wardSelect).val(initialWardId).trigger('change.select2');
              } else {
                // Khi user tự đổi tỉnh, không chọn sẵn phường
                jQuery(wardSelect).val('').trigger('change.select2');
              }
            }
          })
          .catch(function () {
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            if (
              window.jQuery &&
              jQuery.fn.select2 &&
              jQuery(wardSelect).hasClass('select2-hidden-accessible')
            ) {
              jQuery(wardSelect).val('').trigger('change.select2');
            }
          });
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    // ==== CONFIRM XOÁ ĐỊA CHỈ ====
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.js-address-delete-btn');
      if (!btn) {
        return;
      }

      var form = btn.closest('.js-address-delete-form');
      if (!form) {
        return;
      }

      if (!window.UIConfirm || typeof window.UIConfirm !== 'function') {
        if (window.confirm('Bạn có chắc chắn muốn xóa địa chỉ này không?')) {
          form.submit();
        }
        return;
      }

      var message = btn.getAttribute('data-confirm-message') ||
        'Bạn có chắc chắn muốn xóa địa chỉ này không?';

      window.UIConfirm({
        title: 'Xác nhận xoá địa chỉ',
        message: message,
        confirmText: 'Xoá',
        cancelText: 'Huỷ',
        size: 'md'
      }).then(function (confirmed) {
        if (confirmed) {
          form.submit();
        }
      });
    });

    // ==== MỞ MODAL CHỈNH SỬA ĐỊA CHỈ ====
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.address-edit-btn');
      if (!btn) {
        return;
      }

      var modalEl = document.getElementById('updateAddressModal');
      if (!modalEl || !window.bootstrap) {
        return;
      }

      var form = modalEl.querySelector('form');
      var id = btn.getAttribute('data-id') || '';
      var updateUrl = btn.getAttribute('data-update-url') || '';

      if (form && updateUrl) {
        form.action = updateUrl;
      }

      var hiddenId = modalEl.querySelector('#updateAddressId');
      if (hiddenId) {
        hiddenId.value = id;
      }

      var defaultCheckbox = modalEl.querySelector('#updateAddressDefault');
      if (defaultCheckbox) {
        var isDefault = btn.getAttribute('data-default');
        defaultCheckbox.checked = (isDefault === '1' || isDefault === 'true');
      }

      // Fill địa chỉ + note nếu chưa có old()
      var addressInput = modalEl.querySelector('input[name="address"]');
      if (addressInput && !addressInput.value) {
        addressInput.value = btn.getAttribute('data-address') || '';
      }

      var noteTextarea = modalEl.querySelector('textarea[name="note"]');
      if (noteTextarea && !noteTextarea.value) {
        noteTextarea.value = btn.getAttribute('data-note') || '';
      }

      // Fill tỉnh + set ward ban đầu (để handler change lo load ward)
      var provinceSelect = modalEl.querySelector('select[name="address_province_id"]');
      var wardId = btn.getAttribute('data-ward-id') || '';
      var provinceId = btn.getAttribute('data-province-id') || '';

      if (provinceSelect && provinceId) {
        // set value và đánh dấu ward ban đầu
        provinceSelect.value = provinceId;
        provinceSelect.setAttribute('data-initial-ward-id', wardId);

        if (
          window.jQuery &&
          jQuery.fn.select2 &&
          jQuery(provinceSelect).hasClass('select2-hidden-accessible')
        ) {
          // Trigger để Select2 update + handler change sẽ lo load ward
          jQuery(provinceSelect).val(provinceId).trigger('change.select2');
        }
      }

      var modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    });
  });
})();
