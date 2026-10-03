<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'home';
$pageTitle = 'Private North India Tours & Road Trips';
$pageDescription = 'Plan a private North India tour with Reach Dream Travel. Explore Himachal, Kashmir, Uttarakhand, Rajasthan and the Golden Triangle with a driver, stays and a flexible itinerary.';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/components.php';
?>
    <!-- Hero -->
    <section class="hero" id="home" aria-labelledby="heroTitle">
      <div class="hero-image" aria-hidden="true"></div>
      <div class="hero-overlay" aria-hidden="true"></div>
      <div class="container hero-content">
        <div class="row align-items-center g-5">
          <div class="col-12 col-lg-7 hero-copy">
            <p class="hero-kicker">Private road trips, made personal</p>
            <h1 id="heroTitle">Private tours across North India.</h1>
            <p class="hero-lead">Explore Himalayan valleys, Kashmir, Uttarakhand, Rajasthan and the Golden Triangle. We coordinate your route, vehicle, driver and stays around your dates.</p>
            <div class="hero-actions">
              <a class="btn btn-gold" href="packages.php">Explore packages <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
              <a class="btn btn-glass" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Plan on WhatsApp</a>
            </div>
            <ul class="hero-points list-unstyled">
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Private cars and group vehicles</li>
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Stays arranged along your route</li>
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Pickup and drop by arrangement</li>
            </ul>
          </div>
          <div class="col-12 col-lg-5 hero-form-col">
            <div class="hero-stage">
            <form class="trip-form hero-form" aria-labelledby="heroFormTitle" novalidate>
              <div class="form-head">
                <span class="form-icon"><i class="fa-solid fa-route" aria-hidden="true"></i></span>
                <div><h2 id="heroFormTitle">Plan your trip</h2><p>Free quote. No payment needed.</p></div>
              </div>
              <div class="field-row">
                <label class="field"><span>Your name</span><input type="text" name="name" autocomplete="name" placeholder="Full name" required></label>
                <label class="field"><span>Phone</span><input type="tel" name="phone" autocomplete="tel" placeholder="+91" required></label>
              </div>
              <label class="field"><span>Where to?</span>
                <select name="destination"><?php trip_select_options(); ?></select>
              </label>
              <div class="field-row">
                <label class="field"><span>Travel date</span><input type="date" name="date"></label>
                <label class="field"><span>Travellers</span><input type="number" name="travellers" min="1" max="40" value="2" inputmode="numeric"></label>
              </div>
              <label class="field"><span>Vehicle</span>
                <select name="vehicle"><?php vehicle_select_options(); ?></select>
              </label>
              <input class="visually-hidden" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
              <button class="btn btn-gold w-100" type="submit">Get my free quote <i class="fa-brands fa-whatsapp" aria-hidden="true"></i></button>
              <p class="form-status" role="status" aria-live="polite" hidden></p>
              <small class="form-note"><i class="fa-solid fa-lock" aria-hidden="true"></i> No payment needed to enquire</small>
            </form>
              <!-- <div class="float-chip chip-a" aria-hidden="true"><i class="fa-solid fa-mountain-sun"></i><span><b>Kaza, Spiti</b><small>3,800 m above sea level</small></span></div> -->
              <!-- <div class="float-chip chip-b" aria-hidden="true"><i class="fa-solid fa-road"></i><span><b>Atal Tunnel</b><small>9.02 km through the Pir Panjal</small></span></div> -->
            </div>
          </div>
        </div>
      </div>
      <a class="scroll-cue" href="#features" aria-label="Scroll to content"><span></span></a>
    </section>

    <!-- Features -->
    <section class="features" id="features" aria-label="Travel with confidence">
      <div class="container">
        <div class="feature-panel">
          <div class="feature"><span class="feature-icon fi-amber"><i class="fa-solid fa-hotel" aria-hidden="true"></i></span><div><b>Hotels for every budget</b><small>Standard, deluxe &amp; premium</small></div></div>
          <div class="feature"><span class="feature-icon fi-green"><i class="fa-solid fa-car-side" aria-hidden="true"></i></span><div><b>Doorstep pickup</b><small>Home, hotel, airport or station</small></div></div>
          <div class="feature"><span class="feature-icon fi-blue"><i class="fa-solid fa-id-card" aria-hidden="true"></i></span><div><b>Experienced drivers</b><small>Matched to your route</small></div></div>
          <div class="feature"><span class="feature-icon fi-rose"><i class="fa-solid fa-comments" aria-hidden="true"></i></span><div><b>Talk to our team</b><small>Call or WhatsApp to plan</small></div></div>
        </div>
      </div>
    </section>

    <!-- Road times -->
    <!-- <section class="section drive-times">
      <div class="container">
        <div class="section-heading split">
          <div><span class="eyebrow">Road times</span><h2>How far are the mountains?</h2><p>Road time from our base by car. Times are approximate and depend on traffic, weather and stops.</p></div>
          <a class="text-link" href="destinations.php">All destinations <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="drive-grid">
