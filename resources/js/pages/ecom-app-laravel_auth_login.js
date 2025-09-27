// Generated from inline <script> blocks in: ecom-app-laravel/resources/views/auth/login.blade.php
// Each section preserves original order and approximate line ranges.

/* ===== BEGIN inline script #1 (lines 111-127) ===== */
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".toggle-password").forEach(function (btn) {
      btn.addEventListener("click", function () {
        const wrap = this.closest(".toggle-wrap");         // input+icon wrapper
        const input = wrap ? wrap.querySelector("input") : null;
        const icon  = this.querySelector("i");
        if (!input || !icon) return;

        const isPwd = input.type === "password";
        input.type = isPwd ? "text" : "password";
        icon.classList.toggle("fa-eye", isPwd);
        icon.classList.toggle("fa-eye-slash", !isPwd);
      });
    });
  });
/* ===== END inline script #1 ===== */
