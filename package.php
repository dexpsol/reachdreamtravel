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
if (!empty($package['deprecated_duplicate_of']) && isset($packages[$package['deprecated_duplicate_of']])) {
    header('Location: package.php?slug=' . rawurlencode($package['deprecated_duplicate_of']), true, 301);
    exit;
}

$pageKey = 'packages';
$pageTitle = $package['title'] . ' - ' . $package['duration'];
$pageDescription = $package['seo_description'] ?? $package['overview'];
$breadcrumbs = [['Packages', 'packages.php']];
$pageHero = [
    'eyebrow' => $package['label'],
    'title'   => $package['title'],
    'lead'    => $package['route'] ?? '',
    'image'   => img($package['image']),
    'meta'    => [['fa-clock', $package['duration']], ['fa-sun', $package['season']]],
];
require __DIR__ . '/includes/header.php';

$enquiry = 'Hello Reach Dream Travel, please share details for the ' . $package['title'] . ' package.';
$packageCategoryKeys = preg_split('/\s+/', trim((string) $package['cat'])) ?: [];
$related = array_filter($publicPackages, static function ($p, $key) use ($slug, $packageCategoryKeys): bool {
    if ($key === $slug) return false;
    $candidateCategories = preg_split('/\s+/', trim((string) ($p['cat'] ?? ''))) ?: [];
    return (bool) array_intersect($packageCategoryKeys, $candidateCategories);
}, ARRAY_FILTER_USE_BOTH);
if (count($related) < 3) {
    $related += array_diff_key($publicPackages, [$slug => true], $related);
}
$related = array_slice($related, 0, 3, true);
?>

    <section class="section package-detail">
      <div class="container">
        <div class="pd-layout">
          <div class="pd-main">
<?php if ($photoCredit = package_photo_credit((string) $package['image'])): ?>
            <p class="small text-muted mb-3">Photo: <a href="<?= e($photoCredit['source']) ?>" target="_blank" rel="noopener"><?= e($photoCredit['author']) ?></a> &middot; <a href="<?= e($photoCredit['license_url']) ?>" target="_blank" rel="noopener"><?= e($photoCredit['license']) ?></a>. Resized for display.</p>
<?php endif; ?>
            <div class="pd-facts">
              <div><i class="fa-solid fa-clock" aria-hidden="true"></i><small>Duration</small><b><?= e($package['duration']) ?></b></div>
              <div><i class="fa-solid fa-sun" aria-hidden="true"></i><small>Best season</small><b><?= e($package['season']) ?></b></div>
<?php if (!empty($package['start'])): ?>
              <div><i class="fa-solid fa-location-dot" aria-hidden="true"></i><small>Pickup / arrival</small><b><?= e($package['start']) ?></b></div>
<?php endif; ?>
<?php if (!empty($package['end'])): ?>
              <div><i class="fa-solid fa-flag-checkered" aria-hidden="true"></i><small>Departure / drop-off</small><b><?= e($package['end']) ?></b></div>
<?php endif; ?>
            </div>
            <div class="package-tags package-category-tags" aria-label="Package categories">
<?php foreach (package_category_labels($package) as $categoryLabel): ?>
              <span><?= e($categoryLabel) ?></span>
<?php endforeach; ?>
            </div>

            <h2 class="pd-title">Trip overview</h2>
            <p class="pd-lead"><?= e($package['overview']) ?></p>
<?php $packageDestinations = array_filter($destinations, static fn($destination) => in_array($slug, $destination['packages'] ?? [], true)); ?>
<?php if ($packageDestinations): ?>
            <nav class="package-tags mb-4" aria-label="Explore this package's destinations">
<?php foreach ($packageDestinations as $destinationSlug => $destination): ?>
              <a class="btn btn-sm btn-outline-secondary" href="destinations.php?destination=<?= rawurlencode($destinationSlug) ?>#<?= e($destinationSlug) ?>"><?= e($destination['name']) ?></a>
<?php endforeach; ?>
            </nav>
<?php endif; ?>
<?php package_facilities($package); ?>

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
<?php if (!empty($package['itinerary_note'])): ?>
            <p class="small text-muted"><?= e($package['itinerary_note']) ?></p>
<?php endif; ?>

<?php require __DIR__ . '/includes/package-inclusions.php'; ?>
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
                <a href="tel:<?= e($site['phoneLink']) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?= e(site_phone_text()) ?></a>
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
