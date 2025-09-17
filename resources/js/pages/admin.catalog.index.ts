import { init as checkAll } from '../features/checkall';
import { init as tabsUrl } from '../features/tabs-url';
import { bind as bindConfirm } from '../features/confirm';
import { initSelect2 } from '../features/select2';
import { attachNameToSlug } from '../features/slugify';

declare global {
  interface Window { bootstrap?: any; }
}

export default async function init(root: HTMLElement): Promise<void> {
  // Tabs ↔ URL
  tabsUrl(root, { param: 'tab' });

  // Select2 cho filter trên trang
  initSelect2(root);

  // ===== Category scope =====
  const catPane = root.querySelector('#category-pane') as HTMLElement | null;
  if (catPane) {
    // Checkall
    checkAll(catPane, {
      master: '[data-check-all], thead input[type="checkbox"]',
      item: '[data-row-check], tbody input[type="checkbox"]',
      onChange: (ids) => toggleBulkBtn('catBtnBulkDelete', ids.length > 0)
    });

    // Bulk button → fill form hidden + submit (modal confirm đã bind dưới)
    const catBulkBtn = catPane.querySelector('#catBtnBulkDelete') as HTMLButtonElement | null;
    if (catBulkBtn) {
      catBulkBtn.addEventListener('click', () => {
        const form = document.getElementById('catBulkForm') as HTMLFormElement | null;
        const holder = document.getElementById('catBulkIds') as HTMLElement | null;
        if (!form || !holder) { return; }
        fillBulkIds(catPane, holder, '[data-row-check]');
        form.requestSubmit();
      });
    }

    // Confirm cho form bulk
    bindConfirm(root, { selector: '#catBulkForm[data-confirm]' });

    // Edit Category button → open modal and seed form
    catPane.addEventListener('click', (ev) => {
      const btn = (ev.target as HTMLElement).closest('.btnCateEdit') as HTMLElement | null;
      if (!btn) { return; }
      openCategoryModal(btn);
    });
  }

  // ===== Brand scope =====
  const brandPane = root.querySelector('#brand-pane') as HTMLElement | null;
  if (brandPane) {
    checkAll(brandPane, {
      master: '[data-check-all], thead input[type="checkbox"]',
      item: '[data-row-check], tbody input[type="checkbox"]',
      onChange: (ids) => toggleBulkBtn('brandBtnBulkDelete', ids.length > 0)
    });

    const brandBulkBtn = brandPane.querySelector('#brandBtnBulkDelete') as HTMLButtonElement | null;
    if (brandBulkBtn) {
      brandBulkBtn.addEventListener('click', () => {
        const form = document.getElementById('brandBulkForm') as HTMLFormElement | null;
        const holder = document.getElementById('brandBulkIds') as HTMLElement | null;
        if (!form || !holder) { return; }
        fillBulkIds(brandPane, holder, '[data-row-check]');
        form.requestSubmit();
      });
    }

    bindConfirm(root, { selector: '#brandBulkForm[data-confirm]' });

    brandPane.addEventListener('click', (ev) => {
      const btn = (ev.target as HTMLElement).closest('.btnBrandEdit') as HTMLElement | null;
      if (!btn) { return; }
      openBrandModal(btn);
    });
  }

  // Confirm cho form DELETE từng dòng (Category + Brand)
  bindConfirm(root, { selector: 'form[data-confirm]' });

  // Khi modal mở → init Select2 trong modal + wire name→slug
  document.addEventListener('shown.bs.modal', (e: any) => {
    const modal = e.target as HTMLElement;
    if (!modal || !(modal.id === 'uiCategoryModal' || modal.id === 'uiBrandModal')) {
      return;
    }
    initSelect2(modal);
    const name = modal.querySelector('input[name="name"]') as HTMLInputElement | null;
    const slug = modal.querySelector('input[name="slug"]') as HTMLInputElement | null;
    if (name && slug) {
      attachNameToSlug(name, slug);
    }
  });
}