<?php foreach ($driveTimes as [$driveTo, $driveTime, $driveSlug]): ?>
          <a class="drive-card" href="destinations.php#<?= e($driveSlug) ?>">
            <img src="<?= e(img($destinations[$driveSlug]['image'], true)) ?>" alt="" loading="lazy">
            <span class="drive-body"><small>Drive to <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></small><b><?= e($driveTo) ?></b><em><i class="fa-solid fa-car-side" aria-hidden="true"></i> <?= e($driveTime) ?></em></span>
          </a>
<?php endforeach; ?>
        </div>
      </div>
    </section> -->

    <!-- Destination ribbon -->
    <div class="ribbon-band" aria-hidden="true">
      <div class="ribbon-track">
        <span>Shimla</span><i class="fa-solid fa-route"></i><span>Manali</span><i class="fa-solid fa-route"></i><span>Srinagar</span><i class="fa-solid fa-route"></i><span>Gulmarg</span><i class="fa-solid fa-route"></i><span>Nainital</span><i class="fa-solid fa-route"></i><span>Rishikesh</span><i class="fa-solid fa-route"></i><span>Jaipur</span><i class="fa-solid fa-route"></i><span>Agra</span><i class="fa-solid fa-route"></i>
        <span>Shimla</span><i class="fa-solid fa-route"></i><span>Manali</span><i class="fa-solid fa-route"></i><span>Srinagar</span><i class="fa-solid fa-route"></i><span>Gulmarg</span><i class="fa-solid fa-route"></i><span>Nainital</span><i class="fa-solid fa-route"></i><span>Rishikesh</span><i class="fa-solid fa-route"></i><span>Jaipur</span><i class="fa-solid fa-route"></i><span>Agra</span><i class="fa-solid fa-route"></i>
      </div>
    </div>

    <!-- Packages -->
    <section class="section section-packages" id="packages">
      <span class="bg-word" data-drift="0.25" aria-hidden="true">NORTH INDIA</span>
      <div class="container">
        <div class="section-heading split">
          <div><span class="eyebrow">Sample itineraries</span><h2>Choose a starting point.</h2><p>Browse mountain routes, heritage circuits and short city breaks. Each itinerary can be adjusted around your dates.</p></div>
          <a class="text-link" href="contact.php">Plan a custom trip <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="row g-4">
<?php foreach (['shimla-spiti-kinnaur', 'golden-triangle-delhi-agra-jaipur', 'rajasthan-heritage-trail'] as $slug): $package = $packages[$slug]; ?>
          <div class="col-12 col-md-6 col-lg-4">
<?php package_card($slug, $package); ?>
          </div>
