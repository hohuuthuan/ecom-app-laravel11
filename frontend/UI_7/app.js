// Back-to-top show/hide
(function(){
  const btn = document.getElementById('backTop');
  const onScroll = () => {
    if(window.scrollY > 200){
      btn.classList.add('show');
    }else{
      btn.classList.remove('show');
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();

// Horizontal scroller controls
(function(){
  const nextBtns = document.querySelectorAll('.carousel-next');
  const prevBtns = document.querySelectorAll('.carousel-prev');

  const scrollByAmount = (el, dir = 1) => {
    const amount = Math.max(240, el.clientWidth * 0.8);
    el.scrollBy({ left: amount * dir, behavior: 'smooth' });
  };

  nextBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.querySelector(btn.dataset.target);
      if(target){ scrollByAmount(target, +1); }
    });
  });
  prevBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.querySelector(btn.dataset.target);
      if(target){ scrollByAmount(target, -1); }
    });
  });
})();
