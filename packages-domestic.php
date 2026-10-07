<?php
require_once __DIR__ . '/includes/data.php';

$pageKey = 'packages';
$domesticPackages = array_filter($publicPackages, static function (array $package): bool {
    $categories = preg_split('/\s+/', trim((string) ($package['cat'] ?? ''))) ?: [];
    return in_array('domestic', $categories, true) && !in_array('international', $categories, true);
});
$pageTitle = 'Domestic Tour Packages';
$pageDescription = 'Explore domestic tour packages across Himachal, Kashmir, Uttarakhand, Rajasthan, Delhi and Agra with private transport and flexible routes.';
$breadcrumbs = [['Packages', 'packages.php']];
$pageHero = [
    'crumb'   => 'Domestic',
    'eyebrow' => 'Domestic packages',
    'title'   => 'Domestic tours across North India',
    'lead'    => 'All current packages are available here as domestic tours, with routes, vehicles and stays shaped around your dates.',
    'image'   => img('spiti-key-sunset'),
    'meta'    => [['fa-route', count($domesticPackages) . ' domestic routes'], ['fa-car-side', 'Pickup and drop'], ['fa-sliders', 'Fully customisable']],
];
require __DIR__ . '/includes/header.php';
?>

    <section class="section section-packages packages-page">
      <div class="container">
        <div class="gallery-toolbar">
          <nav class="package-subnav package-subnav-inline" aria-label="Package type pages">
            <a class="active" href="packages-domestic.php"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Domestic packages</a>
            <a href="packages-international.php"><i class="fa-solid fa-plane-departure" aria-hidden="true"></i> International packages</a>
          </nav>
          <a class="text-link" href="packages.php">All packages <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="package-grid">
<?php foreach ($domesticPackages as $slug => $package) { package_card($slug, $package); } ?>
        </div>
        <p class="pricing-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Pricing depends on season, route, stay category, vehicle and number of travellers. Ask for a free quote.</p>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
