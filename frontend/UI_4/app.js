// Back-to-top
const backTop = document.getElementById('backTop');

const onScroll = () => {
  if(window.scrollY > 200){
    backTop.classList.add('show');
  }else{
    backTop.classList.remove('show');
  }
};
window.addEventListener('scroll', onScroll, { passive: true });

backTop.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Horizontal scrollers
const attachScroller = (btn) => {
  const targetSel = btn.getAttribute('data-target');
  const scroller = document.querySelector(targetSel);
  if(!scroller){ return; }

  btn.addEventListener('click', () => {
    const dir = btn.classList.contains('left') ? -1 : 1;
    const delta = scroller.clientWidth * 0.9 * dir;
    scroller.scrollBy({ left: delta, behavior: 'smooth' });
  });
};

document.querySelectorAll('.hsnap-btn').forEach(attachScroller);

// Optional: prevent edge-stick on mobile grids (already handled by container padding)

// Init
onScroll();