<?php endforeach; ?>
        </div>
        <div class="section-more"><a class="btn btn-dark" href="packages.php">View all <?= count($publicPackages) ?> packages <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
        <p class="pricing-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Price depends on the season, hotel type, car and number of people. Ask us for a free quote.</p>
      </div>
    </section>

    <!-- India signature escapes -->
    <section class="section section-india-escapes">
      <div class="container">
        <div class="section-heading split">
          <div>
            <span class="eyebrow">Explore North India</span>
            <h2>North India, by road.</h2>
            <p>From the Golden Triangle to Rajasthan’s heritage cities, start with a sample itinerary and tailor it to your dates.</p>
          </div>
          <a class="text-link" href="packages.php">Browse all packages <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>

        <div class="india-journeys-grid">
          <article class="india-journey-card">
            <div class="india-journey-body">
              <span class="india-route-label">North India</span>
              <h3>Golden Triangle</h3>
              <p>Delhi • Agra • Jaipur</p>
              <ul>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Taj Mahal sunrise</li>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Heritage city stay</li>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Easy 4-day route</li>
              </ul>
              <div class="india-journey-foot">
                <span>4D · 3N</span>
                <a href="<?= e(package_url('golden-triangle-delhi-agra-jaipur')) ?>">View plan</a>
              </div>
            </div>
          </article>

          <article class="india-journey-card">
            <div class="india-journey-body">
              <span class="india-route-label">Royal Rajasthan</span>
              <h3>Heritage Trail</h3>
              <p>Jaipur • Jodhpur • Jaisalmer • Udaipur</p>
              <ul>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Forts & palaces</li>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Desert nights</li>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Local food & culture</li>
              </ul>
              <div class="india-journey-foot">
                <span>6D · 5N</span>
                <a href="<?= e(package_url('rajasthan-heritage-trail')) ?>">View plan</a>
              </div>
            </div>
          </article>

          <article class="india-journey-card">
            <div class="india-journey-body">
              <span class="india-route-label">Quick getaway</span>
              <h3>Delhi & Agra</h3>
              <p>Delhi • Agra</p>
              <ul>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Iconic city highlights</li>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Smart short itinerary</li>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Great for couples & families</li>
              </ul>
              <div class="india-journey-foot">
                <span>3D · 2N</span>
                <a href="<?= e(package_url('delhi-agra-weekend')) ?>">View plan</a>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- Fleet -->
    <section class="section section-fleet" id="fleet">
      <span class="bg-word bg-word-right" data-drift="-0.25" aria-hidden="true">ON THE ROAD</span>
      <div class="container">
        <div class="section-heading center">
          <span class="eyebrow">Our vehicles</span>
          <h2>Choose your car.</h2>
          <p>Clean cars with experienced drivers. Pick one that fits your group.</p>
        </div>
        <?php filter_bar(['all' => 'All vehicles', 'car' => 'Cars', 'suv' => 'SUVs & MUVs', 'group' => 'Group travel'], '.fleet-grid .vehicle-card', 'Filter vehicles'); ?>
        <div class="fleet-grid">
