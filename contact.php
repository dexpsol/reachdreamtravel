<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'contact';
$pageTitle = 'Contact Us';
$pageDescription = 'Contact Reach Dream Travel to plan your Himachal trip. Call, WhatsApp or send an enquiry for tours, hotels and cars.';
$pageHero = [
    'crumb'   => 'Contact',
    'eyebrow' => 'Get in touch',
    'title'   => 'Contact Us',
    'lead'    => 'Tell us where you want to go. We will come back with a route, a stay and the right vehicle.',
    'image'   => img('shimla-city'),
];
require __DIR__ . '/includes/header.php';

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
          <div class="contact-card"><span class="cc-icon fi-rose"><i class="fa-solid fa-headset" aria-hidden="true"></i></span><small>Support</small><b>Always here to help</b><em><i class="fa-solid fa-clock" aria-hidden="true"></i> Support 24/7</em></div>
        </div>
      </div>
    </section>

    <!-- Form + map -->
    <section class="section contact-section">
      <div class="container">
        <div class="row g-4 g-lg-5 align-items-stretch">
          <div class="col-12 col-lg-7">
            <div class="contact-form-wrap">
              <span class="eyebrow">Trip enquiry</span>
              <h2 class="contact-title">Let us help you plan.</h2>
              <p class="contact-lead">Fill in what you know — the rest we can work out together. Your enquiry is emailed to us and also opens in WhatsApp so you can send it straight to our phone.</p>
              <form class="trip-form contact-form" aria-label="Trip enquiry" novalidate>
                <div class="field-row">
                  <label class="field"><span>Your name</span><input type="text" name="name" autocomplete="name" placeholder="Full name" required></label>
                  <label class="field"><span>Phone</span><input type="tel" name="phone" autocomplete="tel" placeholder="+91" required></label>
                </div>
                <div class="field-row">
                  <label class="field"><span>Destination</span>
                    <select name="destination"><?php trip_select_options(); ?></select>
                  </label>
                  <label class="field"><span>Travel date</span><input type="date" name="date"></label>
                </div>
                <div class="field-row">
                  <label class="field"><span>Travellers</span><input type="number" name="travellers" min="1" max="40" value="2" inputmode="numeric"></label>
                  <label class="field"><span>Vehicle</span>
                    <select name="vehicle"><?php vehicle_select_options(); ?></select>
                  </label>
                </div>
                <label class="field"><span>Anything else?</span><textarea name="message" rows="4" placeholder="Hotel category, special requests, pickup point…"></textarea></label>
                <input class="visually-hidden" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                <button class="btn btn-gold" type="submit">Send enquiry <i class="fa-brands fa-whatsapp" aria-hidden="true"></i></button>
                <p class="form-status" role="status" aria-live="polite" hidden></p>
              </form>
            </div>
          </div>
          <div class="col-12 col-lg-5">
            <div class="map-card">
              <iframe title="Map showing Himachal Pradesh" src="https://www.google.com/maps?q=Himachal%20Pradesh&amp;z=7&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
              <div class="map-info">
                <b><i class="fa-solid fa-mountain-sun" aria-hidden="true"></i> Trips all over Himachal</b>
                <p>We pick you up from your home, hotel, airport or railway station — and drop you back after the trip.</p>
                <a class="text-link" href="https://www.google.com/maps/search/?api=1&amp;query=Himachal%20Pradesh" target="_blank" rel="noopener">Open in Google Maps <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
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
