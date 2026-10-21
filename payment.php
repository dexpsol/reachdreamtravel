<?php
require_once __DIR__ . '/includes/data.php';
$payment = require __DIR__ . '/includes/payment-settings.php';
$bank = $payment['bank'];
$upi = $payment['upi'];
$paymentLocation = $payment['location'] !== '' ? $payment['location'] : $site['address'];
$qrPath = str_replace('\\', '/', $upi['qrImage']);
$qrReady = preg_match('~^assets/images/payments/[a-zA-Z0-9_-]+\.(png|jpe?g|webp)$~D', $qrPath) && is_file(__DIR__ . '/' . $qrPath);
$paymentHelp = wa_link('Hello Reach Dream Travel, please share the payment details and QR code for my booking.');
$pageKey = 'payment';
$pageTitle = 'Payment';
$pageDescription = 'Bank account and UPI payment details for your Reach Dream Travel booking.';
$pageHero = null;
require __DIR__ . '/includes/header.php';
?>
    <section class="payment-section" aria-labelledby="payment-heading">
      <div class="container">
        <div class="payment-title">
          <span>Payment</span>
          <h1 id="payment-heading">Pay Now</h1>
<?php if ($payment['demo']): ?>
          <p class="payment-demo-notice" role="note">Demo details only. Do not use these details to make a payment.</p>
<?php endif; ?>
        </div>
        <div class="payment-layout">
          <div class="payment-qr-panel">
<?php if ($qrReady): ?>
            <img class="payment-qr-image" src="<?= e($qrPath) ?>" alt="<?= e($upi['name'] ?: $site['name']) ?> UPI payment QR code">
<?php else: ?>
            <div class="payment-qr-placeholder">
              <span class="payment-qr-brand"><?= e($site['name']) ?></span>
              <span class="payment-qr-symbol"><i class="fa-solid fa-qrcode" aria-hidden="true"></i></span>
              <h2><?= $payment['demo'] ? 'Demo payment preview' : 'Pay with any UPI app' ?></h2>
              <p>Contact our team for your booking's payment QR code.</p>
              <a href="<?= e($paymentHelp) ?>" class="btn btn-gold" target="_blank" rel="noopener">Request QR code <i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
<?php if ($upi['id'] !== ''): ?>
              <p class="payment-upi-id"><b>UPI ID:</b> <?= e($upi['id']) ?></p>
<?php endif; ?>
            </div>
<?php endif; ?>
          </div>
          <article class="payment-account" aria-labelledby="account-heading">
            <h2 id="account-heading">Account Info</h2>
            <div class="payment-info-box">
              <dl class="payment-account-details">
<?php foreach (['accountName' => 'Account Name', 'accountNumber' => 'Account No.', 'accountType' => 'Account Type', 'ifsc' => 'IFSC Code', 'bankName' => 'Bank Name', 'branch' => 'Branch Name'] as $key => $label): ?>
<?php if ($key === 'branch' && $bank[$key] === '') continue; ?>
                <div><dt><?= e($label) ?> :</dt> <dd><?= e($bank[$key] !== '' ? $bank[$key] : 'Contact us for details') ?></dd></div>
<?php endforeach; ?>
<?php if (($upi['phone'] ?? '') !== ''): ?>
                <div><dt>Google Pay Number :</dt> <dd><?= e($upi['phone']) ?></dd></div>
<?php endif; ?>
<?php if ($upi['id'] !== ''): ?>
                <div><dt>UPI ID :</dt> <dd><?= e($upi['id']) ?></dd></div>
<?php endif; ?>
              </dl>
            </div>
            <div class="payment-info-box payment-location">
              <h3>Location</h3>
<?php if ($paymentLocation !== ''): ?>
              <p><?= nl2br(e($paymentLocation)) ?></p>
<?php else: ?>
              <p>Contact our team for our office address.</p>
              <a href="tel:<?= e($site['phoneLink']) ?>"><?= e($site['phone']) ?></a>
<?php endif; ?>
            </div>
          </article>
        </div>
      </div>
    </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
