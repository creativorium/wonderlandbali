/**
 * Theme front-end entry. Site-wide styles + light interactivity.
 * Built by Vite -> theme/build/main.js (+ main.css).
 */
import './styles/main.scss';

// Example progressive enhancement: mark header as "scrolled" for sticky styling.
const header = document.getElementById('site-header');
if (header) {
  const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
}
