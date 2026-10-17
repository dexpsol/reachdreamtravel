<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'photo-credits';
$pageTitle = 'Photo credits';
$pageDescription = 'Photographers and licenses for our destination and package cover photographs.';
$pageHero = [
    'eyebrow' => 'Behind the scenery',
    'title' => 'Photo credits',
    'lead' => 'With thanks to the photographers who share these views of our destinations.',
    'image' => img('chandratal'),
];
require __DIR__ . '/includes/header.php';
?>
    <section class="section">
      <div class="container">
        <p>These package photographs are shared under the licenses linked below. Local copies have been resized and compressed for the website; the layout may crop them to fit a card or banner. The original photographs remain available from their source pages.</p>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th scope="col">Destination</th><th scope="col">Photographer</th><th scope="col">License</th><th scope="col">Original photograph</th></tr></thead>
            <tbody>
<?php foreach (package_photo_library()['photos'] as $image => $photo): ?>
              <tr>
                <th scope="row"><img src="<?= e(img($image, true)) ?>" alt="" width="120" height="80" loading="lazy" style="object-fit:cover;border-radius:4px;margin-right:12px"> <?= e($photo['destination']) ?></th>
                <td><?= e($photo['author']) ?></td>
                <td><a href="<?= e($photo['license_url']) ?>" target="_blank" rel="noopener"><?= e($photo['license']) ?></a></td>
                <td><a href="<?= e($photo['source']) ?>" target="_blank" rel="noopener"><?= e($photo['title']) ?></a></td>
              </tr>
<?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
