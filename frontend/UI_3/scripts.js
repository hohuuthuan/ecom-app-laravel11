/* ===== Hanami Books JS — layout mới ===== */
const $ = (s, c=document) => c.querySelector(s);
const $$ = (s, c=document) => [...c.querySelectorAll(s)];

const state = {
  q: '',
  cat: 'all',
  price: null,
  cart: [],
};

document.addEventListener('DOMContentLoaded', () => {
  // Năm footer
  $('#y').textContent = String(new Date().getFullYear());

  // Tìm kiếm
  $('#formSearch').addEventListener('submit', e => {
    e.preventDefault();
    state.q = ($('#q').value || '').trim().toLowerCase();
    render();
  });

  // Danh mục
  $$('#catNav .hb-cat').forEach(btn => btn.addEventListener('click', () => {
    $$('#catNav .hb-cat').forEach(b => b.classList.remove('is-active'));
    btn.classList.add('is-active');
    state.cat = btn.dataset.cat;
    $('#gridTitle').textContent = btn.textContent;
    render();
  }));

  // Bộ lọc giá
  $$('.hb-tag').forEach(tag => {
    tag.addEventListener('click', () => {
      if(tag.id === 'clearFilter'){ state.price = null; }
      else { state.price = tag.dataset.price; }
      render();
    });
  });

  // Thêm vào giỏ
  $$('.hb-card .add').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.hb-card');
      const item = {
        id: card.dataset.id,
        name: card.dataset.title,
        price: Number(card.dataset.price || 0)
      };
      state.cart.push(item);
      updateCartUI();
      toast('Đã thêm vào giỏ');
    });
  });

  // Drawer giỏ hàng
  $('#btnCart').addEventListener('click', openDrawer);
  $('#closeDrawer').addEventListener('click', closeDrawer);
  $('#checkout').addEventListener('click', () => toast('Đang phát triển'));

  render();
});

/* Lọc + hiển thị */
function render(){
  const cards = $$('#grid .hb-card');
  let visible = 0;

  for(const card of cards){
    const t = (card.dataset.title || '').toLowerCase();
    const a = (card.dataset.author || '').toLowerCase();
    const cat = card.dataset.cat || 'all';
    const price = Number(card.dataset.price || 0);

    const matchQ = !state.q || t.includes(state.q) || a.includes(state.q);
    const matchCat = state.cat === 'all' || state.cat === cat;
    const matchPrice = checkPrice(price, state.price);

    const ok = matchQ && matchCat && matchPrice;
    card.style.display = ok ? '' : 'none';
    if(ok){ visible++; }
  }

  if(visible === 0){ toast('Không tìm thấy sách phù hợp'); }
}

function checkPrice(p, rule){
  if(!rule){ return true; }
  if(rule === 'lt100'){ return p < 100000; }
  if(rule === '100-200'){ return p >= 100000 && p <= 200000; }
  if(rule === 'gt200'){ return p > 200000; }
  return true;
}

/* Giỏ hàng */
function updateCartUI(){
  $('#cartN').textContent = String(state.cart.length);

  const ul = $('#cartList');
  ul.innerHTML = '';
  let sum = 0;

  for(const it of state.cart){
    sum += it.price;
    const li = document.createElement('li');
    li.innerHTML = `
      <span>${escapeHtml(it.name)}</span>
      <span>${formatVND(it.price)}</span>
    `;
    ul.appendChild(li);
  }

  $('#cartSum').textContent = formatVND(sum);
  $('#checkout').disabled = state.cart.length === 0;
}

function openDrawer(){
  $('#drawer').classList.add('open');
  $('#drawer').setAttribute('aria-hidden','false');
}
function closeDrawer(){
  $('#drawer').classList.remove('open');
  $('#drawer').setAttribute('aria-hidden','true');
}

/* Subscribe */
$('#sub')?.addEventListener('submit', e => {
  e.preventDefault();
  const email = $('#email').value.trim();
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ toast('Email không hợp lệ'); return; }
  toast('Đã gửi mã giảm 20% tới email');
  $('#email').value = '';
});

/* Toast */
let _t = null;
function toast(msg){
  const el = $('#toast');
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(_t);
  _t = setTimeout(() => el.classList.remove('show'), 1800);
}

/* Utils */
function formatVND(n){ return new Intl.NumberFormat('vi-VN',{style:'currency',currency:'VND',maximumFractionDigits:0}).format(n); }
function escapeHtml(s){ return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
