<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'packages';
$pageTitle = 'Tour Packages';
$pageDescription = 'Private Himachal and India tour packages — Spiti, Shimla, Rajasthan, Delhi, Agra and the Golden Triangle, with stays and transport arranged.';
$pageHero = [
    'crumb'   => 'Packages',
    'eyebrow' => 'Tour packages',
    'title'   => 'Journeys across Himachal & India',
    'lead'    => 'Every trip includes pickup and drop. Car, driver and hotels are included in the plan, and you can change the days to suit you.',
    'image'   => img('spiti-key-sunset'),
    'meta'    => [['fa-route', count($packages) . ' signature routes'], ['fa-location-dot', 'Pickup and drop'], ['fa-sliders', 'Fully customisable']],
];
require __DIR__ . '/includes/header.php';
?>

    <section class="section section-packages packages-page">
      <div class="container">
        <div class="gallery-toolbar">
<?php filter_bar($packageCategories, '.packages-page .package-card', 'Filter packages'); ?>
          <a class="text-link" href="contact.php">Plan a custom trip <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="package-grid">
<?php foreach ($packages as $slug => $package) { package_card($slug, $package); } ?>
        </div>
        <p class="pricing-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Pricing depends on season, route, stay category, vehicle and number of travellers. Ask for a free quote.</p>
      </div>
    </section>

    <!-- Why book -->
    <section class="journey-band" aria-labelledby="whyPackages">
      <div class="container">
        <div class="row g-5 align-items-center">
          <div class="col-12 col-lg-5">
            <span class="eyebrow eyebrow-light">Every package includes</span>
            <h2 id="whyPackages">One plan for the whole journey.</h2>
            <p>Each package brings the route, the stays and the vehicle together — so you only have to enjoy the view.</p>
          </div>
          <div class="col-12 col-lg-7">
            <ol class="steps list-unstyled">
              <li class="step"><span class="step-no">01</span><div><h3>Private vehicle &amp; driver</h3><p>From hatchbacks to 4×4s and coaches, matched to your route and group.</p></div><i class="fa-solid fa-car-side step-icon" aria-hidden="true"></i></li>
              <li class="step"><span class="step-no">02</span><div><h3>Stays along the route</h3><p>Standard, deluxe or premium — hotels, cottages, homestays or camps.</p></div><i class="fa-solid fa-hotel step-icon" aria-hidden="true"></i></li>
              <li class="step"><span class="step-no">03</span><div><h3>Sightseeing your way</h3><p>Add a day, skip a stop or extend to a new valley — just ask.</p></div><i class="fa-solid fa-map-location-dot step-icon" aria-hidden="true"></i></li>
            </ol>
          </div>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
