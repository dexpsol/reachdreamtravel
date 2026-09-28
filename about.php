<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'about';
$pageTitle = 'About Us';
$pageDescription = 'Reach Dream Travel is an Amritsar travel company. We plan Himachal trips with car, driver, hotels and sightseeing — pickup and drop in Amritsar.';
$pageHero = [
    'crumb'   => 'About',
    'eyebrow' => 'Who we are',
    'title'   => 'About Reach Dream Travel',
    'lead'    => 'An Amritsar travel team taking families, friends and groups to the Himachal mountains.',
    'image'   => img('kinnaur-autumn'),
];
require __DIR__ . '/includes/header.php';


?>

    <!-- Story -->
    <section class="section about-section">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-12 col-lg-6 about-visual">
            <div class="about-collage">
              <img class="about-main" src="<?= e(img('kinnaur-sangla', true)) ?>" srcset="<?= e(img('kinnaur-sangla', true)) ?> 900w, <?= e(img('kinnaur-sangla')) ?> 2000w" sizes="(min-width: 992px) 560px, 100vw" alt="Sangla valley in Kinnaur" loading="lazy">
              <img class="about-sub" data-depth="-0.12" src="<?= e(img('spiti-key', true)) ?>" alt="Key Monastery in Spiti" loading="lazy">
              <div class="about-stamp" data-depth="0.18"><b>RD</b><small>YOUR JOURNEY<br>OUR PASSION</small></div>
              <div class="about-chip" data-depth="0.08"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><b>Based in Amritsar</b><small>Trips all over Himachal</small></span></div>
            </div>
          </div>
          <div class="col-12 col-lg-6 about-copy">
            <span class="eyebrow">Our story</span>
            <h2>Mountain trips made easy, from Amritsar.</h2>
            <p>Reach Dream Travel is based in Amritsar, Punjab. Many people here love the Himachal mountains, but planning the trip — the car, the hotels, the route — takes time. So we do it for you.</p>
            <p>We pick you up from your home or hotel in Amritsar, take you to the mountains and bring you back. Our drivers know the hill roads well, and we are always one call away.</p>
            <ul class="about-list list-unstyled">
              <li><i class="fa-solid fa-route" aria-hidden="true"></i> Pickup and drop in Amritsar</li>
              <li><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Car, hotels &amp; sightseeing together</li>
              <li><i class="fa-solid fa-binoculars" aria-hidden="true"></i> Trip plan changed to suit you</li>
              <li><i class="fa-solid fa-user-shield" aria-hidden="true"></i> Safe, experienced hill drivers</li>
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
          <h2>Our four promises.</h2>
          <p>For a short Shimla trip or a long Spiti trip — this is how we work.</p>
        </div>
        <div class="value-grid">
          <article class="value-card"><span class="value-no">01</span><span class="value-icon fi-amber"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i></span><h3>We know the roads</h3><p>Which roads are open, the best places to stop, and how long each drive really takes.</p></article>
          <article class="value-card"><span class="value-no">02</span><span class="value-icon fi-green"><i class="fa-solid fa-person-walking-luggage" aria-hidden="true"></i></span><h3>Your own trip</h3><p>Only your group in the car. Stop for photos or tea whenever you like.</p></article>
          <article class="value-card"><span class="value-no">03</span><span class="value-icon fi-blue"><i class="fa-solid fa-handshake" aria-hidden="true"></i></span><h3>Clear prices</h3><p>We tell you exactly what is included. No hidden charges and no pressure to book.</p></article>
          <article class="value-card"><span class="value-no">04</span><span class="value-icon fi-rose"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i></span><h3>Safety first</h3><p>Good drivers, well-kept cars, and help by phone at any time.</p></article>
        </div>
      </div>
    </section>

    <!-- Destinations -->
    <section class="section section-destinations">
      <div class="container">
        <div class="section-heading split">
          <div><span class="eyebrow">Where we travel</span><h2>Places we take you.</h2><p>From easy hill stations close to Amritsar to long trips in the high mountains.</p></div>
          <a class="text-link" href="destinations.php">All destinations <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="dest-grid">
<?php $i = 0; foreach ($destinations as $destSlug => $d): ?>
          <a class="dest-card<?= $i === 0 ? ' dest-wide' : '' ?>" href="destinations.php#<?= e($destSlug) ?>">
            <img src="<?= e(img($d['image'], true)) ?>" alt="" loading="lazy">
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
            <p>Tell us where you want to go. We plan everything and pick you up from your door in Amritsar.</p>
            <div class="counter-grid">
              <div class="counter-item"><strong><span data-counter="7">0</span><i>+</i></strong><small>Vehicle choices</small></div>
              <div class="counter-item"><strong><span data-counter="24">0</span><i>/7</i></strong><small>Booking support</small></div>
              <div class="counter-item"><strong><span data-counter="6">0</span></strong><small>Regions covered</small></div>
            </div>
          </div>
          <div class="col-12 col-lg-7">
            <ol class="steps list-unstyled">
              <li class="step"><span class="step-no">01</span><div><h3>Tell us your plan</h3><p>Your dates, how many people, and where you want to go — even a rough idea is fine.</p></div><i class="fa-solid fa-comments step-icon" aria-hidden="true"></i></li>
              <li class="step"><span class="step-no">02</span><div><h3>Get your trip plan</h3><p>We send you the day-by-day plan, hotels, car and price.</p></div><i class="fa-solid fa-map-location-dot step-icon" aria-hidden="true"></i></li>
              <li class="step"><span class="step-no">03</span><div><h3>Start your trip</h3><p>The driver picks you up in Amritsar. We are one call away the whole time.</p></div><i class="fa-solid fa-car-side step-icon" aria-hidden="true"></i></li>
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
              <li><i class="fa-solid fa-headset" aria-hidden="true"></i><div><b>Help 24/7</b><span>Call or WhatsApp us at any time during your trip.</span></div></li>
            </ul>
            <div class="btn-row"><a class="btn btn-dark" href="contact.php">Start planning <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><a class="text-link" href="vehicles.php">View our vehicles <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
          </div>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
