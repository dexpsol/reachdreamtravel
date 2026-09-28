(() => {
  'use strict';

  const whatsappNumber = '919876543210'; // Replace with the business WhatsApp number.
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const siteHeader = document.querySelector('.site-header');
  const menuToggle = document.querySelector('.menu-toggle');
  const hero = document.querySelector('.hero');
  const heroImage = document.querySelector('.hero-image');
  const scrollProgress = document.querySelector('.scroll-progress');

  // Compact header after the hero begins to scroll away.
  const updateHeader = () => siteHeader.classList.toggle('scrolled', window.scrollY > 24);
  updateHeader();
  window.addEventListener('scroll', updateHeader, { passive: true });

  // Thin page progress indicator for a little extra polish while scrolling.
  const updateProgress = () => {
    if (!scrollProgress) return;
    const scrollable = document.documentElement.scrollHeight - window.innerHeight;
    scrollProgress.style.transform = `scaleX(${scrollable > 0 ? window.scrollY / scrollable : 0})`;
  };
  updateProgress();
  window.addEventListener('scroll', updateProgress, { passive: true });
  window.addEventListener('resize', updateProgress, { passive: true });

  // Bootstrap offcanvas supplies the left-to-right mobile navigation motion.
  const menuPanel = document.getElementById('siteMenu');
  if (menuPanel && window.bootstrap) {
    menuPanel.addEventListener('show.bs.offcanvas', () => {
      menuToggle?.setAttribute('aria-expanded', 'true');
      menuToggle?.setAttribute('aria-label', 'Close navigation');
    });
    menuPanel.addEventListener('hidden.bs.offcanvas', () => {
      menuToggle?.setAttribute('aria-expanded', 'false');
      menuToggle?.setAttribute('aria-label', 'Open navigation');
    });
    menuPanel.querySelectorAll('.nav-link').forEach(link => link.addEventListener('click', () => {
      if (window.innerWidth < 992) window.bootstrap.Offcanvas.getOrCreateInstance(menuPanel).hide();
    }));
  }

  // Reveal content once as it enters view, with a small stagger for repeated items.
  document.documentElement.classList.add('motion-ready');
  const revealTargets = document.querySelectorAll('.trust-item,.counter-intro,.counter-item,.section-heading,.package-card,.vehicle-card,.about-visual,.about-copy,.stay-photo,.stay-copy,.gallery-strip button,.traveler-note,.footer-main > div');
  revealTargets.forEach((element, index) => {
    element.classList.add('reveal');
    const stagger = index % 4;
    if (stagger) element.classList.add(`stagger-${stagger}`);
  });
  if ('IntersectionObserver' in window && !reducedMotion) {
    const revealObserver = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        revealObserver.unobserve(entry.target);
      }
    }), { threshold: 0.12 });
    revealTargets.forEach(element => revealObserver.observe(element));
  } else revealTargets.forEach(element => element.classList.add('visible'));

  // Subtle pointer tilt adds depth to cards on desktop without affecting touch layouts.
  if (!reducedMotion && window.matchMedia('(pointer:fine)').matches) {
    document.querySelectorAll('.package-card, .vehicle-card, .stay-panel').forEach(card => {
      let tiltFrame = 0;
      card.addEventListener('pointermove', event => {
        if (tiltFrame) return;
        tiltFrame = requestAnimationFrame(() => {
          const bounds = card.getBoundingClientRect();
          const x = (event.clientX - bounds.left) / bounds.width - 0.5;
          const y = (event.clientY - bounds.top) / bounds.height - 0.5;
          card.style.setProperty('--tilt-x', `${y * -5}deg`);
          card.style.setProperty('--tilt-y', `${x * 6}deg`);
          tiltFrame = 0;
        });
      });
      card.addEventListener('pointerleave', () => {
        card.style.setProperty('--tilt-x', '0deg');
        card.style.setProperty('--tilt-y', '0deg');
      });
    });
  }

  // Count-up stats run only the first time the fixed-background band is seen.
  const countNodes = document.querySelectorAll('[data-counter]');
  const countUp = node => {
    const end = Number(node.dataset.counter);
    if (reducedMotion) { node.textContent = String(end); return; }
    const start = performance.now();
    const duration = 1100;
    const step = now => {
      const progress = Math.min((now - start) / duration, 1);
      node.textContent = String(Math.round(end * (1 - Math.pow(1 - progress, 3))));
      if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };
  if ('IntersectionObserver' in window) {
    const counterObserver = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) { countUp(entry.target); counterObserver.unobserve(entry.target); }
    }), { threshold: 0.45 });
    countNodes.forEach(node => counterObserver.observe(node));
  } else countNodes.forEach(countUp);

  // Gentle hero depth follows pointer position on mouse devices only.
  if (hero && heroImage && !reducedMotion && window.matchMedia('(pointer:fine)').matches) {
    let framePending = false;
    hero.addEventListener('pointermove', event => {
      if (framePending) return;
      framePending = true;
      window.requestAnimationFrame(() => {
        const bounds = hero.getBoundingClientRect();
        const x = (event.clientX - bounds.left) / bounds.width - 0.5;
        const y = (event.clientY - bounds.top) / bounds.height - 0.5;
        heroImage.style.transform = `scale(1.1) translate3d(${x * -8}px,${y * -6}px,0)`;
        framePending = false;
      });
    });
    hero.addEventListener('pointerleave', () => { heroImage.style.transform = ''; });
  }

  const itineraries = {
    spiti: {
      title: 'Shimla to Spiti via Kinnaur', image: 'assets/images/spiti.jpg', duration: '5 days · 4 nights · Flexible private trip',
      days: ['Day 1 · Shimla to Sarahan — mountain roads and temple town.', 'Day 2 · Sarahan to Kalpa — orchard country and Kinner Kailash views.', 'Day 3 · Kalpa to Tabo via Nako — high villages and quiet landscapes.', 'Day 4 · Tabo to Kaza — monastery visit and time to explore.', 'Day 5 · Kaza onward — extend to Chandratal and Manali when route and season allow.'],
      details: 'Transport, sightseeing and hotel stay options can be planned together. Meals, entry fees and activities can be discussed while planning. Vehicle and stay category depend on your group, route and season.'
    },
    manali: {
      title: 'Shimla · Kullu · Manali', image: 'assets/images/manali.jpg', duration: 'Flexible duration · Private trip',
      days: ['Day 1 · Arrive in Shimla and enjoy an easy local introduction.', 'Day 2 · Travel through Kullu, with time for riverside stops and Manikaran or Kasol.', 'Day 3 · Discover Manali, Hidimba Temple and the old town.', 'Day 4 · Explore Solang Valley and the Atal Tunnel to Sissu, subject to road and weather conditions.'],
      details: 'Stay options, transport and activities can be arranged around your dates. Route and duration can be customized.'
    },
    shimla: {
      title: 'Shimla local tour', image: 'assets/images/shimla.jpg', duration: '3 days · 2 nights · Flexible private trip',
      days: ['Day 1 · Jakhu Temple, Sankat Mochan, Viceregal Lodge and Mall Road.', 'Day 2 · Green Valley, Kufri and Indira Tourist Park; add Narkanda if you have time.', 'Day 3 · A relaxed morning, then return to Shimla or continue to Chandigarh.'],
      details: 'Transport and stay options can be planned for your group. Entry tickets and meals are not included unless agreed.'
    }
  };

  const packageDialog = document.getElementById('packageModal');
  document.querySelectorAll('.itinerary').forEach(button => button.addEventListener('click', () => {
    const trip = itineraries[button.dataset.package];
    if (!trip || !packageDialog) return;
    const image = document.getElementById('modalImage');
    image.src = trip.image;
    image.alt = `${trip.title} in Himachal Pradesh`;
    document.getElementById('modalTitle').textContent = trip.title;
    document.getElementById('modalContent').innerHTML = `<p><strong>${trip.duration}</strong></p><h3>Suggested itinerary</h3><ul>${trip.days.map(day => `<li>${day}</li>`).join('')}</ul><p>${trip.details}</p>`;
    packageDialog.querySelector('.whatsapp-link').href = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(`Hello Reach Dream Travel, please share details for ${trip.title}.` )}`;
    packageDialog.showModal();
    document.body.classList.add('no-scroll');
  }));
  packageDialog?.querySelector('[data-close]')?.addEventListener('click', () => packageDialog.close());
  packageDialog?.addEventListener('click', event => { if (event.target === packageDialog) packageDialog.close(); });
  packageDialog?.addEventListener('close', () => document.body.classList.remove('no-scroll'));

  // Fullscreen gallery with arrow-key and button navigation.
  const galleryButtons = [...document.querySelectorAll('.gallery-strip button')];
  const galleryStrip = document.querySelector('.gallery-strip');
  const lightbox = document.getElementById('lightbox');
  let galleryIndex = 0;
  const showImage = index => {
    if (!galleryButtons.length || !lightbox) return;
    galleryIndex = (index + galleryButtons.length) % galleryButtons.length;
    const image = lightbox.querySelector('img');
    image.src = galleryButtons[galleryIndex].dataset.full;
    image.alt = galleryButtons[galleryIndex].querySelector('img').alt;
    lightbox.querySelector('.lightbox-count').textContent = `${galleryIndex + 1} / ${galleryButtons.length}`;
  };
  galleryButtons.forEach((button, index) => button.addEventListener('click', () => {
    showImage(index);
    lightbox.showModal();
    document.body.classList.add('no-scroll');
  }));
  document.getElementById('galleryPrev')?.addEventListener('click', () => galleryStrip.scrollBy({ left: -260, behavior: reducedMotion ? 'auto' : 'smooth' }));
  document.getElementById('galleryNext')?.addEventListener('click', () => galleryStrip.scrollBy({ left: 260, behavior: reducedMotion ? 'auto' : 'smooth' }));
  lightbox?.querySelector('.lightbox-close')?.addEventListener('click', () => lightbox.close());
  lightbox?.querySelector('.lightbox-prev')?.addEventListener('click', () => showImage(galleryIndex - 1));
  lightbox?.querySelector('.lightbox-next')?.addEventListener('click', () => showImage(galleryIndex + 1));
  lightbox?.addEventListener('click', event => { if (event.target === lightbox) lightbox.close(); });
  lightbox?.addEventListener('close', () => document.body.classList.remove('no-scroll'));
  lightbox?.addEventListener('keydown', event => {
    if (event.key === 'ArrowLeft') showImage(galleryIndex - 1);
    if (event.key === 'ArrowRight') showImage(galleryIndex + 1);
  });

  // Keep every WhatsApp action pointed at the configurable business number.
  document.querySelectorAll('a[href*="wa.me"]').forEach(link => {
    if (!link.href.includes('?text=')) link.href = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent('Hello Reach Dream Travel, I would like to plan a Himachal trip.')}`;
  });
})();
