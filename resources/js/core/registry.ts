/**
 * Registry: nạp module trang theo data-page, chạy được cả DEV lẫn BUILD.
 * Quy ước: data-page="admin.accounts.index" -> ../pages/admin.accounts.index.ts
 */

const pages = import.meta.glob('../pages/**/*.ts');

export async function boot(root: HTMLElement): Promise<void> {
  const pageId: string = root.dataset.page || '';
  if (!pageId) {
    return;
  }

  const key = `../pages/${pageId}.ts`;
  const loader = (pages as Record<string, () => Promise<any>>)[key];

  if (!loader) {
    console.warn('[app] page module not found:', key);
    return;
  }

  try {
    const mod = await loader();
    const init = (mod as any)?.default;
    if (typeof init === 'function') {
      await init(root);
    }
  } catch (err) {
    console.error('[app] failed to load page module:', key, err);
  }
}
