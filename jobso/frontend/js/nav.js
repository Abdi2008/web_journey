// Small nav helper: toggle mobile menu, close on link click/Escape, highlight active link
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('navToggle');
  const nav = document.getElementById('mainNav');

  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const isOpen = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    // Close menu when a link is clicked (mobile)
    nav.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        if (nav.classList.contains('open')) {
          nav.classList.remove('open');
          toggle.setAttribute('aria-expanded', 'false');
        }
      });
    });

    // Highlight active link (simple pathname match)
    try {
      const currentPath = location.pathname.replace(/\/$/, '');
      nav.querySelectorAll('a').forEach(a => {
        try {
          const linkPath = new URL(a.href, location.origin).pathname.replace(/\/$/, '');
          if (linkPath === currentPath) a.classList.add('active');
        } catch (e) { /* ignore malformed hrefs */ }
      });
    } catch (e) { /* ignore */ }
  }

  // Close on Escape
  document.addEventListener('keydown', (ev) => {
    if (ev.key === 'Escape') {
      if (nav && nav.classList.contains('open')) {
        nav.classList.remove('open');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
      }
    }
  });
});
