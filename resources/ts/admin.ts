import 'bootstrap-icons/font/bootstrap-icons.css';

(function(){
  const KEY = 'sb_state_v1';
  const body = document.body;
  const btn = document.getElementById('toggleSidebar');

  const saved = localStorage.getItem(KEY);
  if(saved==='collapsed' || saved==='expanded'){
    body.setAttribute('data-sb', saved);
  }

  function toggleSidebar():void{
    const isCollapsed = body.getAttribute('data-sb') === 'collapsed';
    const nextState = isCollapsed ? 'expanded' : 'collapsed';
    body.setAttribute('data-sb', nextState);
    if(btn){ btn.setAttribute('aria-pressed', String(!isCollapsed)); }
    localStorage.setItem(KEY, nextState);
  }

  if(btn){ btn.addEventListener('click', toggleSidebar); }

  const links = document.querySelectorAll<HTMLAnchorElement>('.sidebar-link');
  links.forEach(l=>{
    l.addEventListener('click', function(){
      links.forEach(x=>x.classList.remove('active'));
      this.classList.add('active');
    });
  });
})();
