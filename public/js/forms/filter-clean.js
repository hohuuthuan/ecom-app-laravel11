(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form.filter-form').forEach(function (form) {
      form.addEventListener('submit', function () {
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
          if (!el.value) { el.removeAttribute('name'); }
        });
      });
    });
  });
})();