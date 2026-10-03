<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'destinations';
$pageTitle = 'Destinations';
$pageDescription = 'Explore North India destinations including Himachal, Kashmir, Uttarakhand, Delhi, Agra and Rajasthan. Find trip ideas, highlights and the best time to visit.';
$pageHero = [
    'crumb'   => 'Destinations',
    'eyebrow' => 'Where we travel',
    'title'   => 'Places to discover across North India',
    'lead'    => 'Plan a journey through mountain valleys, lake towns, wildlife country and historic cities, with an itinerary shaped around your dates.',
    'image'   => img('taj-mahal-agra'),
];
require __DIR__ . '/includes/header.php';
?>

    <!-- Quick jump -->
    <section class="dest-jump-section">
      <div class="container">
        <nav class="dest-jump" aria-label="Jump to a destination">
<?php foreach ($destinations as $slug => $d): ?>
          <a href="#<?= e($slug) ?>"<?= empty($d['image']) ? ' class="is-text-only"' : '' ?>><?php if (!empty($d['image'])): ?><img src="<?= e(img($d['image'], true)) ?>" alt="" loading="lazy"><?php endif; ?><span><?= e($d['name']) ?></span></a>
<?php endforeach; ?>
        </nav>
      </div>
    </section>

    <section class="section dest-list-section">
      <div class="container">
        <section class="destination-regions" aria-labelledby="destinationRegionsTitle">
          <div class="section-heading"><div><span class="eyebrow">Explore by region</span><h2 id="destinationRegionsTitle">Destinations across the north.</h2></div></div>
          <div class="row g-4">
<?php foreach ($destinationGroups as $group): ?>
            <div class="col-12 col-md-6">
              <article class="destination-region-card">
                <img class="destination-region-image" src="<?= e(img($group['image'], true)) ?>" alt="<?= e($group['title']) ?>" loading="lazy">
                <div>
                  <h3><?= e($group['title']) ?></h3>
                  <p><?= e($group['description']) ?></p>
                  <p><?= e(implode(' · ', $group['items'])) ?></p>
                </div>
              </article>
            </div>
<?php endforeach; ?>
          </div>
        </section>
        <div class="dest-list">
<?php $n = 0; foreach ($destinations as $slug => $d): $n++; $hasScenicPhoto = !empty($d['image']); ?>
          <article class="dest-row<?= $n % 2 === 0 ? ' is-flipped' : '' ?><?= $hasScenicPhoto ? '' : ' is-text-only' ?>" id="<?= e($slug) ?>">
<?php if ($hasScenicPhoto): ?>
            <div class="dr-media">
              <img src="<?= e(img($d['image'], true)) ?>" srcset="<?= e(img($d['image'], true)) ?> 900w, <?= e(img($d['image'])) ?> 2000w" sizes="(min-width: 992px) 600px, 100vw" alt="<?= e($d['name'] . ' — ' . $d['tagline']) ?>" loading="lazy">
              <span class="dr-no" aria-hidden="true"><?= sprintf('%02d', $n) ?></span>
            </div>
<?php endif; ?>
            <div class="dr-body">
              <span class="eyebrow"><?= e($d['tagline']) ?></span>
              <h2><?= e($d['name']) ?></h2>
              <p><?= e($d['text']) ?></p>
              <div class="dr-facts">
                <span><i class="fa-solid fa-car-side" aria-hidden="true"></i> <?= e($d['drive']) ?></span>
<?php if (!in_array($d['altitude'] ?? '', ['Varies by route', 'Varies by zone'], true)): ?>
                <span><i class="fa-solid fa-mountain" aria-hidden="true"></i> <?= e($d['altitude']) ?></span>
<?php endif; ?>
                <span><i class="fa-solid fa-sun" aria-hidden="true"></i> Best: <?= e($d['best']) ?></span>
              </div>
              <h3>Don’t miss</h3>
              <ul class="dr-see list-unstyled">
<?php foreach ($d['see'] as $place): ?>
                <li><?= e($place) ?></li>
<?php endforeach; ?>
              </ul>
              <div class="dr-packages">
<?php foreach ($d['packages'] as $pkgSlug): ?>
                <a href="<?= e(package_url($pkgSlug)) ?>"><i class="fa-solid fa-route" aria-hidden="true"></i> <?= e($packages[$pkgSlug]['title']) ?></a>
<?php endforeach; ?>
              </div>
            </div>
          </article>
<?php endforeach; ?>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
