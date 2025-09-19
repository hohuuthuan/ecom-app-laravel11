export function initSidebarUI(root: Document | Element = document): void {
  const KEY = 'sb_state_v1';
  const html = document.documentElement;
  const body = document.body;
  const btn = root.querySelector<HTMLElement>('#toggleSidebar');

  // trạng thái đã được set sớm trên <html> ở <head>, đồng bộ về <body>
  const saved = localStorage.getItem(KEY);
  const initial = (saved === 'collapsed' || saved === 'expanded') ? saved : 'expanded';
  html.setAttribute('data-sb', initial);
  body.setAttribute('data-sb', initial);

  function setState(next: 'collapsed'|'expanded'){
    html.setAttribute('data-sb', next);
    body.setAttribute('data-sb', next);
    btn?.setAttribute('aria-pressed', String(next === 'expanded'));
    localStorage.setItem(KEY, next);
  }

  function toggleSidebar(): void {
    const isCollapsed = (body.getAttribute('data-sb') === 'collapsed');
    setState(isCollapsed ? 'expanded' : 'collapsed');
  }

  btn?.addEventListener('click', toggleSidebar);

  // Active link demo
  const links = root.querySelectorAll<HTMLAnchorElement>('.sidebar-link');
  links.forEach(l => l.addEventListener('click', function(){ links.forEach(x=>x.classList.remove('active')); this.classList.add('active'); }));

  // đánh dấu ready => bật lại transition
  requestAnimationFrame(() => { document.body.classList.add('sb-ready'); });
}
