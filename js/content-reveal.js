(function () {
  'use strict';

  // Respect user preference
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion) return;

  // Target selectors for auto-reveal
  const autoRevealSelectors = [
    '.hero .container',
    '.page-banner .container',
    'section .container > .text-center',
    '.filter-box',
    '.car-card',
    '.feature-card',
    '.service-card',
    '.testimonial-card',
    '.counter-item',
    '.cta-section .container',
    'form.row.g-3',
    '.p-4.bg-light.rounded'
  ];

  // Add reveal-item class to matched elements (skip if already has a reveal class)
  document.querySelectorAll(autoRevealSelectors.join(', ')).forEach(function (el) {
    if (el.classList.contains('reveal-item') ||
        el.classList.contains('reveal-left') ||
        el.classList.contains('reveal-right') ||
        el.classList.contains('reveal-scale')) return;
    el.classList.add('reveal-item');
  });

  // Stagger children in grid rows
  document.querySelectorAll('.row.g-4, .row.g-5, .row.g-3').forEach(function (row) {
    // Skip rows inside hero or that are purely structural
    if (row.closest('.hero') || row.closest('.stats-section') || row.closest('.cta-section')) return;

    var children = Array.from(row.children);
    children.forEach(function (child, index) {
      child.style.setProperty('--reveal-delay', (index * 100) + 'ms');
      if (!child.classList.contains('reveal-item') &&
          !child.classList.contains('reveal-left') &&
          !child.classList.contains('reveal-right') &&
          !child.classList.contains('reveal-scale')) {
        child.classList.add('reveal-item');
      }
    });
  });

  // Setup IntersectionObserver
  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
        
        // Remove animation classes after it finishes to restore default element CSS (fixes hover bugs)
        var delayStr = entry.target.style.getPropertyValue('--reveal-delay');
        var delay = parseInt(delayStr) || 0;
        setTimeout(function() {
          entry.target.classList.remove('reveal-item', 'reveal-left', 'reveal-right', 'reveal-scale', 'is-visible');
          entry.target.style.removeProperty('--reveal-delay');
        }, delay + 1000); // 800ms transition + graceful buffer
      });
    },
    {
      threshold: 0.12,
      rootMargin: '0px 0px -6% 0px'
    }
  );

  // Observe all reveal elements
  var allRevealElements = document.querySelectorAll(
    '.reveal-item, .reveal-left, .reveal-right, .reveal-scale'
  );
  allRevealElements.forEach(function (el) {
    observer.observe(el);
  });

  // Make hero content visible immediately with slight delay for dramatic effect
  var heroContent = document.querySelector('.hero .container');
  if (heroContent) {
    setTimeout(function () {
      heroContent.classList.add('is-visible');
    }, 200);
  }
})();
