<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'contact';
$pageTitle = 'Contact Us';
$pageDescription = 'Contact Reach Dream Travel to plan a private North India tour. Ask about Himachal, Kashmir, Uttarakhand, Rajasthan, vehicles, stays and custom itineraries.';
$pageHero = [
    'crumb'   => 'Contact',
    'eyebrow' => 'Get in touch',
    'title'   => 'Contact Us',
    'lead'    => 'Tell us where you want to go. We will come back with a route, a stay and the right vehicle.',
    'image'   => img('taj-mahal-agra'),
];
require __DIR__ . '/includes/header.php';

$travelGroups = ['Couple', 'Family', 'Friends', 'Solo', 'Seniors'];
$travelGroup = is_string($_GET['travelling'] ?? null) ? $_GET['travelling'] : '';
$purpose = in_array($travelGroup, $travelGroups, true) ? $travelGroup . ' trip' : '';
$tripInterests = [
    'holiday' => 'Holiday tour',
    'temple' => 'Temple darshan',
    'adventure' => 'Trekking and camping',
    'activities' => 'Games and activities',
    'special' => 'Special-interest trip',
    'solo' => 'Solo trip',
    'new-year' => 'New Year plan',
    'group' => 'Group tour',
];
$interest = is_string($_GET['interest'] ?? null) ? $_GET['interest'] : '';
if (isset($tripInterests[$interest])) $purpose = $tripInterests[$interest];

$faqs = [
    ['How soon should I book?', 'For peak season (May–June, October and the winter holidays) we suggest booking a few weeks ahead. For the rest of the year, a few days is often enough — just ask.'],
    ['Do I need to pay to get a quote?', 'No. Quotes are free and there is no obligation to book.'],
    ['When is the best time to visit Spiti?', 'The full Spiti circuit via Kinnaur and Manali is usually open from late May or June to October, depending on snow on the passes. The Kinnaur side is reachable for more of the year.'],
    ['Where do you pick us up?', 'From your home, your hotel, the airport or the railway station. Pickup from other cities can also be arranged.'],
];
?>

    <!-- Contact cards -->
    <section class="contact-cards-section">
      <div class="container">
        <div class="contact-cards">
          <a class="contact-card" href="tel:<?= e($site['phoneLink']) ?>"><span class="cc-icon fi-amber"><i class="fa-solid fa-phone" aria-hidden="true"></i></span><small>Call us</small><b><?= e($site['phone']) ?></b><em>Tap to call <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></em></a>
          <a class="contact-card" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><span class="cc-icon fi-green"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span><small>WhatsApp</small><b>Chat with us</b><em>Open chat <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></em></a>
          <a class="contact-card" href="mailto:<?= e($site['email']) ?>"><span class="cc-icon fi-blue"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span><small>Email</small><b class="cc-email"><?= e($site['email']) ?></b><em>Write to us <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></em></a>
          <div class="contact-card"><span class="cc-icon fi-rose"><i class="fa-solid fa-route" aria-hidden="true"></i></span><small>Trip planning</small><b>Discuss your route</b><em><i class="fa-solid fa-message" aria-hidden="true"></i> Call or message</em></div>
        </div>
      </div>
    </section>

    <!-- Form + map -->
    <section class="section contact-section">
      <div class="container">
        <div class="row g-4 g-lg-5 align-items-stretch">
          <div class="col-12 col-lg-7">
            <div class="contact-form-wrap">
              <span class="eyebrow">Build your own trip</span>
              <h2 class="contact-title">Customized package</h2>
              <p class="contact-lead">Tell us what you have in mind. We’ll email your request and open a WhatsApp message with the same details ready to send.</p>
              <form class="trip-form contact-form" aria-label="Customized package enquiry" novalidate>
                <div class="field-row">
                  <label class="field"><span>Your name</span><input type="text" name="name" autocomplete="name" placeholder="Full name" required></label>
                  <label class="field"><span>Phone</span><input type="tel" name="phone" autocomplete="tel" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" placeholder="Enter your number" required></label>
                </div>
                <label class="field"><span>Email</span><input type="email" name="email" autocomplete="email" placeholder="you@example.com"></label>
                <div class="field-row">
                  <label class="field"><span>What would you like the package for?</span><input type="text" name="purpose" value="<?= e($purpose) ?>" placeholder="Places, occasion or experience"></label>
                  <label class="field"><span>Arrival date</span><input type="date" name="arrival"></label>
                </div>
                <div class="field-row" data-guest-count>
                  <label class="field"><span>Departure date</span><input type="date" name="departure"></label>
                  <label class="field"><span>Adults</span><input type="number" name="adults" min="1" max="40" value="2" inputmode="numeric"></label>
                  <label class="field"><span>Kids</span><input type="number" name="kids" min="0" max="40" value="0" inputmode="numeric"></label>
                </div>
                <div class="field-row">
                  <label class="field"><span>Room required</span><select name="room_required"><option value="Yes">Yes</option><option value="No">No</option></select></label>
                  <label class="field"><span>Room type</span><select name="room_type"><option value="Standard">Standard</option><option value="Deluxe">Deluxe</option><option value="Family room">Family room</option></select></label>
                </div>
                <div class="field-row">
                  <label class="field"><span>Meal plan</span><select name="meal_plan"><option value="No preference">No preference</option><option value="Room only">Room only</option><option value="Breakfast">Breakfast</option><option value="Breakfast and dinner">Breakfast and dinner</option><option value="All meals">All meals</option></select></label>
                  <label class="field"><span>Transportation</span><select name="transportation" data-transport-choice><option value="Yes, include transportation">Yes, include transportation</option><option value="No thanks">No thanks</option></select></label>
                </div>
                <label class="field" data-vehicle-field><span>Vehicle type</span><select name="vehicle"><?php vehicle_select_options(); ?></select></label>
                <label class="field"><span>Anything else?</span><textarea name="message" rows="4" placeholder="Hotel category, special requests, pickup point…"></textarea></label>
                <input class="visually-hidden" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                <button class="btn btn-gold" type="submit">Send package request <i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>
                <p class="form-status" role="status" aria-live="polite" hidden></p>
              </form>
            </div>
          </div>
          <div class="col-12 col-lg-5">
            <div class="map-card">
              <iframe title="Map showing India" src="https://www.google.com/maps?q=India&amp;z=4&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
              <div class="map-info">
                <b><i class="fa-solid fa-route" aria-hidden="true"></i> Trips across North India</b>
                <p>Pickup and drop-off points are arranged to suit your route and confirmed before you travel.</p>
                <a class="text-link" href="https://www.google.com/maps/search/?api=1&amp;query=India" target="_blank" rel="noopener">Explore destinations <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="section faq-section">
      <div class="container">
        <div class="row g-5">
          <div class="col-12 col-lg-4">
            <span class="eyebrow">Before you ask</span>
            <h2 class="faq-title">Common questions</h2>
            <p class="faq-lead">Still unsure? Send us a message on WhatsApp — it is the fastest way to reach us.</p>
            <a class="btn btn-dark" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Message us</a>
          </div>
          <div class="col-12 col-lg-8">
            <div class="faq-list">
<?php foreach ($faqs as $i => [$q, $a]): ?>
              <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>><summary><?= e($q) ?><span aria-hidden="true"></span></summary><p><?= e($a) ?></p></details>
<?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
