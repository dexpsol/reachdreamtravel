<?php
/**
 * Common header: <head>, site navigation and (for inner pages) the page banner with breadcrumb.
 *
 * Set before including:
 *   $pageKey         Nav key of the current page ('home', 'packages', 'destinations', 'vehicles',
 *                    'stays', 'gallery', 'about', 'contact') or any other key for pages outside the nav
 *   $pageTitle       Title shown in the browser tab (site name is appended)
 *   $pageDescription Meta description
 *   $pageHero        (inner pages) ['title', 'lead', 'image', 'eyebrow']
 *   $breadcrumbs     (optional) extra crumbs between Home and the current page: [['Label', 'url'], ...]
 */
require_once __DIR__ . '/data.php';
require_once __DIR__ . '/components.php';

$pageKey         = $pageKey ?? 'home';
$pageTitle       = $pageTitle ?? 'Discover Himachal';
$pageDescription = $pageDescription ?? 'Reach Dream Travel — Himachal tour packages with pickup and drop. Shimla, Manali, Kasol, Kinnaur and Spiti trips with car, driver and hotels.';
$pageHero        = $pageHero ?? null;
$breadcrumbs     = $breadcrumbs ?? [];
$isHome          = $pageKey === 'home';

// Site root URL (e.g. "/reach/"), used by pages that can be served from any path such as 404.php.
$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';

$nav = [
    'home'         => ['Home', $isHome ? '#home' : 'index.php'],
    'about'        => ['About', 'about.php'],
    'packages'     => ['Packages', 'packages.php'],
    'destinations' => ['Destinations', 'destinations.php'],
    'vehicles'     => ['Vehicles', 'vehicles.php'],
    'stays'        => ['Stays', 'stays.php'],
    'gallery'      => ['Gallery', 'gallery.php'],
    'contact'      => ['Contact', 'contact.php'],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
<?php if (!empty($useBaseHref)): ?>
  <base href="<?= e($baseUrl) ?>">
<?php endif; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0b1f33">
  <title><?= e($pageTitle) ?> | <?= e($site['name']) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?> | <?= e($site['name']) ?>">
  <meta property="og:description" content="<?= e($pageDescription) ?>">
  <meta property="og:type" content="website">
  <link rel="preload" href="assets/fonts/montserrat.ttf" as="font" type="font/ttf" crossorigin>
  <link rel="preload" href="<?= e($pageHero['image'] ?? 'assets/images/hero-himachal.jpg') ?>" as="image">
  <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <script type="application/ld+json">{"@context":"https://schema.org","@type":"TravelAgency","name":"<?= e($site['name']) ?>","description":"Himachal tour packages with car, driver and hotels","address":{"@type":"PostalAddress","addressLocality":"Amritsar","addressRegion":"Punjab","addressCountry":"IN"},"areaServed":"Himachal Pradesh, India","telephone":"<?= e($site['phone']) ?>"}</script>
</head>
<body class="page-<?= e($pageKey) ?>">
  <a class="skip-link" href="#main">Skip to content</a>
  <div class="scroll-progress" aria-hidden="true"></div>

  <header class="site-header" id="top">
    <nav class="navbar navbar-expand-xl container site-nav" aria-label="Main navigation">
      <a class="navbar-brand brand" href="<?= e($nav['home'][1]) ?>" aria-label="<?= e($site['name']) ?> home"><span class="brand-mark" aria-hidden="true"><img src="assets/logo.svg" alt="" width="48" height="48"></span><span>Reach Dream<small>TRAVEL &amp; TOURS</small></span></a>
      <button class="navbar-toggler menu-toggle ms-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#siteMenu" aria-controls="siteMenu" aria-expanded="false" aria-label="Open navigation"><span class="toggle-lines"></span></button>
      <div class="offcanvas offcanvas-xl offcanvas-start site-menu" tabindex="-1" id="siteMenu" aria-labelledby="siteMenuLabel">
        <div class="offcanvas-header d-xl-none"><a class="navbar-brand brand" href="<?= e($nav['home'][1]) ?>" id="siteMenuLabel"><span class="brand-mark" aria-hidden="true"><img src="assets/logo.svg" alt="" width="44" height="44"></span><span>Reach Dream<small>TRAVEL &amp; TOURS</small></span></a><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close navigation"></button></div>
        <div class="offcanvas-body">
          <ul class="navbar-nav mx-xl-auto">
<?php foreach ($nav as $key => [$label, $href]): ?>
            <li class="nav-item"><a class="nav-link<?= $key === $pageKey ? ' active' : '' ?>" href="<?= e($href) ?>"<?= $key === $pageKey ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
          </ul>
          <a class="nav-call" href="tel:<?= e($site['phoneLink']) ?>"><span class="phone-icon"><i class="fa-solid fa-phone" aria-hidden="true"></i></span><span><small>Call for booking</small><b><?= e($site['phone']) ?></b></span></a>
        </div>
      </div>
    </nav>
  </header>

  <main id="main">
<?php if ($pageHero): ?>
    <section class="page-hero" aria-labelledby="pageTitle">
      <div class="page-hero-bg" style="background-image:url('<?= e($pageHero['image']) ?>')" aria-hidden="true"></div>
      <div class="page-hero-overlay" aria-hidden="true"></div>
      <div class="hero-orbs" aria-hidden="true"><span class="orb orb-1"></span><span class="orb orb-2"></span></div>
      <div class="container page-hero-content">
        <nav class="crumbs" aria-label="Breadcrumb">
          <ol>
            <li><a href="index.php"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a></li>
<?php foreach ($breadcrumbs as [$crumbLabel, $crumbUrl]): ?>
            <li><a href="<?= e($crumbUrl) ?>"><?= e($crumbLabel) ?></a></li>
<?php endforeach; ?>
            <li aria-current="page"><?= e($pageHero['crumb'] ?? $pageHero['title']) ?></li>
          </ol>
        </nav>
<?php if (!empty($pageHero['eyebrow'])): ?>
        <span class="eyebrow eyebrow-light"><?= e($pageHero['eyebrow']) ?></span>
<?php endif; ?>
        <h1 id="pageTitle"><?= e($pageHero['title']) ?></h1>
<?php if (!empty($pageHero['lead'])): ?>
        <p class="page-hero-lead"><?= e($pageHero['lead']) ?></p>
<?php endif; ?>
<?php if (!empty($pageHero['meta'])): ?>
        <ul class="page-hero-meta list-unstyled">
<?php foreach ($pageHero['meta'] as [$metaIcon, $metaText]): ?>
          <li><i class="fa-solid <?= e($metaIcon) ?>" aria-hidden="true"></i> <?= e($metaText) ?></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
      </div>
      <div class="page-hero-wave" aria-hidden="true"><svg viewBox="0 0 1440 80" preserveAspectRatio="none"><path d="M0 80V40c160-30 320-40 480-22s320 42 480 34 320-38 480-30v58z"/></svg></div>
    </section>
<?php endif; ?>
