<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'vehicles';
$pageTitle = 'Our Vehicles';
$pageDescription = 'Hatchbacks, MUVs, 4×4s and group coaches with experienced hill drivers for your Himachal trip — Swift, Ertiga, Innova Crysta, Jimny, Tempo Traveller and Force Urbania.';
$pageHero = [
    'crumb'   => 'Vehicles',
    'eyebrow' => 'Our fleet',
    'title'   => 'Vehicles for every road',
    'lead'    => 'Every car comes with an experienced hill driver. We pick you up and bring you back.',
    'image'   => img('road-van'),
    'meta'    => [['fa-car-side', '6 vehicle types'], ['fa-id-card', 'Driver included'], ['fa-headset', '24/7 support']],
];
require __DIR__ . '/includes/header.php';

$routeGuide = [
    ['Shimla, Kullu & Manali', 'Good paved highways and town roads. Any vehicle works — pick by group size and comfort.', 'manali-valley', ['Maruti Swift', 'Maruti Ertiga', 'Tempo Traveller']],
    ['Kinnaur & the Spiti circuit', 'Long days on narrow mountain roads with some rough stretches. Comfort and ground clearance matter.', 'spiti-key', ['Toyota Innova Crysta', 'Suzuki Jimny 4×4']],
    ['Chandratal & high passes', 'Unpaved tracks, stream crossings and altitude. A proper 4×4 is strongly recommended.', 'chandratal', ['Suzuki Jimny 4×4']],
];

$includes = [
    ['fa-id-card', 'Experienced hill driver', 'Local drivers who know mountain roads, weather and the best stops.'],
    ['fa-spray-can-sparkles', 'Clean, checked vehicle', 'Cleaned and inspected before every trip.'],
    ['fa-location-crosshairs', 'Doorstep pickup', 'From your home, hotel, airport or railway station.'],
    ['fa-camera', 'Photo & chai stops', 'Stop wherever you like for photos or tea.'],
    ['fa-file-invoice', 'Clear, upfront quote', 'Tolls, parking, permits and driver allowance listed before you book.'],
    ['fa-headset', '24/7 support', 'A call or WhatsApp away for the whole journey.'],
];

$faqs = [
    ['Does every vehicle come with a driver?', 'Yes. All our vehicles are chauffeur-driven by experienced drivers who know Himachal’s mountain roads well.'],
    ['Which vehicle is best for Spiti Valley?', 'For the full Spiti circuit we recommend the Toyota Innova Crysta for comfort or the Suzuki Jimny 4×4 for rough, unpaved sections such as Chandratal. We will suggest the best option for your dates and group.'],
    ['What is included in the vehicle price?', 'Your quote clearly lists what is included — typically the vehicle, driver and fuel for the planned route. Tolls, parking, permits and driver allowance are confirmed in your quote before you book.'],
    ['Can I book only a car, without a package?', 'Yes. You can book just the car and driver — for a Himachal trip, a day trip, or an airport or station transfer.'],
    ['Can we change the plan during the trip?', 'Small changes to stops and timings are usually fine. For route changes that add distance or days, just call us and we will adjust the plan.'],
];

$slugify = fn(string $name): string => trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)), '-');
?>

    <!-- Highlights -->
    <section class="highlights">
      <div class="container">
        <div class="feature-panel">
          <div class="feature"><span class="feature-icon fi-amber"><i class="fa-solid fa-id-card" aria-hidden="true"></i></span><div><b>Experienced drivers</b><small>Years of mountain driving</small></div></div>
          <div class="feature"><span class="feature-icon fi-green"><i class="fa-solid fa-spray-can-sparkles" aria-hidden="true"></i></span><div><b>Clean &amp; well-kept</b><small>Checked before every trip</small></div></div>
          <div class="feature"><span class="feature-icon fi-blue"><i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i></span><div><b>Doorstep pickup</b><small>Home, hotel, airport or station</small></div></div>
          <div class="feature"><span class="feature-icon fi-rose"><i class="fa-solid fa-headset" aria-hidden="true"></i></span><div><b>24/7 support</b><small>We are a call away</small></div></div>
        </div>
      </div>
    </section>

    <!-- Showroom -->
    <section class="section section-showroom" id="fleet">
      <span class="bg-word" data-drift="0.2" aria-hidden="true">THE FLEET</span>
      <div class="container" data-showroom>
        <div class="section-heading split">
          <div><span class="eyebrow">Choose your ride</span><h2>Six ways to see the mountains.</h2><p>Pick a vehicle to see the details, or filter by how many of you are travelling.</p></div>
        </div>
<?php filter_bar($groupSizes, '.showroom-tab', 'Filter vehicles by group size'); ?>
        <div class="showroom">
          <div class="showroom-tabs" role="tablist" aria-label="Vehicles" aria-orientation="vertical">
<?php foreach ($vehicles as $i => $v): $id = $slugify($v['name']); ?>
            <button type="button" class="showroom-tab" role="tab" id="tab-<?= e($id) ?>" aria-controls="<?= e($id) ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>" data-type="<?= e($v['group']) ?>">
              <img src="<?= e($v['image']) ?>" alt="" loading="lazy">
              <span class="st-text"><b><?= e($v['name']) ?></b><small><?= e($v['tag']) ?> · <?= e($v['seats']) ?> seats</small></span>
              <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
<?php endforeach; ?>
          </div>
          <div class="showroom-stage">
