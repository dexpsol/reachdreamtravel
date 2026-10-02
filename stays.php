<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'stays';
$pageTitle = 'Hotels & Stays';
$pageDescription = 'Explore hotel, cottage, homestay and camp options for North India trips. Reach Dream Travel can help arrange available stays to suit your route and budget.';
$pageHero = [
    'crumb'   => 'Stays',
    'eyebrow' => 'Hotels & stays',
    'title'   => 'A good place to come back to',
    'lead'    => 'Cosy rooms, mountain-view cottages, village homestays and riverside camps — we arrange stays that suit your route and budget.',
    'image'   => img('hotel-forest'),
    'meta'    => [['fa-hotel', 'Hotels & cottages'], ['fa-house-chimney', 'Homestays'], ['fa-campground', 'Camps & glamping']],
];
require __DIR__ . '/includes/header.php';

$categories = [
    ['Standard', 'Clean & simple', 'room-windows', 'Clean, comfortable rooms in well-located hotels — ideal when you are out exploring all day.', ['Clean, comfortable rooms', 'Hot water & heating in season', 'Central locations']],
    ['Deluxe', 'Bigger rooms, better views', 'room-warm', 'Bigger rooms and better views — nice to relax in after a long drive.', ['Larger rooms', 'Valley or garden views', 'In-house dining']],
    ['Premium', 'Best hotels & views', 'room-view', 'Boutique hotels and resorts with standout views, service and amenities.', ['Mountain-view rooms & suites', 'Boutique & resort properties', 'Premium dining & experiences']],
];

$types = [
    ['Hotels & resorts', 'fa-hotel', 'hotel-hill-c', 'In Shimla, Manali and other towns — from simple family hotels to resorts with a view.'],
    ['Cottages & villas', 'fa-house', 'cottage', 'Private wooden cottages among orchards and pine forests — perfect for families.'],
    ['Homestays', 'fa-house-chimney', 'cottage-b', 'Stay with local families in Kinnaur and Spiti villages for a more personal experience.'],
    ['Camps & glamping', 'fa-campground', 'tent-bell', 'Swiss tents and camps by rivers and lakes — from Chitkul to Chandratal.'],
];

$locations = ['Shimla', 'Narkanda', 'Kullu', 'Manali', 'Kasol', 'Sangla', 'Chitkul', 'Kalpa', 'Nako', 'Tabo', 'Kaza', 'Chandratal', 'Sissu'];

$faqs = [
    ['Can I book stays without a vehicle or package?', 'Yes. We can arrange stays on their own, though most travellers find it easiest to book the stay and transport together.'],
    ['Are meals included?', 'It depends on the property and plan you choose. Many hotels include breakfast, and homestays and camps often include dinner too. Your quote will say exactly what is included.'],
    ['What are stays like in Spiti and Chandratal?', 'Stays at high altitude are simpler — cosy homestays, guesthouses and camps with basic facilities. We choose clean, well-reviewed places and tell you what to expect.'],
    ['Can I choose a specific hotel?', 'Of course. If you have a hotel in mind, tell us and we will check availability. Otherwise we will suggest options that suit your budget.'],
];
?>

    <!-- Categories -->
    <section class="section stay-cats">
      <div class="container">
        <div class="section-heading center">
          <span class="eyebrow">Stay categories</span>
          <h2>Pick your level of comfort.</h2>
          <p>You can mix them — simple places in far valleys, and a nicer hotel in Shimla or Manali.</p>
        </div>
        <div class="stay-cat-grid">
<?php foreach ($categories as $i => [$name, $sub, $photo, $text, $features]): ?>
          <article class="stay-cat<?= $i === 1 ? ' is-featured' : '' ?>">
            <div class="sc-photo"><img src="<?= e(img($photo, true)) ?>" alt="<?= e($name) ?> hotel room" loading="lazy"><?php if ($i === 1): ?><span class="ribbon"><i class="fa-solid fa-heart" aria-hidden="true"></i> Most chosen</span><?php endif; ?></div>
            <div class="sc-body">
              <small><?= e($sub) ?></small>
              <h3><?= e($name) ?></h3>
              <p><?= e($text) ?></p>
              <ul class="list-unstyled">
<?php foreach ($features as $feature): ?>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> <?= e($feature) ?></li>
<?php endforeach; ?>
              </ul>
              <a class="btn <?= $i === 1 ? 'btn-gold' : 'btn-outline' ?> w-100" href="<?= e(wa_link('Hello Reach Dream Travel, I am looking for ' . strtolower($name) . ' stays for my trip.')) ?>" target="_blank" rel="noopener">Ask about <?= e(strtolower($name)) ?> stays</a>
            </div>
          </article>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Types -->
    <section class="section stay-types-section">
      <span class="bg-word" data-drift="0.2" aria-hidden="true">STAY</span>
      <div class="container">
        <div class="section-heading split">
          <div><span class="eyebrow">Ways to stay</span><h2>Hotels, cottages, homestays &amp; camps.</h2><p>Choose the kind of stay that fits the place — or mix them along the way.</p></div>
        </div>
        <div class="type-grid">
<?php foreach ($types as [$name, $icon, $photo, $text]): ?>
          <article class="type-card">
            <img src="<?= e(img($photo, true)) ?>" alt="" loading="lazy">
            <div class="type-body"><span class="type-icon"><i class="fa-solid <?= e($icon) ?>" aria-hidden="true"></i></span><h3><?= e($name) ?></h3><p><?= e($text) ?></p></div>
          </article>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Locations -->
    <section class="section stay-locations">
      <div class="container">
        <div class="stay-panel">
          <div class="stay-photo"><img src="<?= e(img('kinnaur-camps', true)) ?>" srcset="<?= e(img('kinnaur-camps', true)) ?> 900w, <?= e(img('kinnaur-camps')) ?> 2000w" sizes="(min-width: 992px) 600px, 100vw" alt="Riverside camps in Kinnaur" loading="lazy"><span class="stay-badge"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> 13+ locations</span></div>
          <div class="stay-copy">
            <span class="eyebrow">Where we arrange stays</span>
            <h2>All along your route.</h2>
            <p>From the hill towns to the last villages before the Tibet border, we know good places to stay in:</p>
            <div class="loc-chips">
<?php foreach ($locations as $location): ?>
              <span><?= e($location) ?></span>
<?php endforeach; ?>
            </div>
            <p class="stay-note">Availability and pricing depend on season, destination, room category and group size.</p>
            <div class="btn-row"><a class="btn btn-dark" href="contact.php">Enquire about stays <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><a class="text-link" href="packages.php">See packages <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="section faq-section">
      <div class="container">
        <div class="row g-5">
          <div class="col-12 col-lg-4">
            <span class="eyebrow">Good to know</span>
            <h2 class="faq-title">Stay FAQs</h2>
            <p class="faq-lead">Have a specific hotel or budget in mind? Just tell us.</p>
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
