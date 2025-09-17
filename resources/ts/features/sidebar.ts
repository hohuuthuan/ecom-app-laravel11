export function init(doc: Document): void {
  const btn = doc.getElementById('sidebarToggle') as HTMLButtonElement | null;
  const body = doc.body;
  if (!btn) return;
  btn.addEventListener('click', () => {
    const collapsed = body.dataset.sidebar === 'collapsed';
    body.dataset.sidebar = collapsed ? 'expanded' : 'collapsed';
  });
}
