(function () {
  const navbar = document.getElementById('siteNavbar');
  if (!navbar) return;

  // Use hysteresis to prevent flicker around a single threshold.
  const SHRINK_AT = 96;
  const EXPAND_AT = 40;
  let isShrunk = navbar.classList.contains('navbar-shrink');
  let ticking = false;

  const updateNavbar = () => {
    const y = window.scrollY || window.pageYOffset || 0;

    if (!isShrunk && y > SHRINK_AT) {
      navbar.classList.add('navbar-shrink');
      isShrunk = true;
    } else if (isShrunk && y < EXPAND_AT) {
      navbar.classList.remove('navbar-shrink');
      isShrunk = false;
    }
  };

  const onScroll = () => {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(() => {
      updateNavbar();
      ticking = false;
    });
  };

  updateNavbar();
  window.addEventListener('scroll', onScroll, { passive: true });
})();
