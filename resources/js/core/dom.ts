/* DOM helpers: query, queryAll, event delegation, dataset parsers */

export function qs<T extends Element>(root: ParentNode, selector: string): T | null {
  return root.querySelector(selector) as T | null;
}

export function qsa<T extends Element>(root: ParentNode, selector: string): T[] {
  return Array.from(root.querySelectorAll(selector)) as T[];
}

type Handler = (ev: Event, matched: Element) => void;

/**
 * on: add listener. If selector provided, use delegation.
 * Returns an unsubscribe function.
 */
export function on(
  root: Element | Document,
  type: string,
  selectorOrHandler: string | Handler,
  handlerOpt?: Handler
): () => void {
  const delegated: boolean = typeof selectorOrHandler === 'string';
  const selector: string = delegated ? (selectorOrHandler as string) : '';
  const handler: Handler = delegated ? (handlerOpt as Handler) : (selectorOrHandler as Handler);

  const listener = (ev: Event) => {
    if (!delegated) {
      handler(ev, ev.target as Element);
      return;
    }
    const target = ev.target as Element | null;
    if (!target) {
      return;
    }
    const matched = target.closest(selector);
    if (matched && (root as Element).contains(matched)) {
      handler(ev, matched);
    }
  };

  root.addEventListener(type, listener as EventListener, false);
  return () => {
    root.removeEventListener(type, listener as EventListener, false);
  };
}

/** Read boolean from data-attribute like data-autohide="true" */
export function dataBool(el: Element, name: string, fallback: boolean = false): boolean {
  const v = el.getAttribute(`data-${name}`);
  if (v === null) {
    return fallback;
  }
  if (v === '' || v === 'true' || v === '1') {
    return true;
  }
  if (v === 'false' || v === '0') {
    return false;
  }
  return fallback;
}

/** Read number from data-attribute like data-delay-ms="3000" */
export function dataNum(el: Element, name: string, fallback: number = 0): number {
  const v = el.getAttribute(`data-${name}`);
  if (v === null || v.trim() === '') {
    return fallback;
  }
  const n = Number(v);
  return Number.isFinite(n) ? n : fallback;
}