/* ---------- helpers ---------- */

function toggleBulkBtn(id: string, enabled: boolean): void {
  const btn = document.getElementById(id) as HTMLButtonElement | null;
  if (!btn) { return; }
  btn.disabled = !enabled;
}

function fillBulkIds(scope: HTMLElement, holder: HTMLElement, itemSelector: string): void {
  holder.innerHTML = '';
  const items = scope.querySelectorAll<HTMLInputElement>(itemSelector);
  for (const it of items) {
    if (it.checked) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = it.value;
      holder.appendChild(input);
    }
  }
}

function openCategoryModal(btn: HTMLElement): void {
  const modalEl = document.getElementById('uiCategoryModal') as HTMLElement | null;
  if (!modalEl) { return; }
  const form = modalEl.querySelector('form') as HTMLFormElement | null;
  if (!form) { return; }

  // Mode update
  const updateUrl = btn.getAttribute('data-update-url') || '';
  form.action = updateUrl;
  const methodInput = form.querySelector('input[name="_method"]') as HTMLInputElement | null;
  if (methodInput) { methodInput.value = 'PUT'; }

  // Seed fields
  setValue(modalEl, 'input[name="name"]', btn.getAttribute('data-name') || '');
  setValue(modalEl, 'input[name="slug"]', btn.getAttribute('data-slug') || '');
  setValue(modalEl, 'textarea[name="description"]', btn.getAttribute('data-description') || '');
  setSelect(modalEl, 'select[name="status"]', btn.getAttribute('data-status') || '');

  // Preview image (nếu có phần tử data-preview)
  const preview = modalEl.querySelector<HTMLElement>('[data-preview]');
  if (preview) {
    const url = btn.getAttribute('data-image') || '';
    preview.setAttribute('src', url);
  }

  showBsModal(modalEl);
}

function openBrandModal(btn: HTMLElement): void {
  const modalEl = document.getElementById('uiBrandModal') as HTMLElement | null;
  if (!modalEl) { return; }
  const form = modalEl.querySelector('form') as HTMLFormElement | null;
  if (!form) { return; }

  // Mode update
  const updateUrl = btn.getAttribute('data-update-url') || '';
  form.action = updateUrl;
  const methodInput = form.querySelector('input[name="_method"]') as HTMLInputElement | null;
  if (methodInput) { methodInput.value = 'PUT'; }

  // Seed fields
  setValue(modalEl, 'input[name="name"]', btn.getAttribute('data-name') || '');
  setValue(modalEl, 'input[name="slug"]', btn.getAttribute('data-slug') || '');
  setValue(modalEl, 'textarea[name="description"]', btn.getAttribute('data-description') || '');
  setSelect(modalEl, 'select[name="status"]', btn.getAttribute('data-status') || '');

  const preview = modalEl.querySelector<HTMLElement>('[data-preview]');
  if (preview) {
    const url = btn.getAttribute('data-image') || '';
    preview.setAttribute('src', url);
  }

  showBsModal(modalEl);
}

function setValue(scope: HTMLElement, selector: string, value: string): void {
  const el = scope.querySelector(selector) as HTMLInputElement | HTMLTextAreaElement | null;
  if (el) { el.value = value; }
}

function setSelect(scope: HTMLElement, selector: string, value: string): void {
  const el = scope.querySelector(selector) as HTMLSelectElement | null;
  if (!el) { return; }
  el.value = value;
  // Nếu dùng select2, trigger change để UI đồng bộ
  const $ = (window as any).jQuery || (window as any).$;
  if ($ && $.fn && $.fn.select2) {
    $(el).trigger('change.select2');
  }
}

function showBsModal(modalEl: HTMLElement): void {
  if (window.bootstrap && window.bootstrap.Modal) {
    const inst = window.bootstrap.Modal.getOrCreateInstance(modalEl);
    inst.show();
  } else {
    // Fallback
    (modalEl as any).style.display = 'block';
  }
}
