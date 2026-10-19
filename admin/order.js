(() => {
  document.querySelectorAll('[data-order-list]').forEach((list) => {
    const form = document.querySelector(`[data-order-form="${list.dataset.orderList}"]`);
    if (!form) return;
    const status = form.querySelector('[data-order-status]');
    const rows = () => Array.from(list.children);
    const order = () => rows().map((row) => row.dataset.orderSlug);
    let savedOrder = order();
    let saving = false;
    let drag = null;
    let frame = null;

    const changed = async (row) => {
      const nextOrder = order();
      if (saving || JSON.stringify(nextOrder) === JSON.stringify(savedOrder)) return;
      saving = true;
      const handles = list.querySelectorAll('.dash-order-handle');
      handles.forEach((handle) => { handle.disabled = true; });
      status.textContent = 'Saving order...';
      try {
        const body = new FormData(form);
        nextOrder.forEach((slug) => body.append('order[]', slug));
        // The input named "action" shadows the form.action DOM property.
        const endpoint = new URL(form.getAttribute('action') || window.location.href, window.location.href);
        const response = await fetch(endpoint, {
          method: 'POST', body, credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!(response.headers.get('Content-Type') || '').includes('application/json')) {
          throw new Error('The server returned a page instead of a save confirmation. Refresh the page and sign in again if needed.');
        }
        const result = await response.json();
        if (!response.ok || result.ok !== true) throw new Error(result.error || 'Please refresh and try again.');
        savedOrder = nextOrder;
        status.textContent = `${row.querySelector('strong').textContent}: position ${rows().indexOf(row) + 1} of ${rows().length}. Order saved.`;
      } catch (error) {
        const items = new Map(rows().map((item) => [item.dataset.orderSlug, item]));
        savedOrder.forEach((slug) => list.append(items.get(slug)));
        status.textContent = `Could not save order. ${error.message} Refresh the page before trying again.`;
      } finally {
        saving = false;
        handles.forEach((handle) => { handle.disabled = false; });
        row.querySelector('.dash-order-handle').focus({ preventScroll: true });
      }
    };

    list.addEventListener('keydown', (event) => {
      const handle = event.target.closest('.dash-order-handle');
      if (!handle || !['ArrowUp', 'ArrowDown'].includes(event.key) || drag || saving) return;
      event.preventDefault();
      const row = handle.closest('[data-order-slug]');
      if (event.key === 'ArrowUp' && row.previousElementSibling) {
        list.insertBefore(row, row.previousElementSibling);
      } else if (event.key === 'ArrowDown' && row.nextElementSibling) {
        list.insertBefore(row.nextElementSibling, row);
      }
      handle.focus({ preventScroll: true });
      row.scrollIntoView({ block: 'nearest' });
      changed(row);
    });

    const place = () => {
      const next = rows().find((row) => {
        if (row === drag.row) return false;
        const rect = row.getBoundingClientRect();
        return drag.y < rect.top + rect.height / 2;
      });
      list.insertBefore(drag.row, next || null);
    };

    const animate = () => {
      if (!drag?.active) return;
      const edge = 80;
      const speed = drag.y < edge ? -Math.min(18, (edge - drag.y) / 4)
        : drag.y > window.innerHeight - edge ? Math.min(18, (drag.y - window.innerHeight + edge) / 4) : 0;
      if (speed) window.scrollBy(0, speed);
      place();
      frame = window.requestAnimationFrame(animate);
    };

    list.addEventListener('pointerdown', (event) => {
      const handle = event.target.closest('.dash-order-handle');
      if (!handle || event.button !== 0 || drag || saving) return;
      event.preventDefault();
      handle.focus({ preventScroll: true });
      drag = { row: handle.closest('[data-order-slug]'), handle, id: event.pointerId,
        startX: event.clientX, startY: event.clientY, y: event.clientY, active: false, before: rows() };
      list.setPointerCapture(event.pointerId);
    });

    list.addEventListener('pointermove', (event) => {
      if (!drag || drag.id !== event.pointerId) return;
      drag.y = event.clientY;
      if (!drag.active && Math.hypot(event.clientX - drag.startX, event.clientY - drag.startY) > 5) {
        drag.active = true;
        drag.row.classList.add('is-ordering');
        document.body.classList.add('is-ordering');
        animate();
      }
    });

    const finish = (cancelled = false) => {
      if (!drag) return;
      const current = drag;
      drag = null;
      window.cancelAnimationFrame(frame);
      if (cancelled) current.before.forEach((row) => list.append(row));
      current.row.classList.remove('is-ordering');
      document.body.classList.remove('is-ordering');
      if (list.hasPointerCapture(current.id)) list.releasePointerCapture(current.id);
      current.handle.focus({ preventScroll: true });
      changed(current.row);
    };
    list.addEventListener('pointerup', (event) => { if (drag?.id === event.pointerId) finish(); });
    list.addEventListener('pointercancel', (event) => { if (drag?.id === event.pointerId) finish(true); });
    list.addEventListener('lostpointercapture', (event) => { if (drag?.id === event.pointerId) finish(true); });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && drag) { event.preventDefault(); finish(true); }
    });
    form.addEventListener('submit', (event) => event.preventDefault());
  });
})();
