<?php
require_once __DIR__ . '/includes/data.php';

$pageKey = 'solo';
$pageTitle = 'Solo Trips and Activities';
$pageDescription = 'Explore solo-friendly activities and tour packages with Reach Dream Travel, including Rishikesh, Manali, Kasol, Chakrata and Dharamshala routes.';
$soloPackages = array_filter($publicPackages, static function (array $package): bool {
    $categories = preg_split('/\s+/', trim((string) ($package['cat'] ?? ''))) ?: [];
    return in_array('solo', $categories, true);
});
$pageHero = [
    'crumb'   => 'Solo Trips',
    'eyebrow' => 'Solo travel',
    'title'   => 'Solo trips with activities, routes and backup planned in.',
    'lead'    => 'Pick a solo-friendly route for riverside cafes, trekking, rafting, yoga, waterfalls, snow drives and quiet hill breaks. We arrange the vehicle, stays and flexible day plan around your comfort.',
    'image'   => img('rishikesh'),
    'meta'    => [['fa-person-walking-luggage', count($soloPackages) . ' solo packages'], ['fa-route', 'Flexible routes'], ['fa-phone-volume', 'Local planning support']],
];
require __DIR__ . '/includes/header.php';

$soloActivities = [
    ['fa-water', 'River rafting', 'Rishikesh rafting plans when the season and local operations allow.'],
    ['fa-spa', 'Yoga and wellness', 'Drop-in yoga, Ganga walks and easy retreat-style days.'],
    ['fa-mountain-sun', 'Short treks', 'Triund, forest trails and viewpoint walks planned with local conditions in mind.'],
    ['fa-campground', 'Camping add-ons', 'Kasol, Tosh and riverside camping options where available.'],
    ['fa-snowflake', 'Snow and mountain drives', 'Solang, Atal Tunnel and Sissu drives for snow or high-valley views.'],
    ['fa-mug-saucer', 'Cafe and slow travel', 'Old Manali, Kasol, Dharamkot and riverfront cafe time built into the route.'],
    ['fa-water-ladder', 'Waterfalls and caves', 'Chakrata, Tiger Falls, Budher Caves and quiet nature stops.'],
    ['fa-place-of-worship', 'Culture and temples', 'McLeod Ganj, Beatles Ashram, local temples and monastery visits.'],
];
?>

    <section class="section solo-intro" aria-labelledby="soloIntroTitle">
      <div class="container">
        <div class="solo-intro-grid">
          <div>
            <span class="eyebrow">Activities for solo travellers</span>
            <h2 id="soloIntroTitle">Go solo, without planning everything alone.</h2>
            <p>These plans are made for one traveller or a small independent group. Choose the activity style you want, then ask us to tune the pace, stay type, vehicle and pickup point.</p>
          </div>
          <div class="solo-planning-card">
            <h3>Solo plan can include</h3>
            <ul class="list-unstyled">
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Private cab or shared-style route planning</li>
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Hotel, hostel, homestay or camp suggestions</li>
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activity timing based on season and weather</li>
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Pickup and drop from home, hotel, station or airport</li>
            </ul>
            <a class="btn btn-gold" href="contact.php?travelling=Solo">Plan my solo trip <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
          </div>
        </div>
      </div>
    </section>

    <section class="section solo-activities" aria-labelledby="soloActivitiesTitle">
      <div class="container">
        <div class="section-heading split">
          <div><span class="eyebrow">Solo activities</span><h2 id="soloActivitiesTitle">Choose the kind of day you want.</h2><p>Mix active days with slower cafe time, sightseeing or rest days.</p></div>
          <a class="text-link" href="contact.php?interest=solo">Ask for suggestions <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="solo-activity-grid">
<?php foreach ($soloActivities as [$icon, $title, $text]): ?>
          <article class="solo-activity-card">
            <i class="fa-solid <?= e($icon) ?>" aria-hidden="true"></i>
            <h3><?= e($title) ?></h3>
            <p><?= e($text) ?></p>
          </article>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section section-packages solo-packages" aria-labelledby="soloPackagesTitle">
      <div class="container">
        <div class="section-heading split">
          <div><span class="eyebrow">Solo packages</span><h2 id="soloPackagesTitle">All solo-friendly packages.</h2><p>Start with one of these routes, then change the dates, activities, stays or pickup point.</p></div>
          <a class="text-link" href="packages.php">Browse all packages <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
<?php if (!$soloPackages): ?>
        <p class="pricing-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Solo packages will appear here once they are added in the admin.</p>
<?php else: ?>
        <div class="package-grid">
<?php foreach ($soloPackages as $slug => $package) { package_card($slug, $package); } ?>
        </div>
<?php endif; ?>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
