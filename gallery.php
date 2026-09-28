<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'gallery';
$pageTitle = 'Gallery';
$pageDescription = 'Photos from across Himachal Pradesh — Spiti monasteries, Kinnaur valleys, Chandratal lake, Manali snow, Shimla and more.';
$pageHero = [
    'crumb'   => 'Gallery',
    'eyebrow' => 'Postcard moments',
    'title'   => 'Himachal Gallery',
    'lead'    => 'Lakes, monasteries, snowy passes and quiet villages — a look at the places we travel to every season.',
    'image'   => img('chandratal'),
];
require __DIR__ . '/includes/header.php';
?>

    <section class="section gallery-page">
      <div class="container">
        <div class="gallery-toolbar">
<?php filter_bar($galleryCategories, '.masonry-item', 'Filter photos'); ?>
          <p class="gallery-count"><b data-count><?= count($gallery) ?></b> photos</p>
        </div>

        <div class="masonry" data-gallery>
<?php foreach ($gallery as $photo): ?>
          <button type="button" class="masonry-item" data-type="<?= e($photo['cat']) ?>" data-full="<?= e(img($photo['file'])) ?>" data-caption="<?= e($photo['title'] . ' · ' . $photo['place']) ?>" aria-label="View <?= e($photo['title']) ?>">
            <img src="<?= e(img($photo['file'], true)) ?>" alt="<?= e($photo['title'] . ', ' . $photo['place']) ?>" loading="lazy">
            <span class="masonry-info"><b><?= e($photo['title']) ?></b><small><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?= e($photo['place']) ?></small></span>
            <span class="masonry-zoom" aria-hidden="true"><i class="fa-solid fa-expand"></i></span>
          </button>
<?php endforeach; ?>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
