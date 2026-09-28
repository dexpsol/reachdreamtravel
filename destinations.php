<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'destinations';
$pageTitle = 'Destinations';
$pageDescription = 'Explore Himachal destinations — Shimla, Manali & Solang, Kullu & Kasol, Kinnaur, Spiti Valley and Chandratal — with the best time to visit and what to see.';
$pageHero = [
    'crumb'   => 'Destinations',
    'eyebrow' => 'Where we travel',
    'title'   => 'Destinations in Himachal',
    'lead'    => 'Six places in Himachal we take you to from Amritsar — with road time, best season and what to see.',
    'image'   => img('spiti-key-snow'),
];
require __DIR__ . '/includes/header.php';
?>

    <!-- Quick jump -->
    <section class="dest-jump-section">
      <div class="container">
        <nav class="dest-jump" aria-label="Jump to a destination">
<?php foreach ($destinations as $slug => $d): ?>
          <a href="#<?= e($slug) ?>"><img src="<?= e(img($d['image'], true)) ?>" alt="" loading="lazy"><span><?= e($d['name']) ?></span></a>
<?php endforeach; ?>
        </nav>
      </div>
    </section>

    <section class="section dest-list-section">
      <div class="container">
        <div class="dest-list">
<?php $n = 0; foreach ($destinations as $slug => $d): $n++; ?>
          <article class="dest-row<?= $n % 2 === 0 ? ' is-flipped' : '' ?>" id="<?= e($slug) ?>">
            <div class="dr-media">
              <img src="<?= e(img($d['image'], true)) ?>" srcset="<?= e(img($d['image'], true)) ?> 900w, <?= e(img($d['image'])) ?> 2000w" sizes="(min-width: 992px) 600px, 100vw" alt="<?= e($d['name']) ?>, Himachal Pradesh" loading="lazy">
              <span class="dr-no" aria-hidden="true"><?= sprintf('%02d', $n) ?></span>
            </div>
            <div class="dr-body">
              <span class="eyebrow"><?= e($d['tagline']) ?></span>
              <h2><?= e($d['name']) ?></h2>
              <p><?= e($d['text']) ?></p>
              <div class="dr-facts">
                <span><i class="fa-solid fa-car-side" aria-hidden="true"></i> <?= e($d['drive']) ?></span>
                <span><i class="fa-solid fa-mountain" aria-hidden="true"></i> <?= e($d['altitude']) ?></span>
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
