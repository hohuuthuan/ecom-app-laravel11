(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const wrap = this.closest('.toggle-wrap');
        const input = wrap ? wrap.querySelector('input') : null;
        const icon = this.querySelector('i');
        if (!input || !icon) { return; }
        const isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        icon.classList.toggle('fa-eye', isPwd);
        icon.classList.toggle('fa-eye-slash', !isPwd);
      });
    });
  });
})();