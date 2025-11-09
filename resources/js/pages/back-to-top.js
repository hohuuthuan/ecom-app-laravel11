(() => {
  const btn = document.getElementById('backTop');
  const onScroll = () => {
    if (window.scrollY > 220) {
      btn.classList.add('show');
    } else {
      btn.classList.remove('show');
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
})();

// Horizontal scrollers controls
(() => {
  const nextBtns = document.querySelectorAll('.sc-next');
  const prevBtns = document.querySelectorAll('.sc-prev');

  const scrollByAmount = (el, dir = 1) => {
    const amount = Math.max(240, el.clientWidth * 0.8);
    el.scrollBy({ left: amount * dir, behavior: 'smooth' });
  };

  const bind = (btns, dir) => {
    btns.forEach(btn => {
      btn.addEventListener('click', () => {
        const targetSel = btn.getAttribute('data-target');
        const target = document.querySelector(targetSel);
        if (target) { scrollByAmount(target, dir); }
      });
    });
  };

  bind(nextBtns, +1);
  bind(prevBtns, -1);
})();

