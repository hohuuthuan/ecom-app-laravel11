/* ====== Minimal JS cho UX mượt ====== */
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

const state = {
  category: 'all',
  query: '',
  cart: Number(localStorage.getItem('cartCount') || 0),
  theme: localStorage.getItem('theme') || 'light' // light-first
};

document.addEventListener('DOMContentLoaded', () => {
  // Theme init
  if(state.theme === 'dark'){ document.documentElement.classList.add('dark'); }
  updateCartUI();

  // Nav toggle
  const navBtn = $('#navToggle');
  const nav = $('#siteNav');
  navBtn.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    navBtn.setAttribute('aria-expanded', String(open));
  });

  // Category filter
  $$('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
      $$('.chip').forEach(c => c.classList.remove('is-active'));
      chip.classList.add('is-active');
      state.category = chip.dataset.category;
      filterBooks();
    });
  });

  // Search submit
  $('#searchForm').addEventListener('submit', e => {
    e.preventDefault();
    state.query = ($('#searchInput').value || '').trim().toLowerCase();
    filterBooks();
  });

  // Add to cart
  $$('.add-to-cart').forEach(btn => btn.addEventListener('click', () => {
    addToCart(btn.dataset.id);
    kawaiiBurst(btn);
  }));

  // Subscribe
  $('#subForm').addEventListener('submit', e => {
    e.preventDefault();
    const email = $('#emailInput').value.trim();
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ toast('Email không hợp lệ'); return; }
    toast('Đã gửi mã giảm 20% tới email');
    $('#emailInput').value = '';
  });

  // Theme toggle
  $('#themeToggle').addEventListener('click', () => {
    const isDark = document.documentElement.classList.toggle('dark');
    state.theme = isDark ? 'dark' : 'light';
    localStorage.setItem('theme', state.theme);
  });
});

/* Filter logic */
function filterBooks(){
  const allCards = $$('.book-card');
  const { category, query } = state;
  let visible = 0;

  for(const card of allCards){
    const title = (card.dataset.title || '').toLowerCase();
    const author = (card.dataset.author || '').toLowerCase();
    const cat = card.dataset.category || 'all';
    const matchCat = category === 'all' || cat === category;
    const matchQuery = !query || title.includes(query) || author.includes(query);

    if(matchCat && matchQuery){
      card.style.display = '';
      visible++;
    }else{
      card.style.display = 'none';
    }
  }

  if(visible === 0){ toast('Không tìm thấy sách phù hợp'); }
}

/* Cart */
function addToCart(){
  state.cart += 1;
  localStorage.setItem('cartCount', String(state.cart));
  updateCartUI();
  toast('Đã thêm vào giỏ');
}
function updateCartUI(){
  $('#cartCount').textContent = String(state.cart);
}

/* Toast */
let toastTimer = null;
function toast(msg){
  const t = $('#toast');
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 1800);
}

/* Hiệu ứng “kawaii confetti” nhỏ khi thêm giỏ */
function kawaiiBurst(anchor){
  const rect = anchor.getBoundingClientRect();
  const cx = rect.left + rect.width/2 + window.scrollX;
  const cy = rect.top + rect.height/2 + window.scrollY;
  const n = 10;
  for(let i=0;i<n;i++){
    const s = document.createElement('span');
    s.className = 'mini-spark';
    s.style.left = cx + 'px';
    s.style.top = cy + 'px';
    s.style.setProperty('--dx', (Math.random()*2-1)*60 + 'px');
    s.style.setProperty('--dy', (Math.random()*2-1)*60 + 'px');
    s.style.setProperty('--rt', (Math.random()*120-60) + 'deg');
    document.body.appendChild(s);
    setTimeout(() => s.remove(), 600);
  }
}

/* Style runtime cho mini-spark */
const _sparkStyle = document.createElement('style');
_sparkStyle.textContent = `
  .mini-spark{
    position:absolute;width:10px;height:10px;pointer-events:none;z-index:60;
    background: conic-gradient(#FFB3C7, #B3E5FC, #FFE082, #FFB3C7);
    border-radius:3px; transform:translate(-50%,-50%); opacity:.95;
    animation: burst 600ms ease-out forwards;
    box-shadow: 0 2px 6px rgba(0,0,0,.15);
  }
  @keyframes burst{
    0%{transform:translate(-50%,-50%) rotate(0) scale(.8); opacity:1}
    100%{transform:translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) rotate(var(--rt)) scale(.6); opacity:0}
  }
`;
document.head.appendChild(_sparkStyle);
