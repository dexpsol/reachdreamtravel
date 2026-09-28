<?php
/** Common footer: closes <main>, site footer, floating buttons, dialogs and scripts. */
$footerPackages = array_slice($packages, 0, 4, true);
?>
  </main>

  <footer class="site-footer">
    <!-- Layered mountain skyline rising out of the page into the footer -->
    <div class="footer-peaks" aria-hidden="true">
      <svg class="peaks-back" viewBox="0 0 1440 160" preserveAspectRatio="none"><path d="M0 160V96l70-34 60 22 90-62 80 44 70-30 110 70 90-52 60 26 100-70 90 48 70-22 110 64 80-38 70 20 90-56 70 30 60-18 70 12v162z"/></svg>
      <svg class="peaks-mid" viewBox="0 0 1440 160" preserveAspectRatio="none"><path d="M0 160v-40l90-40 70 26 110-58 90 46 60-18 120 62 80-34 90 28 110-66 80 40 90-20 100 50 90-36 80 26 90-30 40 12 50-10v58z"/></svg>
      <svg class="peaks-front" viewBox="0 0 1440 160" preserveAspectRatio="none"><path d="M0 160v-22l110-26 90 16 120-34 100 30 90-10 130 26 110-30 120 22 100-18 110 20 120-28 90 16 100-12 50 8v62z"/></svg>
    </div>
    <span class="footer-watermark" aria-hidden="true">HIMACHAL</span>

    <div class="container">
<?php if (!$isHome): ?>
      <div class="footer-cta">
        <div>
          <h2>Planning a trip to the mountains?</h2>
          <p>Tell us your dates and group size, and we will suggest a route, stay and vehicle.</p>
        </div>
        <div class="footer-cta-actions">
          <a class="btn btn-gold" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Chat on WhatsApp</a>
          <a class="btn btn-glass" href="contact.php">Send an enquiry</a>
        </div>
      </div>
<?php endif; ?>

      <!-- Quick contact -->
      <div class="footer-contact">
        <a class="fc-card" href="tel:<?= e($site['phoneLink']) ?>">
          <span class="fc-icon fc-amber"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
          <span class="fc-text"><small>Call for booking</small><b><?= e($site['phone']) ?></b></span>
          <i class="fa-solid fa-arrow-right fc-arrow" aria-hidden="true"></i>
        </a>
        <a class="fc-card" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
          <span class="fc-icon fc-green"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
          <span class="fc-text"><small>WhatsApp · 24/7</small><b>Chat with us</b></span>
          <i class="fa-solid fa-arrow-right fc-arrow" aria-hidden="true"></i>
        </a>
        <a class="fc-card" href="mailto:<?= e($site['email']) ?>">
          <span class="fc-icon fc-blue"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
          <span class="fc-text"><small>Email us</small><b><?= e($site['email']) ?></b></span>
          <i class="fa-solid fa-arrow-right fc-arrow" aria-hidden="true"></i>
        </a>
      </div>

      <div class="footer-main">
        <div class="footer-about">
          <a class="brand footer-brand" href="index.php"><span class="brand-mark">RD</span><span>Reach Dream<small>TRAVEL · HIMACHAL</small></span></a>
          <p>Himachal trips — car with driver, hotels and sightseeing in one booking.</p>
          <p class="footer-address"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?= e($site['address']) ?></p>
          <div class="social-links">
            <a class="s-instagram" href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
            <a class="s-facebook" href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
            <a class="s-whatsapp" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
            <a class="s-email" href="mailto:<?= e($site['email']) ?>" aria-label="Email"><i class="fa-solid fa-envelope" aria-hidden="true"></i></a>
          </div>
        </div>

        <nav class="footer-col" aria-label="Explore">
          <h3>Explore</h3>
          <a href="index.php">Home</a>
          <a href="about.php">About us</a>
          <a href="packages.php">Tour packages</a>
          <a href="destinations.php">Destinations</a>
          <a href="vehicles.php">Vehicles</a>
          <a href="stays.php">Hotels &amp; stays</a>
          <a href="gallery.php">Gallery</a>
          <a href="contact.php">Contact</a>
        </nav>

        <nav class="footer-col" aria-label="Destinations">
          <h3>Destinations</h3>
<?php foreach ($destinations as $destSlug => $dest): ?>
          <a href="destinations.php#<?= e($destSlug) ?>"><?= e($dest['name']) ?></a>
<?php endforeach; ?>
        </nav>

        <div class="footer-col footer-tours">
          <h3>Popular tours</h3>
<?php foreach ($footerPackages as $pkgSlug => $pkg): ?>
          <a class="ft-item" href="<?= e(package_url($pkgSlug)) ?>">
            <img src="<?= e(img($pkg['image'], true)) ?>" alt="" loading="lazy" width="120" height="90">
            <span><b><?= e($pkg['title']) ?></b><small><i class="fa-solid fa-clock" aria-hidden="true"></i> <?= e($pkg['duration']) ?></small></span>
          </a>
<?php endforeach; ?>
        </div>
      </div>

      <div class="footer-bottom">
        <span>© <?= date('Y') ?> <?= e($site['name']) ?>. All rights reserved.</span>
        <!-- <nav class="footer-legal" aria-label="Legal"><a href="privacy.php">Privacy policy</a><a href="terms.php">Terms &amp; conditions</a></nav> -->
        <!-- <span class="footer-made">Made with <i class="fa-solid fa-heart" aria-hidden="true"></i><span class="visually-hidden">love</span> in the Himalaya</span> -->
      </div>
    </div>
  </footer>

  <a class="back-to-top" href="#top" aria-label="Back to top"><svg viewBox="0 0 48 48" aria-hidden="true"><circle class="ring-track" cx="24" cy="24" r="22"/><circle class="ring-fill" cx="24" cy="24" r="22"/></svg><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></a>
  <a class="whatsapp-float" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" aria-label="Chat with <?= e($site['name']) ?> on WhatsApp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><span>Chat with us</span></a>

  <dialog class="lightbox" id="lightbox" aria-label="Photo viewer"><button class="lightbox-close" aria-label="Close gallery"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button><button class="lightbox-prev" aria-label="Previous image"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button><img alt="Himachal scenery"><button class="lightbox-next" aria-label="Next image"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button><div class="lightbox-caption"><b></b><small></small></div><span class="lightbox-count"></span></dialog>

  <script src="assets/bootstrap/bootstrap.bundle.min.js" defer></script>
  <script src="script.js" defer></script>
</body>
</html>
