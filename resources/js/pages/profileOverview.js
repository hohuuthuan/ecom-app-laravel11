(function () {
  var navLinks = document.querySelectorAll('.profile-nav-link');
  var sections = document.querySelectorAll('.profile-section');

  navLinks.forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      var target = link.getAttribute('data-target');

      navLinks.forEach(function (item) {
        item.classList.remove('active');
      });
      sections.forEach(function (section) {
        section.classList.remove('active');
      });

      link.classList.add('active');
      var section = document.querySelector('.profile-section[data-section="' + target + '"]');
      if (section) {
        section.classList.add('active');
      }
    });
  });
})();