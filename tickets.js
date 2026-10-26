(() => {
  const form = document.querySelector('[data-ticket-form]');
  if (!form) return;
  const date = form.elements.date;
  const returnDate = form.elements.return_date;
  const journey = form.elements.journey_type;
  const travelClass = form.elements.travel_class;
  const from = form.elements.journey_from;
  const to = form.elements.journey_to;
  const update = () => {
    const type = form.elements.ticket_type.value;
    const place = type === 'Air' ? 'airport' : type === 'Railway' ? 'station' : 'boarding point';
    from.placeholder = `Departure city or ${place}`;
    to.placeholder = `Arrival city or ${type === 'Bus' ? 'drop-off point' : place}`;
    form.querySelectorAll('[data-ticket-mode]').forEach(group => {
      group.disabled = group.dataset.ticketMode !== type;
      group.hidden = group.disabled;
    });
    if (travelClass.selectedOptions[0]?.parentElement.disabled) travelClass.value = 'No preference';
    const isReturn = journey.value === 'Return';
    form.querySelector('[data-ticket-return]').hidden = !isReturn;
    returnDate.disabled = !isReturn;
    returnDate.required = isReturn;
    returnDate.min = date.value || date.min;
    to.setCustomValidity(from.value.trim().toLowerCase() === to.value.trim().toLowerCase() && to.value.trim() ? 'Choose a different destination.' : '');
  };
  form.addEventListener('change', update);
  from.addEventListener('input', update);
  to.addEventListener('input', update);
  form.addEventListener('reset', () => setTimeout(update, 0));
  update();
})();
