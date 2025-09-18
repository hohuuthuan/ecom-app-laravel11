// resources/ts/features/admin-shell.ts
const STORAGE_KEY = 'admin:sidebar:collapsed';

export function initAdminShell(doc: Document): void {
  const sidebar = doc.getElementById('adminSidebar');
  const toggle = doc.getElementById('btnSidebarToggle');

  if (!sidebar || !toggle) return;

  // apply saved state
  const saved = localStorage.getItem(STORAGE_KEY);
  if (saved === '1') sidebar.classList.add('is-collapsed');

  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('is-collapsed');
    const collapsed = sidebar.classList.contains('is-collapsed') ? '1' : '0';
    localStorage.setItem(STORAGE_KEY, collapsed);
  });
}