<?php foreach ($vehicles as $i => $v): $id = $slugify($v['name']); [$extraIcon, $extraLabel] = $v['extra']; ?>
            <article class="showroom-panel" role="tabpanel" id="<?= e($id) ?>" aria-labelledby="tab-<?= e($id) ?>"<?= $i === 0 ? '' : ' hidden' ?>>
              <div class="sp-photo">
                <img src="<?= e($v['image']) ?>" alt="<?= e($v['alt']) ?>" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>" width="1400" height="1050">
                <span class="sp-tag"><?= e($v['tag']) ?></span>
                <span class="sp-index" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?><small>/<?= sprintf('%02d', count($vehicles)) ?></small></span>
              </div>
              <div class="sp-body">
                <div class="sp-head">
                  <h3><?= e($v['name']) ?></h3>
                  <span class="sp-best"><i class="fa-solid fa-star" aria-hidden="true"></i> Best for <?= e(lcfirst($v['best'])) ?></span>
                </div>
                <p class="sp-text"><?= e($v['details']) ?></p>
                <dl class="sp-specs">
                  <div><dt><i class="fa-solid fa-user-group" aria-hidden="true"></i> Seats</dt><dd><?= e($v['seats']) ?></dd></div>
                  <div><dt><i class="fa-solid fa-suitcase-rolling" aria-hidden="true"></i> Luggage</dt><dd><?= e($v['bags']) ?></dd></div>
                  <div><dt><i class="fa-solid <?= e($extraIcon) ?>" aria-hidden="true"></i> Comfort</dt><dd><?= e($extraLabel) ?></dd></div>
                  <div><dt><i class="fa-solid fa-road" aria-hidden="true"></i> Terrain</dt><dd><?= e($v['terrain']) ?></dd></div>
                </dl>
                <div class="sp-columns">
                  <ul class="sp-features list-unstyled">
<?php foreach ($v['features'] as $feature): ?>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> <?= e($feature) ?></li>
<?php endforeach; ?>
                  </ul>
                  <div class="sp-routes"><span>Ideal for</span><?php foreach ($v['routes'] as $route): ?><em><?= e($route) ?></em><?php endforeach; ?></div>
                </div>
                <div class="btn-row">
                  <a class="btn btn-gold" data-vehicle="<?= e($v['name']) ?>" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Enquire now</a>
                  <a class="btn btn-outline" href="tel:<?= e($site['phoneLink']) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call to book</a>
                </div>
              </div>
            </article>
<?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- Route guide -->
    <section class="section route-guide">
      <div class="container">
        <div class="section-heading center">
          <span class="eyebrow">Route guide</span>
          <h2>The right vehicle for the road.</h2>
          <p>Himachal’s roads change a lot between the valleys and the high passes. Here is what we usually recommend.</p>
        </div>
        <div class="route-grid">
<?php foreach ($routeGuide as $i => [$title, $text, $photo, $picks]): ?>
          <article class="route-card">
            <div class="route-photo"><img src="<?= e(img($photo, true)) ?>" alt="" loading="lazy"><span class="route-level"><?= ['Easy roads', 'Mountain roads', 'Off-road'][$i] ?></span></div>
            <div class="route-body">
              <h3><?= e($title) ?></h3>
              <p><?= e($text) ?></p>
              <div class="route-picks"><span>We recommend</span><?php foreach ($picks as $pick): ?><a href="#tab-<?= e($slugify($pick)) ?>" data-pick="tab-<?= e($slugify($pick)) ?>"><?= e($pick) ?></a><?php endforeach; ?></div>
            </div>
          </article>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Comparison -->
    <!-- <section class="section compare-section">
      <div class="container">
        <div class="section-heading center">
          <span class="eyebrow">Side by side</span>
          <h2>Compare at a glance.</h2>
          <p>Seats, luggage and terrain for every vehicle in one table.</p>
        </div>
        <div class="table-wrap">
          <table class="compare-table">
            <thead><tr><th scope="col">Vehicle</th><th scope="col">Type</th><th scope="col">Seats</th><th scope="col">Luggage</th><th scope="col">Terrain</th><th scope="col">Best for</th></tr></thead>
            <tbody>
<?php foreach ($vehicles as $v): ?>
              <tr><th scope="row"><span class="ct-name"><img src="<?= e($v['image']) ?>" alt="" loading="lazy"><?= e($v['name']) ?></span></th><td><span class="pill"><?= e($v['tag']) ?></span></td><td><?= e($v['seats']) ?></td><td><?= e($v['bags']) ?></td><td><?= e($v['terrain']) ?></td><td><?= e($v['best']) ?></td></tr>
<?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="table-hint"><i class="fa-solid fa-arrows-left-right" aria-hidden="true"></i> Swipe to see the full table</p>
      </div>
    </section> -->

    <!-- Included -->
    <section class="section includes-section">
      <div class="container">
        <div class="section-heading center">
          <span class="eyebrow eyebrow-light">With every booking</span>
          <h2>What every ride includes.</h2>
        </div>
        <div class="includes-grid">
<?php foreach ($includes as [$icon, $title, $text]): ?>
          <div class="include-item"><span><i class="fa-solid <?= e($icon) ?>" aria-hidden="true"></i></span><div><h3><?= e($title) ?></h3><p><?= e($text) ?></p></div></div>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="section faq-section">
      <div class="container">
        <div class="row g-5">
          <div class="col-12 col-lg-4">
            <span class="eyebrow">Good to know</span>
            <h2 class="faq-title">Vehicle FAQs</h2>
            <p class="faq-lead">Anything else on your mind? Call or message us — we are happy to help.</p>
            <a class="btn btn-dark" href="contact.php">Ask a question <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
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
