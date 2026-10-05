<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'about';
$pageTitle = 'About Us';
$pageDescription = 'Reach Dream Travel plans private North India tours across Himachal, Kashmir, Uttarakhand, Rajasthan and the Golden Triangle, with routes, drivers and stays arranged for you.';
$pageHero = [
    'crumb'   => 'About',
    'eyebrow' => 'Who we are',
    'title'   => 'About Reach Dream Travel',
    'lead'    => 'Private trips across North India, with the route, driver and stays planned around your travel dates.',
    'image'   => img('taj-mahal-agra'),
];
require __DIR__ . '/includes/header.php';


?>

    <!-- Story -->
    <section class="section about-section">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-12 col-lg-6 about-visual">
            <div class="about-collage">
              <img class="about-main" src="<?= e(img('taj-mahal-agra', true)) ?>" srcset="<?= e(img('taj-mahal-agra', true)) ?> 900w, <?= e(img('taj-mahal-agra')) ?> 2000w" sizes="(min-width: 992px) 560px, 100vw" alt="Taj Mahal in Agra" loading="lazy">
              <img class="about-sub" data-depth="-0.12" src="<?= e(img('spiti-key', true)) ?>" alt="Key Monastery in Spiti" loading="lazy">
              <div class="about-stamp" data-depth="0.18"><b>RD</b><small>YOUR JOURNEY<br>OUR PASSION</small></div>
              <div class="about-chip" data-depth="0.08"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><b>Trips across North India</b><small>Pickup by arrangement</small></span></div>
            </div>
          </div>
          <div class="col-12 col-lg-6 about-copy">
            <span class="eyebrow">Our story</span>
            <h2>Private road trips, planned with care.</h2>
            <p>Reach Dream Travel brings your route, private vehicle, driver and stays together in one plan. Choose a Himalayan escape, a Kashmir holiday, a Rajasthan circuit or a classic Delhi–Agra tour.</p>
            <p>Share your dates, group size and the places you have in mind. We’ll shape the itinerary around your pace, route conditions and preferred style of stay.</p>
            <ul class="about-list list-unstyled">
              <li><i class="fa-solid fa-route" aria-hidden="true"></i> Pickup and drop at your door</li>
              <li><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Car, hotels &amp; sightseeing together</li>
              <li><i class="fa-solid fa-binoculars" aria-hidden="true"></i> Trip plan changed to suit you</li>
              <li><i class="fa-solid fa-user-shield" aria-hidden="true"></i> Experienced drivers suited to your route</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Values -->
    <section class="section section-values">
      <span class="bg-word" data-drift="0.2" aria-hidden="true">OUR VALUES</span>
      <div class="container">
        <div class="section-heading center">
          <span class="eyebrow">What we stand for</span>
          <h2>What goes into the plan.</h2>
          <p>Route, vehicle and stay options are discussed before you confirm the trip.</p>
        </div>
        <div class="value-grid">
          <article class="value-card"><span class="value-no">01</span><span class="value-icon fi-amber"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i></span><h3>Route first</h3><p>We discuss drive times, stops and road conditions while shaping the itinerary.</p></article>
          <article class="value-card"><span class="value-no">02</span><span class="value-icon fi-green"><i class="fa-solid fa-person-walking-luggage" aria-hidden="true"></i></span><h3>Your own trip</h3><p>Only your group in the car. Stop for photos or tea whenever you like.</p></article>
          <article class="value-card"><span class="value-no">03</span><span class="value-icon fi-blue"><i class="fa-solid fa-handshake" aria-hidden="true"></i></span><h3>Know what’s included</h3><p>Your quote lists the vehicle, stays and other confirmed costs before you book.</p></article>
          <article class="value-card"><span class="value-no">04</span><span class="value-icon fi-rose"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i></span><h3>Vehicle for the route</h3><p>Choose from cars, 4x4s and group vehicles based on your route and party size.</p></article>
        </div>
      </div>
    </section>

    <!-- Destinations -->
    <section class="section section-destinations">
      <div class="container">
        <div class="section-heading split">
          <div><span class="eyebrow">Where we travel</span><h2>Places we take you.</h2><p>From easy hill stations to long trips in the high mountains.</p></div>
          <a class="text-link" href="destinations.php">All destinations <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="dest-grid">
