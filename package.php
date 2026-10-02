<?php
require_once __DIR__ . '/includes/data.php';

$slug = $_GET['slug'] ?? '';
if (!isset($packages[$slug])) {
    // Unknown package: show the 404 page with the right status code.
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$package = $packages[$slug];

$pageKey = 'packages';
$pageTitle = $package['title'];
$pageDescription = $package['overview'];
$breadcrumbs = [['Packages', 'packages.php']];
$pageHero = [
    'eyebrow' => $package['label'],
    'title'   => $package['title'],
    'lead'    => $package['route'],
    'image'   => img($package['image']),
    'meta'    => [['fa-clock', $package['duration']], ['fa-signal', $package['difficulty']], ['fa-sun', $package['season']]],
];
require __DIR__ . '/includes/header.php';

$enquiry = 'Hello Reach Dream Travel, please share details for the ' . $package['title'] . ' package.';
$related = array_filter($packages, fn($p, $key) => $key !== $slug && $p['cat'] === $package['cat'], ARRAY_FILTER_USE_BOTH);
if (count($related) < 3) {
    $related += array_diff_key($packages, [$slug => true], $related);
}
$related = array_slice($related, 0, 3, true);
?>

    <section class="section package-detail">
      <div class="container">
        <div class="pd-layout">
          <div class="pd-main">
            <div class="pd-facts">
              <div><i class="fa-solid fa-clock" aria-hidden="true"></i><small>Duration</small><b><?= e($package['duration']) ?></b></div>
              <div><i class="fa-solid fa-signal" aria-hidden="true"></i><small>Difficulty</small><b><?= e($package['difficulty']) ?></b></div>
              <div><i class="fa-solid fa-sun" aria-hidden="true"></i><small>Best season</small><b><?= e($package['season']) ?></b></div>
              <div><i class="fa-solid fa-location-dot" aria-hidden="true"></i><small>Starts &amp; ends</small><b><?= e($package['start']) ?></b></div>
            </div>

            <h2 class="pd-title">Trip overview</h2>
            <p class="pd-lead"><?= e($package['overview']) ?></p>

            <h2 class="pd-title">Highlights</h2>
            <ul class="pd-highlights list-unstyled">
<?php foreach ($package['highlights'] as $highlight): ?>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i> <?= e($highlight) ?></li>
<?php endforeach; ?>
            </ul>

            <h2 class="pd-title">Day-by-day plan</h2>
            <ol class="timeline list-unstyled">
<?php foreach ($package['days'] as $i => [$dayTitle, $dayText]): ?>
              <li class="timeline-item"><span class="timeline-day">Day <?= $i + 1 ?></span><div class="timeline-card"><h3><?= e($dayTitle) ?></h3><p><?= e($dayText) ?></p></div></li>
<?php endforeach; ?>
            </ol>

            <div class="pd-included">
              <div>
                <h2 class="pd-title">Can be arranged</h2>
                <ul class="list-unstyled pd-list pd-yes">
                  <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Private vehicle with experienced driver</li>
                  <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Hotel, cottage, homestay or camp stays</li>
                  <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Sightseeing as per the trip plan</li>
                  <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Pickup and drop at your home or hotel</li>
                </ul>
              </div>
              <div>
                <h2 class="pd-title">Discussed while planning</h2>
                <ul class="list-unstyled pd-list pd-maybe">
                  <li><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Meals and entry tickets</li>
                  <li><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Activities such as rafting or paragliding</li>
                  <li><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Permits, tolls and parking</li>
                  <li><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Route changes due to weather or road conditions</li>
                </ul>
              </div>
            </div>
          </div>

          <aside class="pd-aside">
            <div class="pd-card">
              <span class="eyebrow">Plan this trip</span>
              <h2>Get a free quote</h2>
<?php package_price($package); ?>
              <p>Tell us your dates and group size — we will send you a full plan and price.</p>
              <form class="trip-form" aria-label="Enquire about <?= e($package['title']) ?>" novalidate>
                <input type="hidden" name="destination" value="<?= e($package['title']) ?>">
                <label class="field"><span>Your name</span><input type="text" name="name" autocomplete="name" placeholder="Full name" required></label>
                <label class="field"><span>Phone</span><input type="tel" name="phone" autocomplete="tel" placeholder="+91" required></label>
                <label class="field"><span>Travel date</span><input type="date" name="date"></label>
                <label class="field"><span>Travellers</span><input type="number" name="travellers" min="1" max="40" value="2" inputmode="numeric"></label>
                <label class="field"><span>Vehicle</span><select name="vehicle"><?php vehicle_select_options(); ?></select></label>
                <input class="visually-hidden" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                <button class="btn btn-gold w-100" type="submit">Send enquiry <i class="fa-brands fa-whatsapp" aria-hidden="true"></i></button>
                <p class="form-status" role="status" aria-live="polite" hidden></p>
              </form>
              <div class="pd-card-foot">
                <a href="tel:<?= e($site['phoneLink']) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?= e($site['phone']) ?></a>
                <a href="<?= e(wa_link($enquiry)) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Quick chat</a>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </section>

    <section class="section section-packages related-packages">
      <div class="container">
        <div class="section-heading split">
          <div><span class="eyebrow">You may also like</span><h2>More journeys to explore.</h2></div>
          <a class="text-link" href="packages.php">All packages <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="package-grid">
<?php foreach ($related as $relatedSlug => $relatedPackage) { package_card($relatedSlug, $relatedPackage); } ?>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
