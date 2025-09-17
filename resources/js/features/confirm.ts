import { on } from '../core/dom';

declare global {
  interface Window { bootstrap?: any; }
}

function getModalElements() {
  const modalEl = document.getElementById('uiConfirmModal') as HTMLElement | null;
  const msgEl = document.getElementById('uiConfirmMessage') as HTMLElement | null;
  const okBtn = document.getElementById('uiConfirmOk') as HTMLButtonElement | null;
  return { modalEl, msgEl, okBtn };
}

function showModal(message: string): Promise<boolean> {
  const { modalEl, msgEl, okBtn } = getModalElements();
  if (!modalEl || !msgEl || !okBtn || !(window.bootstrap && window.bootstrap.Modal)) {
    // Fallback nếu thiếu Bootstrap/markup
    return Promise.resolve(window.confirm(message));
  }

  msgEl.textContent = message || 'Bạn chắc chắn muốn thực hiện?';

  return new Promise<boolean>((resolve) => {
    const bs = window.bootstrap.Modal.getOrCreateInstance(modalEl);
    const onOk = () => { cleanup(); resolve(true); };
    const onHide = () => { cleanup(); resolve(false); };

    function cleanup() {
      okBtn!.removeEventListener('click', onOk);
      modalEl!.removeEventListener('hidden.bs.modal', onHide as any);
    }

    okBtn.addEventListener('click', onOk);
    modalEl.addEventListener('hidden.bs.modal', onHide as any);

    bs.show();
  });
}

export interface ConfirmOptions {
  selector: string;                    // ví dụ: [data-confirm] trên <form>
  getMessage?:(el: Element) => string; // tuỳ biến message
}

/**
 * Bắt submit form có [data-confirm], bật modal, xác nhận xong mới submit.
 * Chuẩn hoá: dùng FORM cho xoá (DELETE). Không dùng <a> cho routes DELETE.
 */
export function bind(root: HTMLElement, options: ConfirmOptions): void {
  on(root, 'submit', options.selector, async (ev, el) => {
    const form = el as HTMLFormElement;
    const msg = options.getMessage ? options.getMessage(form) : (form.getAttribute('data-confirm') || 'Xác nhận thao tác?');
    ev.preventDefault();
    ev.stopPropagation();

    const ok = await showModal(msg);
    if (ok) {
      form.submit();
    }
  });
}
