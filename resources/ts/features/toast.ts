type ToastType = 'success' | 'error' | 'warning' | 'info';
type ToastOpts = { autohide?: boolean; delay?: number; staggerMs?: number };

function ensureContainer(doc: Document): HTMLElement {
  let c = doc.querySelector('.tw-toast-container') as HTMLElement | null;
  if (!c) {
    c = doc.createElement('div');
    c.className = 'tw-toast-container';
    doc.body.appendChild(c);
  }
  return c;
}

function iconSvg(type: ToastType): string {
  if (type === 'success') return '<svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>';
  if (type === 'error')   return '<svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>';
  if (type === 'warning') return '<svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>';
  return '<svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 16h-1v-4h-1m1-4h.01"/><circle cx="12" cy="12" r="9"/></svg>';
}

function slideClose(box: HTMLElement) {
  const c = box.parentElement as HTMLElement | null;
  if (!c) { box.remove(); return; }

  // 1) First: đo vị trí top của siblings còn lại
  const siblings = Array.from(c.children).filter(n => n !== box) as HTMLElement[];
  const firstTop = new Map<HTMLElement, number>();
  siblings.forEach(el => firstTop.set(el, el.getBoundingClientRect().top));

  // 2) Ghost của box để chạy exit
  const r = box.getBoundingClientRect();
  const ghost = box.cloneNode(true) as HTMLElement;
  ghost.style.position = 'fixed';
  ghost.style.left = `${r.left}px`;
  ghost.style.top = `${r.top}px`;
  ghost.style.width = `${r.width}px`;
  ghost.style.height = `${r.height}px`;
  ghost.style.margin = '0';
  ghost.style.pointerEvents = 'none';
  ghost.setAttribute('aria-hidden', 'true');
  document.body.appendChild(ghost);

  // 3) Remove box thật để siblings reflow
  box.remove();

  // 4) Last: FLIP siblings translate từ vị trí cũ -> mới
  requestAnimationFrame(() => {
    siblings.forEach(el => {
      const lastTop = el.getBoundingClientRect().top;
      const dy = (firstTop.get(el) ?? lastTop) - lastTop; // dương => dịch xuống, âm => dịch lên
      if (Math.abs(dy) < 0.5) return;
      el.style.transform = `translateY(${dy}px)`;
      // kích hoạt transition ở frame kế để không giật
      requestAnimationFrame(() => {
        el.style.transition = 'transform 260ms cubic-bezier(0.22,1,0.36,1)';
        el.style.transform = 'translateY(0)';
        const cleanup = () => { el.style.transition = ''; el.style.transform = ''; el.removeEventListener('transitionend', cleanup); };
        el.addEventListener('transitionend', cleanup);
      });
    });
  });

  // 5) Chạy exit cho ghost rồi xoá
  ghost.classList.add('tw-toast-exit');
  ghost.addEventListener('animationend', () => ghost.remove(), { once: true });
}

export function show(doc: Document, type: ToastType, html: string, opts: ToastOpts = {}): void {
  const { autohide = true, delay = 8000, staggerMs = 350 } = opts;
  const c = ensureContainer(doc);

  const box = doc.createElement('div');
  box.className = `tw-toast tw-toast-${type}`;
  box.setAttribute('role', 'status');
  box.innerHTML = `
    <div class="tw-toast-inner flex items-start gap-3">
      <span class="tw-toast-icon">${iconSvg(type)}</span>
      <div class="tw-toast-body">${html}</div>
      <button class="tw-toast-close" type="button" aria-label="Đóng">✕</button>
    </div>
    ${autohide ? '<div class="tw-toast-progress"></div>' : ''}
  `;

  // Click bất kỳ để đóng
  box.addEventListener('click', () => slideClose(box));
  box.querySelector<HTMLButtonElement>('.tw-toast-close')?.addEventListener('click', (e) => { e.stopPropagation(); slideClose(box); });

  c.appendChild(box);

  if (autohide) {
    const idx = Array.prototype.indexOf.call(c.children, box) as number;
    const finalDelay = delay + Math.max(0, idx) * staggerMs;

    const prog = box.querySelector<HTMLElement>('.tw-toast-progress');
    if (prog) {
      requestAnimationFrame(() => {
        prog.style.transition = `transform ${finalDelay}ms linear`;
        prog.style.transform = 'scaleX(0)';
      });
    }
    window.setTimeout(() => slideClose(box), finalDelay);
  }
}

export function init(doc: Document): void {
  const nodes = Array.from(doc.querySelectorAll<HTMLElement>('[data-toast]'));
  if (nodes.length === 0) return;
  for (const el of nodes) {
    const type = (el.getAttribute('data-toast') || 'info') as ToastType;
    const autohide = (el.getAttribute('data-autohide') ?? 'true') !== 'false';
    const delay = Number(el.getAttribute('data-delay-ms') || 8000);
    const stagger = Number(el.getAttribute('data-stagger-ms') || 350);
    show(doc, type, el.innerHTML, { autohide, delay, staggerMs: stagger });
    el.remove();
  }
}