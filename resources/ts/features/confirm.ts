function setOpen(el: HTMLElement, open: boolean) {
  el.dataset.open = open ? '1' : '0';
  el.dispatchEvent(new CustomEvent(open ? 'modal:open' : 'modal:closed', { bubbles: true }));
}

export function show(message?: string): Promise<boolean> {
  const modalEl = document.getElementById('uiConfirmModal') as HTMLElement | null;
  const msgEl = document.getElementById('uiConfirmMessage') as HTMLElement | null;
  const okBtn = document.getElementById('uiConfirmOk') as HTMLButtonElement | null;
  const cancelBtn = document.getElementById('uiConfirmCancel') as HTMLButtonElement | null;

  if (!modalEl || !msgEl || !okBtn || !cancelBtn) {
    return Promise.resolve(window.confirm(message || 'Bạn chắc không?')); // fallback an toàn
  }

  msgEl.textContent = message || 'Bạn chắc không?';

  return new Promise<boolean>((resolve) => {
    const onOk = () => { cleanup(); resolve(true); };
    const onCancel = () => { cleanup(); resolve(false); };
    const onBackdrop = (e: MouseEvent) => { if (e.target === modalEl) { cleanup(); resolve(false); } };
    const onKey = (e: KeyboardEvent) => { if (e.key === 'Escape') { cleanup(); resolve(false); } };

    const cleanup = () => {
      okBtn.removeEventListener('click', onOk);
      cancelBtn.removeEventListener('click', onCancel);
      modalEl.removeEventListener('click', onBackdrop);
      document.removeEventListener('keydown', onKey);
      setOpen(modalEl, false);
    };

    okBtn.addEventListener('click', onOk);
    cancelBtn.addEventListener('click', onCancel);
    modalEl.addEventListener('click', onBackdrop);
    document.addEventListener('keydown', onKey);
    setOpen(modalEl, true);
  });
}

/** Tự gắn xác nhận cho [data-confirm] và form[data-confirm] */
export function bind(root: Document | HTMLElement = document): void {
  // Link, button
  root.addEventListener('click', async (e) => {
    const target = (e.target as HTMLElement).closest<HTMLElement>('[data-confirm]');
    if (!target) return;

    // Bỏ qua nếu có data-skip-confirm
    if (target.hasAttribute('data-skip-confirm')) return;

    // Form submit button: để submit qua logic submit
    if (target instanceof HTMLButtonElement && target.type === 'submit') return;

    e.preventDefault();
    const msg = target.getAttribute('data-confirm') || 'Bạn chắc không?';
    if (await show(msg)) {
      // Link => điều hướng, Button => click lần nữa với skip
      if (target instanceof HTMLAnchorElement && target.href) {
        window.location.assign(target.href);
      } else {
        target.setAttribute('data-skip-confirm', '1');
        target.click();
        target.removeAttribute('data-skip-confirm');
      }
    }
  });

  // Form
  root.addEventListener('submit', async (e) => {
    const form = e.target as HTMLFormElement;
    if (!form.matches('form[data-confirm]')) return;
    if ((form as any).__confirming) return; // tránh vòng lặp

    e.preventDefault();
    const msg = form.getAttribute('data-confirm') || 'Bạn chắc không?';
    if (await show(msg)) {
      (form as any).__confirming = true;
      form.submit();
      (form as any).__confirming = false;
    }
  });
}

declare global {
  interface Window { uiConfirm?: typeof show; }
}
if (typeof window !== 'undefined') {
  window.uiConfirm = show; // tiện gọi tay khi cần
}
