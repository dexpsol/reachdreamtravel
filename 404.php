<?php
require_once __DIR__ . '/includes/data.php';
// Page not found. Also used by package.php for unknown packages and by .htaccess (ErrorDocument).
if (!headers_sent()) {
    http_response_code(404);
}
$pageKey = '404';
$pageTitle = 'Page not found';
$pageDescription = 'The page you were looking for could not be found.';
$useBaseHref = true; // Keeps links and assets working at any URL depth.
require __DIR__ . '/includes/header.php';
?>

    <section class="notfound">
      <div class="notfound-bg" style="background-image:url('<?= e(img('snow-peaks')) ?>')" aria-hidden="true"></div>
      <div class="container notfound-content">
        <span class="notfound-code" aria-hidden="true">404</span>
        <h1>Looks like this trail doesn’t exist.</h1>
        <p>The page you were looking for may have moved. Let’s get you back on the road.</p>
        <div class="btn-row">
          <a class="btn btn-gold" href="index.php"><i class="fa-solid fa-house" aria-hidden="true"></i> Back to home</a>
          <a class="btn btn-glass" href="packages.php">Browse packages</a>
        </div>
        <nav class="notfound-links" aria-label="Popular pages">
          <a href="destinations.php">Destinations</a><a href="vehicles.php">Vehicles</a><a href="gallery.php">Gallery</a><a href="contact.php">Contact</a>
        </nav>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
