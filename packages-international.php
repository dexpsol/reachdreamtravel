<?php
require_once __DIR__ . '/includes/data.php';

$pageKey = 'packages';
$internationalPackages = array_filter($publicPackages, static function (array $package): bool {
    $categories = preg_split('/\s+/', trim((string) ($package['cat'] ?? ''))) ?: [];
    return in_array('international', $categories, true);
});
$availableInternationalPackages = $internationalPackages;
unset($availableInternationalPackages['international-coming-soon']);
$hasInternationalPackages = count($availableInternationalPackages) > 0;
if ($hasInternationalPackages) {
    $internationalPackages = $availableInternationalPackages;
}
$pageTitle = 'International Tour Packages';
$pageDescription = $hasInternationalPackages
    ? 'Explore international tour packages with Reach Dream Travel, with flexible itineraries shaped around your dates and travel style.'
    : 'International tour packages from Reach Dream Travel are coming soon. Contact us to share the destinations you want next.';
$breadcrumbs = [['Packages', 'packages.php']];
$pageHero = [
    'crumb'   => 'International',
    'eyebrow' => 'International packages',
    'title'   => $hasInternationalPackages ? 'International tours, shaped around you' : 'International packages coming soon',
    'lead'    => $hasInternationalPackages ? 'Explore international destinations with an itinerary shaped around your dates and travel style.' : 'We are preparing international tour options. For now, share your preferred country or travel style and we will keep you posted.',
    'image'   => img('hero-himachal'),
    'meta'    => [['fa-plane-departure', $hasInternationalPackages ? count($internationalPackages) . ' international routes' : 'Coming soon'], ['fa-passport', 'Tell us your destination'], ['fa-comments', 'Plan ahead']],
];
require __DIR__ . '/includes/header.php';
?>

    <section class="section section-packages packages-page">
      <div class="container">
        <div class="gallery-toolbar">
          <nav class="package-subnav package-subnav-inline" aria-label="Package type pages">
            <a href="packages-domestic.php"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Domestic packages</a>
            <a class="active" href="packages-international.php"><i class="fa-solid fa-plane-departure" aria-hidden="true"></i> International packages</a>
          </nav>
          <a class="text-link" href="contact.php?interest=international">Ask about international trips <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="package-grid">
<?php foreach ($internationalPackages as $slug => $package) { package_card($slug, $package); } ?>
        </div>
        <p class="pricing-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> <?= $hasInternationalPackages ? 'Pricing depends on destination, season, stays and number of travellers. Ask for a personalised quote.' : 'International packages are coming soon. Tell us your dream destination and we will help you plan when options are ready.' ?></p>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
