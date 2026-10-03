<?php
/** Reusable markup snippets shared by several pages. */

/** Link to a package detail page. */
function package_url(string $slug): string
{
    return 'package.php?slug=' . rawurlencode($slug);
}

/** Human-readable labels for a package's space-separated category keys. */
function package_category_labels(array $package): array
{
    global $packageCategories;
    $keys = preg_split('/\s+/', trim((string) ($package['cat'] ?? ''))) ?: [];
    return array_values(array_map(
        static fn($key) => $packageCategories[$key] ?? ucwords(str_replace('-', ' ', $key)),
        array_filter($keys, static fn($key) => $key !== '' && $key !== 'all')
    ));
}

/** Optional package price shown consistently on cards and detail pages. */
function package_price(array $p): void
{
    $regular = (int) ($p['price_regular'] ?? 0);
    $discount = (int) ($p['price_discount'] ?? 0);
    if ($regular < 1 && $discount < 1) return;
    $shown = $discount > 0 ? $discount : $regular;
    ?>
              <div class="package-price">
                <span><?= $discount > 0 ? 'Discount price' : 'Package price' ?></span>
                <strong><?= e(format_price($shown)) ?></strong>
<?php if ($discount > 0 && $regular > 0): ?>
                <del>Regular <?= e(format_price($regular)) ?></del>
<?php endif; ?>
              </div>
<?php
}

/** Tour package card (homepage and packages page). */
function package_card(string $slug, array $p): void
{
    global $site;
    if (!empty($p['deprecated_duplicate_of'])) return;
    $categoryLabels = package_category_labels($p);
    ?>
            <article class="package-card" data-type="<?= e($p['cat']) ?>">
              <a class="package-image" href="<?= e(package_url($slug)) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e(img($p['image'], true)) ?>" alt="" loading="lazy" width="900" height="600"><?php if ($p['popular']): ?><span class="ribbon"><i class="fa-solid fa-route" aria-hidden="true"></i> Featured itinerary</span><?php endif; ?><span class="duration-badge"><i class="fa-solid fa-clock" aria-hidden="true"></i> <?= e($p['short']) ?></span></a>
              <div class="package-content">
                <span class="package-label"><?= e($p['label']) ?></span>
                <div class="package-tags package-category-tags" aria-label="Package categories">
<?php foreach ($categoryLabels as $categoryLabel): ?>
                  <span><?= e($categoryLabel) ?></span>
<?php endforeach; ?>
                </div>
                <h3><a href="<?= e(package_url($slug)) ?>"><?= e($p['title']) ?></a></h3>
                <p class="route"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?= e($p['route']) ?></p>
<?php package_price($p); ?>
                <div class="package-tags"><span class="tag-from"><i class="fa-solid fa-car-side" aria-hidden="true"></i> Pickup &amp; drop</span><span><i class="fa-solid fa-signal" aria-hidden="true"></i> <?= e($p['difficulty']) ?></span><span><i class="fa-solid fa-sun" aria-hidden="true"></i> <?= e($p['season']) ?></span></div>
                <a class="package-link" href="<?= e(package_url($slug)) ?>">See day-by-day plan <span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span></a>
                <div class="package-contact-actions" aria-label="Contact us about <?= e($p['title']) ?>">
                  <a class="package-contact-icon package-call" href="tel:<?= e($site['phoneLink']) ?>" aria-label="Call about <?= e($p['title']) ?>" title="Call us"><i class="fa-solid fa-phone" aria-hidden="true"></i></a>
                  <a class="package-contact-icon package-whatsapp" href="<?= e(wa_link('Hello Reach Dream Travel, I would like to know more about ' . $p['title'] . '.')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp about <?= e($p['title']) ?>" title="WhatsApp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
                  <button class="package-callback" type="button" data-callback-trigger data-package-title="<?= e($p['title']) ?>" aria-haspopup="dialog" aria-controls="callback-dialog"><i class="fa-solid fa-phone-volume" aria-hidden="true"></i> Request callback</button>
                </div>
              </div>
            </article>
<?php
}

/** Compact vehicle card (homepage fleet grid). */
function vehicle_card(array $v): void
{
    [$extraIcon, $extraLabel] = $v['extra'];
    ?>
          <article class="vehicle-card" data-type="<?= e($v['type']) ?>">
            <div class="vehicle-photo"><img src="<?= e($v['image']) ?>" alt="<?= e($v['alt']) ?>" loading="lazy" width="1200" height="900"><span class="vehicle-tag"><?= e($v['tag']) ?></span></div>
            <div class="vehicle-body">
              <h3><?= e($v['name']) ?></h3>
              <p><?= e($v['summary']) ?></p>
              <ul class="vehicle-specs list-unstyled"><li><i class="fa-solid fa-user-group" aria-hidden="true"></i> <?= e($v['seats']) ?> seats</li><li><i class="fa-solid fa-suitcase-rolling" aria-hidden="true"></i> <?= e($v['bags']) ?></li><li><i class="fa-solid <?= e($extraIcon) ?>" aria-hidden="true"></i> <?= e($extraLabel) ?></li></ul>
              <div class="vehicle-foot"><span class="best-for">Best for <?= e(lcfirst($v['best'])) ?></span><a class="vehicle-cta" data-vehicle="<?= e($v['name']) ?>" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" aria-label="Enquire about <?= e($v['name']) ?>"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
            </div>
          </article>
<?php
}

/**
 * Filter chip bar. $target is a CSS selector for the items to filter;
 * items list their categories (space-separated) in data-type.
 */
function filter_bar(array $options, string $target, string $label): void
{
    ?>
        <div class="filter-bar" role="group" aria-label="<?= e($label) ?>" data-target="<?= e($target) ?>">
<?php $first = true; foreach ($options as $key => $text): ?>
          <button type="button" class="<?= $first ? 'active' : '' ?>" data-filter="<?= e($key) ?>" aria-pressed="<?= $first ? 'true' : 'false' ?>"><?= e($text) ?></button>
<?php $first = false; endforeach; ?>
        </div>
<?php
}

/** Trip enquiry form fields (destination/date/travellers/vehicle), shared by forms. */
function trip_select_options(): void
{
    global $publicPackages;
    foreach ($publicPackages as $p) {
        echo '<option>' . e($p['title']) . '</option>';
    }
    echo '<option>Custom trip</option>';
}

function vehicle_select_options(): void
{
    global $vehicles;
    echo '<option>No preference </option>';
    foreach ($vehicles as $v) {
        echo '<option>' . e($v['name']) . '</option>';
    }
}
