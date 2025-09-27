/* Helpers */
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

const state = {
  category: 'all',
  query: '',
  cart: Number(localStorage.getItem('cartCount') || 0),
  theme: localStorage.getItem('theme') || 'dark'
};

/* Init */
document.addEventListener('DOMContentLoaded', () => {
  // Theme
  if(state.theme === 'light'){ document.documentElement.classList.add('light'); }
  updateCartUI();

  // Nav toggle
  const navBtn = $('#navToggle');
  const nav = $('#siteNav');
  navBtn.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    navBtn.setAttribute('aria-expanded', String(open));
  });

  // Category chips
  $$('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
      $$('.chip').forEach(c => c.classList.remove('is-active'));
      chip.classList.add('is-active');
      state.category = chip.dataset.category;
      filterBooks();
    });
  });

  // Search
  $('#searchForm').addEventListener('submit', e => {
    e.preventDefault();
    state.query = ($('#searchInput').value || '').trim().toLowerCase();
    filterBooks();
  });

  // Add to cart
  $$('.add-to-cart').forEach(btn => btn.addEventListener('click', () => addToCart(btn.dataset.id)));

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
    document.documentElement.classList.toggle('light');
    state.theme = document.documentElement.classList.contains('light') ? 'light' : 'dark';
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
function addToCart(id){
  // giả lập thêm vào giỏ
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

/* Optional: light theme vars */
const lightVars = `
  :root.light{
    --bg:#f7f8fb; --elev:#ffffff; --card:#ffffff; --text:#10121a; --muted:#596079;
    --line:#e6e8f0; --shadow:0 10px 30px rgba(16,18,26,.08);
  }
  :root.light .logo svg{fill:#10121a}
  :root.light .logo .logo-page{fill:#10121a}
`;
const style = document.createElement('style');
style.textContent = lightVars;
document.head.appendChild(style);
