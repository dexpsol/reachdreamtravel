<?php
require_once __DIR__ . '/includes/data.php';
$pageKey = 'privacy';
$pageTitle = 'Privacy Policy';
$pageDescription = 'How Reach Dream Travel collects, uses and protects the information you share when planning a trip with us.';
$pageHero = [
    'eyebrow' => 'Legal',
    'title'   => 'Privacy Policy',
    'lead'    => 'How we handle the information you share with us when planning your trip.',
    'image'   => img('meadow'),
];
require __DIR__ . '/includes/header.php';

$sections = [
    'collect' => ['Information we collect', [
        'When you enquire or book, we collect the details you choose to share — such as your name, phone number, email address, travel dates, group size and trip preferences.',
        'Our enquiry forms open WhatsApp on your device with your message pre-filled. Nothing is sent until you press send in WhatsApp, and this website does not store the form contents.',
    ]],
    'use' => ['How we use it', [
        'We use your information only to respond to enquiries, plan and confirm your trip, arrange stays and transport, and contact you about your booking.',
        'We do not sell or rent your personal information.',
    ]],
    'share' => ['Sharing with partners', [
        'To deliver your trip we share only the details needed with the hotels, drivers and service partners involved in your booking.',
    ]],
    'keep' => ['How long we keep it', [
        'We keep booking information for as long as needed to provide our services and to meet legal and accounting requirements.',
    ]],
    'cookies' => ['Cookies & third-party services', [
        'This website does not use advertising or tracking cookies. The contact page shows an embedded Google Map, and WhatsApp links open WhatsApp — these services have their own privacy policies.',
    ]],
    'rights' => ['Your choices', [
        'You can ask us to access, correct or delete the personal information we hold about you at any time by contacting us.',
    ]],
];
?>

    <section class="section legal-section">
      <div class="container">
        <div class="legal-layout">
          <aside class="legal-toc">
            <b>On this page</b>
            <nav aria-label="Privacy policy sections">
<?php foreach ($sections as $id => [$title]): ?>
              <a href="#<?= e($id) ?>"><?= e($title) ?></a>
<?php endforeach; ?>
              <a href="#contact-us">Contact us</a>
            </nav>
          </aside>
          <article class="legal-body">
            <p class="legal-updated"><i class="fa-solid fa-calendar" aria-hidden="true"></i> Last updated: <?= date('F Y') ?></p>
            <p class="legal-intro"><?= e($site['name']) ?> (“we”, “us”) respects your privacy. This policy explains what information we collect when you use this website or plan a trip with us, and how we use it.</p>
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
              <p>Questions about this policy? Email <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a> or call <a href="tel:<?= e($site['phoneLink']) ?>"><?= e($site['phone']) ?></a>.</p>
            </section>
          </article>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
