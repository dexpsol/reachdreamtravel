<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'packages';
$pageTitle = 'North India Tour Packages';
$pageDescription = 'Plan private North India tours across Himachal, Kashmir, Uttarakhand, Rajasthan, Delhi and Agra, with flexible routes, transport and stays.';
$pageHero = [
    'crumb'   => 'Packages',
    'eyebrow' => 'Tour packages',
    'title'   => 'North India tours, shaped around you',
    'lead'    => 'Explore mountain, heritage and wildlife routes with an itinerary shaped around your dates. Ask us to arrange a vehicle, driver and stays for your trip.',
    'image'   => img('spiti-key-sunset'),
    'meta'    => [['fa-route', count($publicPackages) . ' signature routes'], ...(PACKAGE_ROUTE_FIELDS_VISIBLE ? [['fa-location-dot', 'Pickup and drop']] : []), ['fa-sliders', 'Fully customisable']],
];
require __DIR__ . '/includes/header.php';
?>

    <section class="section section-packages packages-page">
      <div class="container">
<?php require __DIR__ . '/includes/package-discovery.php'; ?>
        <div class="package-grid">
<?php foreach ($publicPackages as $slug => $package) { package_card($slug, $package); } ?>
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
