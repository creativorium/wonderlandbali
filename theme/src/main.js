/**
 * Theme front-end entry. Site-wide styles + light interactivity.
 * Built by Vite -> theme/build/main.js (+ main.css).
 */
import './styles/main.scss';

// Header: switch transparent -> solid once scrolled past the top (front page).
const header = document.getElementById('site-header');
if (header && header.classList.contains('site-header--transparent')) {
  const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 40);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
}

// Full-screen menu overlay toggle.
const toggle = document.getElementById('menu-toggle');
const overlay = document.getElementById('menu-overlay');
const closeBtn = document.getElementById('menu-close');

if (toggle && overlay) {
  const openMenu = () => {
    overlay.hidden = false;
    // next frame so the transition runs
    requestAnimationFrame(() => overlay.classList.add('is-open'));
    toggle.setAttribute('aria-expanded', 'true');
    document.body.classList.add('menu-open');
  };
  const closeMenu = () => {
    overlay.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
    setTimeout(() => { overlay.hidden = true; }, 350);
  };

  toggle.addEventListener('click', openMenu);
  closeBtn?.addEventListener('click', closeMenu);
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeMenu();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeMenu();
  });
  // Close when a menu link is followed.
  overlay.querySelectorAll('a').forEach((a) => a.addEventListener('click', closeMenu));
}
