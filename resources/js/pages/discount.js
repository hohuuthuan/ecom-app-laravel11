// ==== BLOCK 1: mở modal edit + fill dữ liệu ====
document.addEventListener('DOMContentLoaded', function () {
  var editModalElement = document.getElementById('discountEditModal');
  var createForm = document.getElementById('discountCreateForm');
  var editForm = document.getElementById('discountEditForm');
  var editModal = null;

  if (editModalElement && window.bootstrap) {
    editModal = window.bootstrap.Modal.getOrCreateInstance(editModalElement);
  }

  if (createForm) {
    var createModalElement = document.getElementById('discountCreateModal');
    if (createModalElement) {
      createModalElement.addEventListener('show.bs.modal', function () {
        createForm.reset();
      });
    }
  }

  var editButtons = document.querySelectorAll('.js-edit-discount-btn');
  if (editButtons.length > 0 && editForm && editModal) {
    editButtons.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();

        var id = btn.getAttribute('data-id') || '';
        var code = btn.getAttribute('data-code') || '';
        var type = btn.getAttribute('data-type') || '';
        var value = btn.getAttribute('data-value') || '';
        var minOrder = btn.getAttribute('data-min-order') || '';
        var start = btn.getAttribute('data-start') || '';
        var end = btn.getAttribute('data-end') || '';
        var status = btn.getAttribute('data-status') || '';
        var updateUrl = btn.getAttribute('data-update-url') || '';
        var usageLimit = btn.getAttribute('data-usage-limit') || '';
        var perUserLimit = btn.getAttribute('data-per-user-limit') || '';

        editForm.action = updateUrl;

        var inputId = document.getElementById('edit_discount_id');
        if (inputId) {
          inputId.value = id;
        }

        var inputCode = document.getElementById('edit_discount_code');
        if (inputCode) {
          inputCode.value = code;
        }

        var selectType = document.getElementById('edit_discount_type');
        if (selectType) {
          selectType.value = type;
          selectType.dispatchEvent(new Event('change'));
        }

        var inputValue = document.getElementById('edit_discount_value');
        if (inputValue) {
          inputValue.value = value;
        }

        var inputMinOrder = document.getElementById('edit_discount_min_order');
        if (inputMinOrder) {
          inputMinOrder.value = minOrder;
        }

        var inputUsageLimit = document.getElementById('edit_discount_usage_limit');
        if (inputUsageLimit) {
          inputUsageLimit.value = usageLimit;
        }

        var inputPerUserLimit = document.getElementById('edit_discount_per_user_limit');
        if (inputPerUserLimit) {
          inputPerUserLimit.value = perUserLimit;
        }

        var inputStart = document.getElementById('edit_discount_start');
        if (inputStart) {
          inputStart.value = start;
        }

        var inputEnd = document.getElementById('edit_discount_end');
        if (inputEnd) {
          inputEnd.value = end;
        }

        var selectStatus = document.getElementById('edit_discount_status');
        if (selectStatus) {
          selectStatus.value = status || 'ACTIVE';
          selectStatus.dispatchEvent(new Event('change'));
        }

        editModal.show();
      });
    });
  }
});

// ==== BLOCK 2: xử lý lỗi validate + auto mở lại modal ====
document.addEventListener('DOMContentLoaded', function () {
  if (!window.bootstrap) {
    return;
  }

  function resetDiscountModalErrors(modalEl) {
    if (!modalEl) {
      return;
    }

    var invalids = modalEl.querySelectorAll('.is-invalid');
    invalids.forEach(function (el) {
      el.classList.remove('is-invalid');
    });

    var feedbacks = modalEl.querySelectorAll('.invalid-feedback');
    feedbacks.forEach(function (el) {
      el.classList.remove('d-block');
    });
  }

  var createModalEl = document.getElementById('discountCreateModal');
  var editModalEl = document.getElementById('discountEditModal');

  if (createModalEl) {
    createModalEl.addEventListener('hidden.bs.modal', function () {
      resetDiscountModalErrors(createModalEl);
    });
  }

  if (editModalEl) {
    editModalEl.addEventListener('hidden.bs.modal', function () {
      resetDiscountModalErrors(editModalEl);
    });
  }

  var createState = document.getElementById('__discountCreateState');
  if (createState && createState.getAttribute('data-has-errors') === '1' && createModalEl) {
    var cm = window.bootstrap.Modal.getOrCreateInstance(createModalEl);
    cm.show();
  }

  var editState = document.getElementById('__discountEditState');
  if (editState && editState.getAttribute('data-has-errors') === '1' && editModalEl) {
    var em = window.bootstrap.Modal.getOrCreateInstance(editModalEl);
    em.show();
  }
});

// ==== BLOCK 3: modal confirm xoá ====
document.addEventListener('DOMContentLoaded', function () {
  var confirmModalEl = document.getElementById('uiConfirmModal');
  if (!confirmModalEl || !window.bootstrap) {
    return;
  }

  var confirmModal = window.bootstrap.Modal.getOrCreateInstance(confirmModalEl);
  var msgEl = document.getElementById('uiConfirmMessage');
  var okBtn = document.getElementById('uiConfirmOkBtn');
  var pendingForm = null;

  var deleteForms = document.querySelectorAll('.js-discount-delete-form');
  if (!deleteForms.length || !okBtn) {
    return;
  }

  deleteForms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      pendingForm = form;

      if (msgEl) {
        var msg = form.getAttribute('data-confirm-message') || 'Bạn có chắc muốn thực hiện thao tác này?';
        msgEl.textContent = msg;
      }

      confirmModal.show();
    });
  });

  okBtn.addEventListener('click', function () {
    if (!pendingForm) {
      confirmModal.hide();
      return;
    }

    confirmModal.hide();
    pendingForm.submit();
    pendingForm = null;
  });
});
