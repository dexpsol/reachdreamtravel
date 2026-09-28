(() => {
  'use strict';

  const whatsappNumber = '919888351723'; // Keep in sync with $site['whatsapp'] in includes/data.php.
  const waLink = text => `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(text)}`;
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const siteHeader = document.querySelector('.site-header');
  const menuToggle = document.querySelector('.menu-toggle');
  const scrollProgress = document.querySelector('.scroll-progress');
  const backToTop = document.querySelector('.back-to-top');

  // Solid header and page progress bar while scrolling.
  const onScroll = () => {
    siteHeader.classList.toggle('scrolled', window.scrollY > 24);
    const scrollable = document.documentElement.scrollHeight - window.innerHeight;
    const progress = scrollable > 0 ? window.scrollY / scrollable : 0;
    if (scrollProgress) scrollProgress.style.transform = `scaleX(${progress})`;
    if (backToTop) {
      backToTop.classList.toggle('show', window.scrollY > 600);
      backToTop.style.setProperty('--progress', progress.toFixed(3));
    }
  };

  // Back-to-top button returns to the top smoothly without adding #top to the URL.
  backToTop?.addEventListener('click', event => {
    event.preventDefault();
    window.scrollTo({ top: 0, behavior: reducedMotion ? 'auto' : 'smooth' });
  });
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });

  // Bootstrap offcanvas supplies the mobile navigation panel.
  const menuPanel = document.getElementById('siteMenu');
  if (menuPanel) {
    menuPanel.addEventListener('show.bs.offcanvas', () => {
      menuToggle?.setAttribute('aria-expanded', 'true');
      menuToggle?.setAttribute('aria-label', 'Close navigation');
    });
    menuPanel.addEventListener('hidden.bs.offcanvas', () => {
      menuToggle?.setAttribute('aria-expanded', 'false');
      menuToggle?.setAttribute('aria-label', 'Open navigation');
    });
    menuPanel.querySelectorAll('.nav-link').forEach(link => link.addEventListener('click', () => {
      if (window.innerWidth < 1200 && window.bootstrap) window.bootstrap.Offcanvas.getOrCreateInstance(menuPanel).hide();
    }));
  }

  // On the homepage, highlight the nav link for the section in view (inner pages are marked by PHP).
  const navLinks = [...document.querySelectorAll('.site-menu .nav-link[href^="#"]')];
  const sections = navLinks.map(link => document.querySelector(link.getAttribute('href'))).filter(Boolean);
  if ('IntersectionObserver' in window) {
    const navObserver = new IntersectionObserver(entries => entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      navLinks.forEach(link => link.classList.toggle('active', link.getAttribute('href') === `#${entry.target.id}`));
    }), { rootMargin: '-45% 0px -50% 0px' });
    sections.forEach(section => navObserver.observe(section));
  }

  // Reveal content once as it enters view, with a small stagger for repeated items.
  document.documentElement.classList.add('motion-ready');
  const revealTargets = document.querySelectorAll('.feature,.drive-card,.section-heading,.package-card,.vehicle-card,.journey-band .col-lg-5,.step,.about-visual,.about-copy,.stay-panel,.gallery-grid button,.cta-card,.footer-main > *,.fc-card,.footer-cta,.value-card,.dest-card,.why-panel,.vehicle-detail,.table-wrap,.faq-item,.contact-card,.contact-form-wrap,.map-card,.masonry-item,.route-card,.include-item,.stay-cat,.type-card,.dest-row,.timeline-item,.pd-card,.legal-body section,.showroom');
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
        // Drop reveal styles afterwards so hover transitions are not delayed by the stagger.
        setTimeout(() => entry.target.classList.remove('reveal', 'visible', 'stagger-1', 'stagger-2', 'stagger-3'), 1200);
      }
    }), { threshold: 0.12 });
    revealTargets.forEach(element => revealObserver.observe(element));
  } else revealTargets.forEach(element => element.classList.add('visible'));

  // Count-up stats run the first time they are seen.
  const countNodes = document.querySelectorAll('[data-counter]');
  const countUp = node => {
    const end = Number(node.dataset.counter);
    if (reducedMotion) { node.textContent = String(end); return; }
    const start = performance.now();
    const duration = 1200;
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

  // ---------- 3D & premium effects ----------
  const finePointer = window.matchMedia('(pointer: fine)').matches;
  const rich = finePointer && !reducedMotion;

  // Headings flip up word by word in 3D when they scroll into view.
  const splitHeadings = document.querySelectorAll('.page-hero h1,.dr-body h2,.section-heading h2,.about-copy h2,.stay-copy h2,.journey-band h2,.cta-copy h2,.why-copy h2,.faq-title,.contact-title');
  if (!reducedMotion && 'IntersectionObserver' in window) {
    splitHeadings.forEach(heading => {
      let index = 0;
      heading.setAttribute('aria-label', heading.textContent.trim());
      [...heading.childNodes].forEach(node => {
        if (node.nodeType !== Node.TEXT_NODE) return;
        const fragment = document.createDocumentFragment();
        node.textContent.split(/(\s+)/).forEach(part => {
          if (!part) return;
          if (/^\s+$/.test(part)) { fragment.append(' '); return; }
          const word = document.createElement('span');
          word.className = 'w';
          word.setAttribute('aria-hidden', 'true');
          word.innerHTML = '<span></span>';
          word.firstChild.textContent = part;
          word.firstChild.style.setProperty('--i', index++);
          fragment.append(word);
        });
        node.replaceWith(fragment);
      });
      heading.classList.add('split-3d');
    });
    const headingObserver = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) { entry.target.classList.add('in'); headingObserver.unobserve(entry.target); }
    }), { threshold: 0.4 });
    splitHeadings.forEach(heading => headingObserver.observe(heading));
  }

  // Cards tilt toward the pointer, with a glare that follows it.
  if (rich) {
    document.querySelectorAll('.package-card,.vehicle-card,.gallery-grid button,.step,.stay-types>div,.value-card,.dest-card,.contact-card,.masonry-item,.route-card,.stay-cat,.type-card').forEach(card => {
      const strength = card.matches('.step') ? 4 : card.matches('.stay-types>div,.contact-card') ? 12 : card.matches('.stay-cat,.route-card') ? 6 : 9;
      card.classList.add('tilt');
      const glare = document.createElement('span');
      glare.className = 'glare';
      card.append(glare);
      let frame = 0;
      card.addEventListener('pointermove', event => {
        if (frame) return;
        frame = requestAnimationFrame(() => {
          frame = 0;
          const bounds = card.getBoundingClientRect();
          const x = (event.clientX - bounds.left) / bounds.width;
          const y = (event.clientY - bounds.top) / bounds.height;
          card.classList.add('is-tilting');
          card.style.setProperty('--ry', `${(x - 0.5) * strength}deg`);
          card.style.setProperty('--rx', `${(0.5 - y) * strength}deg`);
          card.style.setProperty('--mx', (x - 0.5).toFixed(3));
          card.style.setProperty('--my', (y - 0.5).toFixed(3));
          card.style.setProperty('--gx', `${x * 100}%`);
          card.style.setProperty('--gy', `${y * 100}%`);
        });
      });
      card.addEventListener('pointerleave', () => {
        card.classList.remove('is-tilting');
        ['--rx', '--ry', '--mx', '--my'].forEach(prop => card.style.removeProperty(prop));
      });
    });

    // Hero layers move at different depths with the pointer.
    document.querySelectorAll('.hero,.page-hero').forEach(hero => {
      hero.addEventListener('pointermove', event => {
        const bounds = hero.getBoundingClientRect();
        hero.style.setProperty('--hx', ((event.clientX - bounds.left) / bounds.width - 0.5).toFixed(3));
        hero.style.setProperty('--hy', ((event.clientY - bounds.top) / bounds.height - 0.5).toFixed(3));
      });
      hero.addEventListener('pointerleave', () => { hero.style.setProperty('--hx', 0); hero.style.setProperty('--hy', 0); });
    });

    // Buttons lean toward the cursor.
    document.querySelectorAll('.btn-gold,.btn-glass,.btn-dark,.btn-outline,.vehicle-cta,.whatsapp-float').forEach(button => {
      button.classList.add('magnetic');
      button.addEventListener('pointermove', event => {
        const bounds = button.getBoundingClientRect();
        button.style.setProperty('--bx', `${(event.clientX - bounds.left - bounds.width / 2) * 0.22}px`);
        button.style.setProperty('--by', `${(event.clientY - bounds.top - bounds.height / 2) * 0.3}px`);
      });
      button.addEventListener('pointerleave', () => { button.style.removeProperty('--bx'); button.style.removeProperty('--by'); });
    });

    // Soft spotlight follows the pointer across the dark panels.
    document.querySelectorAll('.journey-band,.cta-card,.footer-cta').forEach(panel => panel.addEventListener('pointermove', event => {
      const bounds = panel.getBoundingClientRect();
      panel.style.setProperty('--sx', `${event.clientX - bounds.left}px`);
      panel.style.setProperty('--sy', `${event.clientY - bounds.top}px`);
    }));
  }

  // Scroll-linked depth: hero recedes, background words drift, collage layers separate.
  if (!reducedMotion) {
    const heroSection = document.querySelector('.hero,.page-hero');
    const depthNodes = [...document.querySelectorAll('[data-depth]')];
    const driftNodes = [...document.querySelectorAll('[data-drift]')];
    let ticking = false;
    const updateDepth = () => {
      ticking = false;
      const viewport = window.innerHeight;
      if (heroSection) heroSection.style.setProperty('--hs', Math.min(window.scrollY / heroSection.offsetHeight, 1).toFixed(3));
      depthNodes.forEach(node => {
        const bounds = node.getBoundingClientRect();
        const offset = bounds.top + bounds.height / 2 - viewport / 2;
        node.style.setProperty('--depth-y', `${(offset * Number(node.dataset.depth)).toFixed(1)}px`);
      });
      driftNodes.forEach(node => {
        const bounds = node.parentElement.getBoundingClientRect();
        node.style.setProperty('--drift', `${((viewport / 2 - bounds.top - bounds.height / 2) * Number(node.dataset.drift)).toFixed(1)}px`);
      });
    };
    updateDepth();
    window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(updateDepth); } }, { passive: true });
    window.addEventListener('resize', updateDepth, { passive: true });
  }

  // Filter chip bars (vehicles, gallery). Each bar names the items it filters in data-target.
  document.querySelectorAll('.filter-bar').forEach(bar => {
    const items = [...document.querySelectorAll(bar.dataset.target)];
    const counter = document.querySelector('[data-count]');
    bar.querySelectorAll('button').forEach(button => button.addEventListener('click', () => {
      const filter = button.dataset.filter;
      bar.querySelectorAll('button').forEach(other => {
        other.classList.toggle('active', other === button);
        other.setAttribute('aria-pressed', String(other === button));
      });
      let shown = 0;
      items.forEach(item => {
        const show = filter === 'all' || (item.dataset.type || '').split(' ').includes(filter);
        item.classList.toggle('is-hidden', !show);
        item.classList.remove('is-entering');
        if (show) {
          shown++;
          if (!reducedMotion) { void item.offsetWidth; item.classList.add('is-entering'); }
        }
      });
      if (counter && bar.closest('.gallery-toolbar')) counter.textContent = String(shown);
    }));
  });

  // Each vehicle card opens WhatsApp with the vehicle already named.
  document.querySelectorAll('[data-vehicle]').forEach(link => {
    link.href = waLink(`Hello Reach Dream Travel, I would like to book a ${link.dataset.vehicle} for my Himachal trip.`);
  });

  // Trip enquiry forms: the details are emailed to us (send-enquiry.php) and a
  // WhatsApp chat opens with the same details ready to send.
  const labels = { name: 'Name', phone: 'Phone', destination: 'Trip', date: 'Travel date', travellers: 'Travellers', vehicle: 'Vehicle', message: 'Notes' };
  document.querySelectorAll('.trip-form').forEach(form => form.addEventListener('submit', event => {
    event.preventDefault();
    const status = form.querySelector('.form-status');
    const showStatus = (text, isError) => {
      if (!status) return;
      status.textContent = text;
      status.classList.toggle('is-error', isError);
      status.hidden = false;
    };
    const missing = [...form.querySelectorAll('[required]')].find(field => !field.value.trim());
    if (missing) {
      showStatus('Please enter your name and phone number.', true);
      missing.focus();
      return;
    }

    const data = new FormData(form);
    const lines = ['Hello Reach Dream Travel, I would like a quote for a Himachal trip.'];
    data.forEach((value, key) => {
      const text = String(value).trim();
      if (text && labels[key]) lines.push(`${labels[key]}: ${text}`);
    });
    // Open WhatsApp straight away (inside the click) so pop-up blockers allow it.
    window.open(waLink(lines.join('\n')), '_blank', 'noopener');

    const button = form.querySelector('[type="submit"]');
    if (button) button.disabled = true;
    showStatus('Sending your enquiry…', false);
    fetch('send-enquiry.php', { method: 'POST', body: data })
      .then(response => response.json())
      .then(result => {
        showStatus(result.message, !result.ok);
        if (result.ok) form.reset();
      })
      .catch(() => showStatus('Could not send by email. Please send the WhatsApp message or call us.', true))
      .finally(() => { if (button) button.disabled = false; });
  }));

  // Vehicle showroom: tabs switch the vehicle shown in the detail panel.
  document.querySelectorAll('[data-showroom]').forEach(showroom => {
    const tabs = [...showroom.querySelectorAll('[role="tab"]')];
    const activate = (tab, focus) => {
      tabs.forEach(other => {
        const selected = other === tab;
        other.setAttribute('aria-selected', String(selected));
        other.tabIndex = selected ? 0 : -1;
        const panel = document.getElementById(other.getAttribute('aria-controls'));
        panel.hidden = !selected;
        if (selected) {
          panel.classList.remove('is-entering');
          if (!reducedMotion) { void panel.offsetWidth; panel.classList.add('is-entering'); }
        }
      });
      if (focus) tab.focus();
    };
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        activate(tab);
        // On small screens bring the details into view after choosing a vehicle.
        if (window.innerWidth < 992) document.getElementById(tab.getAttribute('aria-controls')).scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
      });
      tab.addEventListener('keydown', event => {
        const visible = tabs.filter(t => !t.classList.contains('is-hidden'));
        const index = visible.indexOf(tab);
        let next = null;
        if (event.key === 'ArrowDown' || event.key === 'ArrowRight') next = visible[(index + 1) % visible.length];
        if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') next = visible[(index - 1 + visible.length) % visible.length];
        if (event.key === 'Home') next = visible[0];
        if (event.key === 'End') next = visible[visible.length - 1];
        if (next) { event.preventDefault(); activate(next, true); }
      });
    });
    // When a group-size filter hides the selected vehicle, select the first one still shown.
    showroom.querySelectorAll('.filter-bar button').forEach(button => button.addEventListener('click', () => {
      const current = tabs.find(t => t.getAttribute('aria-selected') === 'true');
      if (current && current.classList.contains('is-hidden')) {
        const first = tabs.find(t => !t.classList.contains('is-hidden'));
        if (first) activate(first);
      }
    }));
  });

  // Route guide "We recommend" links select that vehicle in the showroom.
  document.querySelectorAll('[data-pick]').forEach(link => link.addEventListener('click', event => {
    const tab = document.getElementById(link.dataset.pick);
    if (!tab) return;
    event.preventDefault();
    const showAll = tab.closest('[data-showroom]')?.querySelector('.filter-bar [data-filter="all"]');
    if (tab.classList.contains('is-hidden')) showAll?.click();
    tab.click();
    tab.closest('.showroom').scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
  }));

  // Fullscreen lightbox for any [data-gallery] grid; steps through the photos currently visible.
  const lightbox = document.getElementById('lightbox');
  let galleryItems = [];
  let galleryIndex = 0;
  const showImage = index => {
    if (!galleryItems.length || !lightbox) return;
    galleryIndex = (index + galleryItems.length) % galleryItems.length;
    const item = galleryItems[galleryIndex];
    const image = lightbox.querySelector('img');
    image.src = item.dataset.full;
    image.alt = item.querySelector('img').alt;
    lightbox.querySelector('.lightbox-caption b').textContent = item.dataset.caption || item.querySelector('img').alt;
    lightbox.querySelector('.lightbox-caption small').textContent = item.dataset.credit || '';
    lightbox.querySelector('.lightbox-count').textContent = `${galleryIndex + 1} / ${galleryItems.length}`;
  };
  document.querySelectorAll('[data-gallery]').forEach(grid => grid.querySelectorAll('[data-full]').forEach(button => button.addEventListener('click', () => {
    galleryItems = [...grid.querySelectorAll('[data-full]')].filter(item => !item.classList.contains('is-hidden'));
    showImage(galleryItems.indexOf(button));
    lightbox.showModal();
    document.body.classList.add('no-scroll');
  })));
  lightbox?.querySelector('.lightbox-close')?.addEventListener('click', () => lightbox.close());
  lightbox?.querySelector('.lightbox-prev')?.addEventListener('click', () => showImage(galleryIndex - 1));
  lightbox?.querySelector('.lightbox-next')?.addEventListener('click', () => showImage(galleryIndex + 1));
  lightbox?.addEventListener('click', event => { if (event.target === lightbox) lightbox.close(); });
  lightbox?.addEventListener('close', () => document.body.classList.remove('no-scroll'));
  lightbox?.addEventListener('keydown', event => {
    if (event.key === 'ArrowLeft') showImage(galleryIndex - 1);
    if (event.key === 'ArrowRight') showImage(galleryIndex + 1);
  });

  // Keep every remaining WhatsApp link pointed at the configurable business number.
  document.querySelectorAll('a[href*="wa.me"]').forEach(link => {
    if (!link.href.includes('?text=')) link.href = waLink('Hello Reach Dream Travel, I would like to plan a Himachal trip.');
  });
})();
