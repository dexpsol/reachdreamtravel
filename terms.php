<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'terms';
$pageTitle = 'Terms & Conditions';
$pageDescription = 'Booking terms for tours, stays and vehicles arranged by Reach Dream Travel.';
$pageHero = [
    'eyebrow' => 'Legal',
    'title'   => 'Terms & Conditions',
    'lead'    => 'The basics of how bookings with us work.',
    'image'   => img('meadow'),
];
require __DIR__ . '/includes/header.php';

$sections = [
    'quotes' => ['Quotes & bookings', [
        'All quotes are tailored to your dates, route, group size and chosen stay and vehicle categories. A booking is confirmed once we have confirmed it with you in writing and any agreed advance has been received.',
        'Prices can change with season and availability until a booking is confirmed.',
    ]],
    'inclusions' => ['What is included', [
        'Your confirmation lists exactly what is included — for example vehicle, driver, stays, sightseeing and meals. Anything not listed, such as entry tickets, activities, permits, tolls or parking, is not included unless agreed.',
    ]],
    'changes' => ['Changes & cancellations', [
        'Tell us as early as possible if you need to change or cancel. Cancellation charges depend on how close to the travel date you cancel and on the policies of the hotels and partners involved; these are shared with your confirmation.',
    ]],
    'mountains' => ['Travel in the mountains', [
        'Mountain roads, passes and weather can change quickly. For your safety, routes, timings or stays may need to change because of snow, landslides, road closures or official restrictions. We will always try to offer the best available alternative.',
        'Additional costs caused by such events — for example an extra night’s stay — are not covered unless agreed.',
    ]],
    'responsibilities' => ['Your responsibilities', [
        'Please carry valid photo ID and any permits required for your route, follow your driver’s safety advice, and let us know in advance about any health conditions relevant to high-altitude travel.',
    ]],
    'liability' => ['Liability', [
        'We work with trusted partners but are not liable for loss, injury, delay or damage caused by events beyond our reasonable control. We recommend travel insurance for longer and high-altitude trips.',
    ]],
];
?>

    <section class="section legal-section">
      <div class="container">
        <div class="legal-layout">
          <aside class="legal-toc">
            <b>On this page</b>
            <nav aria-label="Terms sections">
<?php foreach ($sections as $id => [$title]): ?>
              <a href="#<?= e($id) ?>"><?= e($title) ?></a>
<?php endforeach; ?>
              <a href="#contact-us">Contact us</a>
            </nav>
          </aside>
          <article class="legal-body">
            <p class="legal-updated"><i class="fa-solid fa-calendar" aria-hidden="true"></i> Last updated: <?= date('F Y') ?></p>
            <p class="legal-intro">These terms apply to tours, stays and vehicles booked with <?= e($site['name']) ?>. Please read them before confirming your trip.</p>
<?php foreach ($sections as $id => [$title, $paragraphs]): ?>
            <section id="<?= e($id) ?>">
              <h2><?= e($title) ?></h2>
<?php foreach ($paragraphs as $paragraph): ?>
              <p><?= e($paragraph) ?></p>
<?php endforeach; ?>
            </section>
<?php endforeach; ?>
            <section id="contact-us">
              <h2>Contact us</h2>
              <p>Questions about these terms? Email <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a> or call <a href="tel:<?= e($site['phoneLink']) ?>"><?= e($site['phone']) ?></a>.</p>
            </section>
          </article>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
