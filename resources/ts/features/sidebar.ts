export function initSidebarUI(root: Document | Element = document): void {
  const KEY = 'sb_state_v1';
  const body = document.body;
  const btn = root.querySelector<HTMLElement>('#toggleSidebar');

  const saved = localStorage.getItem(KEY);
  if (saved === 'collapsed' || saved === 'expanded') {
    body.setAttribute('data-sb', saved);
    btn?.setAttribute('aria-pressed', String(saved === 'expanded'));
  }

  function toggleSidebar(): void {
    const isCollapsed = body.getAttribute('data-sb') === 'collapsed';
    const nextState = isCollapsed ? 'expanded' : 'collapsed';
    body.setAttribute('data-sb', nextState);
    btn?.setAttribute('aria-pressed', String(!isCollapsed));
    localStorage.setItem(KEY, nextState);
  }

  btn?.addEventListener('click', toggleSidebar);

  const links = root.querySelectorAll<HTMLAnchorElement>('.sidebar-link');
  links.forEach((l) => {
    l.addEventListener('click', function () {
      links.forEach((x) => x.classList.remove('active'));
      this.classList.add('active');
    });
  });
}
