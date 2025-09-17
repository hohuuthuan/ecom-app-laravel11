let el: HTMLElement | null = null;

function ensure(): HTMLElement {
  if (el !== null) { return el; }
  let node = document.getElementById('app-loading') as HTMLElement | null;
  if (!node) {
    const tpl = document.getElementById('tpl-app-loading') as HTMLTemplateElement | null;
    if (!tpl) {
      throw new Error('[loading] Missing #app-loading or #tpl-app-loading');
    }
    const frag = tpl.content.cloneNode(true) as DocumentFragment;
    document.body.appendChild(frag);
    node = document.getElementById('app-loading') as HTMLElement | null;
    if (!node) { throw new Error('[loading] Failed to mount template'); }
  }
  el = node;
  return el;
}


export function show(text?: string): void {
  const root = ensure();
  const t = root.querySelector('.app-loading__text') as HTMLElement | null;
  if (t) {
    const msg: string =
      typeof text === 'string' && text.trim() !== ''
        ? text
        : (root.getAttribute('data-default-text') || 'Đang xử lý…');
    t.textContent = msg;
  }
  root.classList.remove('hidden');
}

export function hide(): void {
  ensure().classList.add('hidden');
}
