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

// Off-canvas menu panel.
const toggle = document.getElementById('menu-toggle');
const overlay = document.getElementById('menu-overlay');
const closeBtn = document.getElementById('menu-close');

if (toggle && overlay) {
  const openMenu = () => {
    overlay.hidden = false;
    requestAnimationFrame(() => overlay.classList.add('is-open'));
    toggle.setAttribute('aria-expanded', 'true');
    document.body.classList.add('menu-open');
  };
  const closeMenu = () => {
    overlay.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
    setTimeout(() => { overlay.hidden = true; }, 420);
  };

  toggle.addEventListener('click', openMenu);
  closeBtn?.addEventListener('click', closeMenu);
  overlay.querySelectorAll('[data-menu-close]').forEach((el) => el.addEventListener('click', closeMenu));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeMenu();
  });

  // Sub-menu accordions (Region / Services): inject a toggle arrow per parent.
  const parents = overlay.querySelectorAll('.overlay-nav > .menu-item-has-children');
  parents.forEach((li) => {
    const link = li.querySelector(':scope > a');
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'overlay-nav__toggle';
    btn.setAttribute('aria-label', 'Toggle submenu');
    const toggleSub = (e) => {
      e.preventDefault();
      li.classList.toggle('is-open');
    };
    btn.addEventListener('click', toggleSub);
    link.after(btn);
    // Region / Services usually link to "#": make the label toggle too.
    const href = link.getAttribute('href') || '';
    if (href === '#' || href === '') link.addEventListener('click', toggleSub);
  });

  // Any real navigation link closes the panel.
  overlay.querySelectorAll('a').forEach((a) => {
    const li = a.parentElement;
    const isParentLabel =
      li &&
      li.classList.contains('menu-item-has-children') &&
      li.parentElement.classList.contains('overlay-nav');
    const href = a.getAttribute('href') || '';
    if (isParentLabel && (href === '#' || href === '')) return; // toggles instead
    a.addEventListener('click', closeMenu);
  });
}

/* ---------------------------------------------------------------------------
   Conversion events -> dataLayer.

   GTM triggers hang off these. Deliberately tiny: our forms redirect back with
   ?wl_sent=1, so a submission is just a page load carrying a flag (PHP sets
   window.wlFormSubmitted). The old Elementor tracker had to patch window.fetch
   and XMLHttpRequest and watch the DOM for a success node to do the same job.
--------------------------------------------------------------------------- */
(function () {
  const push = (event, data) => {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(Object.assign({ event }, data || {}));
  };

  // A form was submitted successfully on the page we just landed on.
  if (window.wlFormSubmitted) {
    push('form_submit', {
      form_name: window.wlFormSubmitted, // 'contact' | 'request'
      page_url: location.href,
      page_title: document.title,
    });
  }

  // Outbound taps worth counting as conversions. Delegated, so it covers the
  // floating button, footer links and anything added later.
  document.addEventListener(
    'click',
    (e) => {
      const a = e.target.closest && e.target.closest('a[href]');
      if (!a) return;
      const href = a.getAttribute('href') || '';

      if (href.indexOf('wa.me') !== -1 || href.indexOf('whatsapp.com') !== -1) {
        push('whatsapp_click', { link_url: href, page_url: location.href });
      } else if (href.indexOf('tel:') === 0) {
        push('phone_click', { link_url: href });
      } else if (href.indexOf('mailto:') === 0) {
        push('email_click', { link_url: href });
      } else if (/\.pdf($|\?)/i.test(href)) {
        push('brochure_download', { link_url: href });
      }
    },
    true
  );
})();