<?php $i = 0; foreach ($destinations as $destSlug => $d): $hasScenicPhoto = !in_array($destSlug, ['delhi', 'agra', 'rajasthan'], true); ?>
          <a class="dest-card<?= $i === 0 ? ' dest-wide' : '' ?><?= $hasScenicPhoto ? '' : ' is-text-only' ?>" href="<?= e(destination_url($destSlug)) ?>">
<?php if ($hasScenicPhoto): ?>
            <img src="<?= e(img($d['image'], true)) ?>" alt="" loading="lazy">
<?php endif; ?>
            <div class="dest-body"><span class="dest-no"><?= sprintf('%02d', ++$i) ?></span><h3><?= e($d['name']) ?></h3><p><?= e($d['tagline']) ?> · <?= e($d['drive']) ?></p></div>
          </a>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- How we plan -->
    <section class="journey-band" aria-labelledby="planTitle">
      <div class="container">
        <div class="row g-5 align-items-center">
          <div class="col-12 col-lg-5">
            <span class="eyebrow eyebrow-light">How we work</span>
            <h2 id="planTitle">Booking is easy.</h2>
            <p>Tell us where you want to go. We plan everything and pick you up from your door.</p>
            <div class="counter-grid">
              <div class="counter-item"><strong><?= count($vehicles) ?></strong><small>Vehicle options</small></div>
              <div class="counter-item"><strong><?= count($destinations) ?></strong><small>Destinations</small></div>
              <div class="counter-item"><strong><?= count($publicPackages) ?></strong><small>Sample itineraries</small></div>
            </div>
          </div>
          <div class="col-12 col-lg-7">
            <ol class="steps list-unstyled">
              <li class="step"><span class="step-no">01</span><div><h3>Tell us your plan</h3><p>Your dates, how many people, and where you want to go — even a rough idea is fine.</p></div><i class="fa-solid fa-comments step-icon" aria-hidden="true"></i></li>
              <li class="step"><span class="step-no">02</span><div><h3>Get your trip plan</h3><p>We send you the day-by-day plan, hotels, car and price.</p></div><i class="fa-solid fa-map-location-dot step-icon" aria-hidden="true"></i></li>
              <li class="step"><span class="step-no">03</span><div><h3>Start your trip</h3><p>The driver picks you up from your door. We are one call away the whole time.</p></div><i class="fa-solid fa-car-side step-icon" aria-hidden="true"></i></li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Why us -->
    <section class="section why-section">
      <div class="container">
        <div class="why-panel">
          <div class="why-photo"><img src="<?= e(img('trek-lahaul', true)) ?>" srcset="<?= e(img('trek-lahaul', true)) ?> 900w, <?= e(img('trek-lahaul')) ?> 2000w" sizes="(min-width: 992px) 600px, 100vw" alt="Travellers walking towards snow peaks in Lahaul" loading="lazy"></div>
          <div class="why-copy">
            <span class="eyebrow">Why travel with us</span>
            <h2>Why people book with us.</h2>
            <ul class="why-list list-unstyled">
              <li><i class="fa-solid fa-car-side" aria-hidden="true"></i><div><b>The right car for your trip</b><span>Small cars, 7-seaters, 4×4s for Spiti and mini buses for groups.</span></div></li>
              <li><i class="fa-solid fa-hotel" aria-hidden="true"></i><div><b>Hotels for your budget</b><span>Standard, deluxe or premium — you choose.</span></div></li>
              <li><i class="fa-solid fa-sliders" aria-hidden="true"></i><div><b>Change the plan any time</b><span>Add a day, skip a place, or add a new stop.</span></div></li>
              <li><i class="fa-solid fa-comments" aria-hidden="true"></i><div><b>Talk through the details</b><span>Contact our team about your route, pickup and any changes to the plan.</span></div></li>
            </ul>
            <div class="btn-row"><a class="btn btn-dark" href="contact.php">Start planning <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><a class="text-link" href="vehicles.php">View our vehicles <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
          </div>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
