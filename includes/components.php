<?php
/** Reusable markup snippets shared by several pages. */

/** Link to a package detail page. */
function package_url(string $slug): string
{
    return 'package.php?slug=' . rawurlencode($slug);
}

/** Tour package card (homepage and packages page). */
function package_card(string $slug, array $p): void
{
    ?>
            <article class="package-card" data-type="<?= e($p['cat']) ?>">
              <a class="package-image" href="<?= e(package_url($slug)) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e(img($p['image'], true)) ?>" alt="" loading="lazy" width="900" height="600"><?php if ($p['popular']): ?><span class="ribbon"><i class="fa-solid fa-fire" aria-hidden="true"></i> Most popular</span><?php endif; ?><span class="duration-badge"><i class="fa-solid fa-clock" aria-hidden="true"></i> <?= e($p['short']) ?></span></a>
              <div class="package-content">
                <span class="package-label"><?= e($p['label']) ?></span>
                <h3><a href="<?= e(package_url($slug)) ?>"><?= e($p['title']) ?></a></h3>
                <p class="route"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?= e($p['route']) ?></p>
                <div class="package-tags"><span class="tag-from"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> From Amritsar</span><span><i class="fa-solid fa-signal" aria-hidden="true"></i> <?= e($p['difficulty']) ?></span><span><i class="fa-solid fa-sun" aria-hidden="true"></i> <?= e($p['season']) ?></span></div>
                <a class="package-link" href="<?= e(package_url($slug)) ?>">See day-by-day plan <span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span></a>
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
    global $packages;
    foreach ($packages as $p) {
        echo '<option>' . e($p['title']) . '</option>';
    }
    echo '<option>Custom trip</option>';
}

function vehicle_select_options(): void
{
    global $vehicles;
    echo '<option>Suggest the best option</option>';
    foreach ($vehicles as $v) {
        echo '<option>' . e($v['name']) . '</option>';
    }
}
