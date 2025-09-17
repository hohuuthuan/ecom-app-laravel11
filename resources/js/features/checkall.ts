import { on, qsa } from '../core/dom';

export interface CheckAllConfig {
  master: string; // selector cho checkbox tổng
  item: string;   // selector cho checkbox dòng
  onChange?:(ids: Array<string|number>) => void;
}

/**
 * Khởi tạo check-all theo selector. Làm việc theo "phạm vi gần nhất":
 * - Khi thay đổi master, chỉ tác động các item trong cùng bảng/container.
 * - Khi thay đổi item, cập nhật trạng thái master (checked/indeterminate).
 */
export function init(root: HTMLElement, cfg: CheckAllConfig): void {
  // master change
  on(root, 'change', cfg.master, (ev, masterEl) => {
    const master = masterEl as HTMLInputElement;
    const scope = findScope(master, root);
    const items = qsa<HTMLInputElement>(scope, cfg.item);
    for (const it of items) {
      if (!it.disabled) {
        it.checked = master.checked;
      }
    }
    updateMasterState(scope, cfg);
    emitChange(scope, cfg);
  });

  // item change
  on(root, 'change', cfg.item, (ev, itemEl) => {
    const scope = findScope(itemEl, root);
    updateMasterState(scope, cfg);
    emitChange(scope, cfg);
  });

  // helper: initial sync
  const masters = qsa<HTMLInputElement>(root, cfg.master);
  for (const m of masters) {
    const scope = findScope(m, root);
    updateMasterState(scope, cfg);
  }
}

function findScope(el: Element, fallbackRoot: HTMLElement): HTMLElement {
  const table = el.closest('table');
  if (table) {
    return table as HTMLElement;
  }
  const form = el.closest('form');
  if (form) {
    return form as HTMLElement;
  }
  const section = el.closest('[data-checkall-scope]');
  if (section) {
    return section as HTMLElement;
  }
  return fallbackRoot;
}

function updateMasterState(scope: HTMLElement, cfg: CheckAllConfig): void {
  const master = scope.querySelector(cfg.master) as HTMLInputElement | null;
  if (!master) {
    return;
  }
  const items = qsa<HTMLInputElement>(scope, cfg.item).filter(i => !i.disabled);
  const checked = items.filter(i => i.checked);
  if (items.length === 0) {
    master.checked = false;
    master.indeterminate = false;
    return;
  }
  master.checked = checked.length === items.length;
  master.indeterminate = checked.length > 0 && checked.length < items.length;
}

function emitChange(scope: HTMLElement, cfg: CheckAllConfig): void {
  if (!cfg.onChange) {
    return;
  }
  const items = qsa<HTMLInputElement>(scope, cfg.item).filter(i => i.checked);
  const ids: Array<string|number> = items.map(i => {
    const v = i.value;
    const n = Number(v);
    return Number.isFinite(n) ? n : v;
  });
  cfg.onChange(ids);
}