<?php foreach ($vehicles as $vehicle) { vehicle_card($vehicle); } ?>
        </div>
        <div class="section-more"><a class="btn btn-dark" href="vehicles.php">Compare all vehicles <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
      </div>
    </section>

    <!-- Numbers + how it works -->
    <section class="journey-band" aria-labelledby="journeyTitle">
      <div class="container">
        <div class="row g-5 align-items-center">
          <div class="col-12 col-lg-5">
            <span class="eyebrow eyebrow-light">Made for your journey</span>
            <h2 id="journeyTitle">Booking is easy.</h2>
            <p>Share your dates, group size and the places you have in mind. We’ll help shape the route and coordinate the arrangements you need.</p>
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
              <li class="step"><span class="step-no">03</span><div><h3>Start your trip</h3><p>Meet your driver at the agreed pickup point, with our team available if you need help along the way.</p></div><i class="fa-solid fa-car-side step-icon" aria-hidden="true"></i></li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- About -->
    <section class="section about-section" id="about">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-12 col-lg-6 about-visual">
            <div class="about-collage">
              <img class="about-main" src="<?= e(img('kinnaur-sangla', true)) ?>" alt="Sangla valley in Kinnaur" loading="lazy">
              <img class="about-sub" data-depth="-0.12" src="<?= e(img('hidimba', true)) ?>" alt="Hidimba Devi Temple in Manali" loading="lazy">
              <div class="about-stamp" data-depth="0.18"><b>RD</b><small>YOUR JOURNEY<br>OUR PASSION</small></div>
              <div class="about-chip" data-depth="0.08"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><b>Trips across North India</b><small>Pickup by arrangement</small></span></div>
            </div>
          </div>
          <div class="col-12 col-lg-6 about-copy">
            <span class="eyebrow">About Reach Dream Travel</span>
            <h2>One plan for the road ahead.</h2>
            <p>Reach Dream Travel plans private holidays across North India, from Himachal and Kashmir to Uttarakhand, Rajasthan and the Golden Triangle.</p>
            <p>Share your dates, group size and preferred pace. We’ll help put together the route, vehicle and stays before you decide.</p>
            <ul class="about-list list-unstyled">
              <li><i class="fa-solid fa-route" aria-hidden="true"></i> Pickup and drop arranged around your route</li>
              <li><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Vehicle, stays and sightseeing planned together</li>
              <li><i class="fa-solid fa-binoculars" aria-hidden="true"></i> Itinerary shaped around your dates</li>
              <li><i class="fa-solid fa-user-shield" aria-hidden="true"></i> Driver and vehicle matched to the journey</li>
            </ul>
            <div class="btn-row"><a class="btn btn-dark" href="contact.php">Let us plan your trip <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><a class="text-link" href="about.php">More about us <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stays -->
    <section class="section stay-section" id="stays">
      <div class="container">
        <div class="stay-panel">
          <div class="stay-photo"><img src="assets/images/stays/hotel.jpg" alt="Mountain stay with warm lights and a cosy atmosphere" loading="lazy"><span class="stay-badge"><i class="fa-solid fa-star" aria-hidden="true"></i> Handpicked stays</span></div>
          <div class="stay-copy">
            <span class="eyebrow">Hotel &amp; stay</span>
            <h2>Good hotels for every budget.</h2>
            <p>We book clean, comfortable hotels on your route. Choose the level you like.</p>
            <div class="stay-types">
              <div><i class="fa-solid fa-bed" aria-hidden="true"></i><b>Standard</b><small>Clean &amp; simple</small></div>
              <div><i class="fa-solid fa-mug-hot" aria-hidden="true"></i><b>Deluxe</b><small>Bigger rooms, better views</small></div>
              <div><i class="fa-solid fa-mountain-sun" aria-hidden="true"></i><b>Premium</b><small>Best hotels &amp; resorts</small></div>
            </div>
            <p class="stay-note">Price and availability depend on the season and hotel type.</p>
            <a class="text-link" href="contact.php">Add a stay to your trip <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
          </div>
        </div>
      </div>
    </section>

    <!-- Gallery -->
    <section class="section section-gallery" id="gallery">
      <div class="container">
        <div class="section-heading split">
          <div><span class="eyebrow">Postcard moments</span><h2>See where you can go.</h2><p>Lakes, snow, monasteries and green valleys — all a road trip away.</p></div>
          <a class="text-link" href="gallery.php">View full gallery <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="gallery-grid" data-gallery aria-label="Himachal travel gallery">
<?php foreach (array_slice($gallery, 0, 5) as $photo): ?>
          <button type="button" data-full="<?= e(img($photo['file'])) ?>" data-caption="<?= e($photo['title'] . ' · ' . $photo['place']) ?>" aria-label="View <?= e($photo['title']) ?>"><img src="<?= e(img($photo['file'], true)) ?>" alt="<?= e($photo['title'] . ', ' . $photo['place']) ?>" loading="lazy"><span><?= e($photo['title']) ?></span></button>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Traveller note + CTA -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-card">
          <div class="cta-quote">
            <span class="eyebrow eyebrow-light">A clear first step</span>
            <p>Share your dates, group size and the places you have in mind. We’ll come back with a route and options to discuss.</p>
            <small>No obligation to book</small>
          </div>
          <div class="cta-copy">
            <span class="eyebrow eyebrow-light">Start with your travel dates</span>
            <h2>Let’s plan the route.</h2>
            <div class="cta-actions">
              <a class="btn btn-gold" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">Book your trip <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
              <a class="btn btn-glass" href="tel:<?= e($site['phoneLink']) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call us</a>
            </div>
          </div>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
