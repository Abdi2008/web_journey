// Small module to trigger homepage animations with gentle stagger
window.addEventListener('DOMContentLoaded', () => {
  try {
    // Hero text and CTA
    const heroLeft = document.querySelector('.hero .left');
    const heroRightImg = document.querySelector('.hero .inner .right img');
    if (heroLeft) {
      heroLeft.classList.add('anim-hidden');
      // force reflow then add class
      requestAnimationFrame(() => {
        heroLeft.classList.remove('anim-hidden');
        heroLeft.classList.add('anim-fade-up');
      });
    }
    if (heroRightImg) {
      heroRightImg.classList.add('anim-hidden');
      requestAnimationFrame(() => {
        heroRightImg.classList.remove('anim-hidden');
        heroRightImg.classList.add('anim-fade-up');
        // give it a slow float after the initial reveal
        setTimeout(() => heroRightImg.classList.add('anim-float'), 800);
      });
    }

    // Stagger companies grid
    const comps = document.querySelector('.companies');
    if (comps) {
      comps.classList.add('stagger');
      Array.from(comps.children).forEach((el, idx) => {
        el.style.setProperty('--stagger-index', idx);
      });
    }

    // Stagger features and steps
    const features = document.querySelector('.features');
    if (features) {
      features.classList.add('stagger');
      Array.from(features.children).forEach((el, idx) => el.style.setProperty('--stagger-index', idx));
    }
    const steps = document.querySelector('.how-steps');
    if (steps) {
      steps.classList.add('stagger');
      Array.from(steps.children).forEach((el, idx) => el.style.setProperty('--stagger-index', idx));
    }

    // Reveal latest job cards when loadJobs finishes; if jobs present, stagger them
    const jobsGrid = document.getElementById('jobs');
    if (jobsGrid) {
      // Observe child additions and then stagger reveal
      const obs = new MutationObserver((mutations, observer) => {
        // only act when children > 0
        if (jobsGrid.children.length > 0) {
          Array.from(jobsGrid.children).forEach((el, idx) => {
            el.style.opacity = 0;
            el.style.transform = 'translateY(8px)';
            el.style.animation = `fadeUp 520ms cubic-bezier(.2,.9,.2,1) forwards`;
            el.style.animationDelay = `${idx * 70}ms`;
          });
          observer.disconnect();
        }
      });
      obs.observe(jobsGrid, { childList: true });
    }
  } catch (e) {
    console.error('Animations init error', e);
  }
});
