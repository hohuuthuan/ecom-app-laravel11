(function () {
  var navLinks = document.querySelectorAll('.profile-nav-link');
  var sections = document.querySelectorAll('.profile-section');

  if (!navLinks.length || !sections.length) {
    return;
  }

  function setActiveTab(target) {
    if (!target) return;

    navLinks.forEach(function (link) {
      var isActive = link.getAttribute('data-target') === target;
      link.classList.toggle('active', isActive);
    });

    sections.forEach(function (section) {
      var isActive = section.getAttribute('data-section') === target;
      section.classList.toggle('active', isActive);
    });
  }

  navLinks.forEach(function (link) {
    link.addEventListener('click', function (e) {
      var target = link.getAttribute('data-target');
      if (!target) return;

      e.preventDefault();

      setActiveTab(target);

      if (window.history && window.history.replaceState) {
        var url = new URL(window.location.href);
        url.searchParams.set('tab', target);
        window.history.replaceState(null, '', url.toString());
      }
    });
  });
})();
