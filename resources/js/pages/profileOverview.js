(function () {
  var navLinks = document.querySelectorAll('.profile-nav-link');
  var sections = document.querySelectorAll('.profile-section');

  if (navLinks.length && sections.length) {
    function setActiveTab(target) {
      if (!target) return;

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
        if (!target) return;

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

  var modalEl = document.getElementById('editProfileModal');
  if (!modalEl) {
    return;
  }

  modalEl.addEventListener('hidden.bs.modal', function () {
    // Bỏ class is-invalid trên các input/select trong modal
    modalEl.querySelectorAll('.is-invalid').forEach(function (el) {
      el.classList.remove('is-invalid');
    });

    // Ẩn các dòng lỗi nhỏ bên dưới input (không xoá DOM)
    modalEl.querySelectorAll('.invalid-feedback').forEach(function (el) {
      el.classList.add('d-none');
    });

    // Ẩn alert lỗi chung (nếu có)
    modalEl.querySelectorAll('.alert').forEach(function (el) {
      el.classList.add('d-none');
    });

    // Reset giá trị input/select về data-original-value (dữ liệu user hiện tại)
    modalEl.querySelectorAll('[data-original-value]').forEach(function (el) {
      var original = el.getAttribute('data-original-value') || '';

      if (el.tagName === 'SELECT') {
        el.value = original;

        if (window.jQuery && jQuery(el).hasClass('setupSelect2')) {
          jQuery(el).val(original).trigger('change.select2');
        }
      } else {
        el.value = original;
      }
    });
  });
})();
