<?php
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/backend/ticket-enquiry.php';
$today = (new DateTimeImmutable('today', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d');
$pageKey = 'tickets';
$pageTitle = 'Railway, Air & Bus Ticket Enquiry';
$pageDescription = 'Enquire about railway, air and bus tickets with Reach Dream Travel. Share your route, travel dates and number of passengers.';
$pageHero = [
    'crumb' => 'Tickets',
    'eyebrow' => 'Every journey starts with a ticket',
    'title' => 'Railway, Air & Bus Tickets',
    'lead' => 'Tell us where you are headed. Our team will help you find a suitable travel option.',
    'image' => img('kinnaur-peaks'),
];
require __DIR__ . '/includes/header.php';
?>
    <section class="section tickets-section" aria-labelledby="ticket-form-heading">
      <div class="container">
        <div class="tickets-layout">
          <aside class="tickets-intro">
            <span class="eyebrow">Plan your journey</span>
            <h2>By rail, by air,<br>or by road.</h2>
            <p>Share your travel details in one quick enquiry. We will get back to you with availability and fares.</p>
            <ul class="tickets-services">
              <li><i class="fa-solid fa-train" aria-hidden="true"></i><div><h3>Railway tickets</h3><p>Choose your stations and preferred travel class.</p></div></li>
              <li><i class="fa-solid fa-plane-departure" aria-hidden="true"></i><div><h3>Air tickets</h3><p>Domestic or international, one way or return.</p></div></li>
              <li><i class="fa-solid fa-bus" aria-hidden="true"></i><div><h3>Bus tickets</h3><p>Seater or sleeper options for your route.</p></div></li>
            </ul>
            <a class="text-link" href="tel:<?= e($site['phoneLink']) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?= e($site['phone']) ?></a>
          </aside>
          <div class="contact-form-wrap tickets-form-wrap">
            <span class="eyebrow">Send us your requirements</span>
            <h2 id="ticket-form-heading" class="contact-title">Ticket enquiry</h2>
            <p class="contact-lead">Fields marked * are required. This is an enquiry; our team will confirm fares and availability before booking.</p>
            <form class="trip-form tickets-form" data-ticket-form aria-label="Ticket enquiry" method="post" action="backend/send-enquiry.php">
              <input type="hidden" name="enquiry_type" value="ticket">
              <fieldset class="ticket-types">
                <legend>Ticket type *</legend>
<?php foreach (['Railway' => 'fa-train', 'Air' => 'fa-plane-departure', 'Bus' => 'fa-bus'] as $type => $icon): ?>
                <label><input type="radio" name="ticket_type" value="<?= e($type) ?>" required<?= $type === 'Railway' ? ' checked' : '' ?>><span><i class="fa-solid <?= e($icon) ?>" aria-hidden="true"></i><?= e($type) ?></span></label>
<?php endforeach; ?>
              </fieldset>
              <div class="field-row">
                <label class="field"><span>Full name *</span><input type="text" name="name" autocomplete="name" maxlength="150" placeholder="Your full name" required></label>
                <label class="field"><span>Phone number *</span><input type="tel" name="phone" autocomplete="tel" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" placeholder="10 digit mobile number" required></label>
              </div>
              <div class="field-row">
                <label class="field"><span>Email</span><input type="email" name="email" autocomplete="email" maxlength="254" placeholder="you@example.com"></label>
                <label class="field"><span>Number of pax *</span><input type="number" name="passengers" inputmode="numeric" min="1" max="100" value="1" required></label>
              </div>
              <div class="field-row">
                <label class="field"><span>From *</span><input type="text" name="journey_from" maxlength="150" placeholder="Departure city or station" required></label>
                <label class="field"><span>To *</span><input type="text" name="journey_to" maxlength="150" placeholder="Arrival city or station" required></label>
              </div>
              <div class="field-row">
                <label class="field"><span>Travel date *</span><input type="date" name="date" min="<?= e($today) ?>" required></label>
                <label class="field"><span>Journey type *</span><select name="journey_type" required><option>One way</option><option>Return</option></select></label>
              </div>
              <label class="field" data-ticket-return><span>Return date (required for a return journey)</span><input type="date" name="return_date" min="<?= e($today) ?>"></label>
              <div class="field-row">
                <label class="field"><span>Preferred class / seat *</span><select name="travel_class" required><option>No preference</option>
<?php foreach (ticket_travel_classes() as $type => $classes): ?>
                  <optgroup label="<?= e($type) ?>" data-ticket-mode="<?= e($type) ?>"><?php foreach ($classes as $class): ?><option><?= e($class) ?></option><?php endforeach; ?></optgroup>
<?php endforeach; ?>
                </select></label>
                <label class="field"><span>Preferred departure time</span><select name="preferred_time"><option>No preference</option><option>Morning</option><option>Afternoon</option><option>Evening</option><option>Night</option></select></label>
              </div>
              <label class="field"><span>Preferred train / airline / bus operator</span><input type="text" name="preferred_service" maxlength="150" placeholder="Name or service number, if you have one"></label>
              <label class="field"><span>Additional requirements</span><textarea name="message" rows="3" maxlength="1500" placeholder="Children's ages, seat preferences, flexible dates or any other requests"></textarea></label>
              <input class="visually-hidden" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
              <button class="btn btn-gold w-100" type="submit">Send ticket enquiry <i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>
              <p class="form-status" role="status" aria-live="polite" hidden></p>
              <small class="form-note">We will save your enquiry and open WhatsApp with your details ready to send.</small>
            </form>
          </div>
        </div>
      </div>
    </section>
    <script src="tickets.js?v=<?= (int) filemtime(__DIR__ . '/tickets.js') ?>" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
