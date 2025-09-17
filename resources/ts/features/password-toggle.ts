export function bindPasswordToggles(root: Document | HTMLElement = document): void {
  const scope = root instanceof Document ? root : (root.ownerDocument ?? document);
  root.querySelectorAll<HTMLButtonElement>('[data-toggle-password]').forEach((btn) => {
    const targetId = btn.getAttribute('data-target');
    if (!targetId) return;
    const input = scope.getElementById(targetId) as HTMLInputElement | null;
    if (!input) return;

    btn.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      const eye = btn.querySelector<HTMLElement>('[data-eye]');
      const eyeOff = btn.querySelector<HTMLElement>('[data-eye-off]');
      if (eye && eyeOff) {
        eye.classList.toggle('hidden', !show);
        eyeOff.classList.toggle('hidden', show);
      }
    });
  });
}