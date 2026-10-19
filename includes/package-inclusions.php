<?php if (!empty($package['inclusions']) || !empty($package['exclusions'])): ?>
            <div class="pd-included">
<?php foreach (['inclusions' => ['Inclusions', 'fa-circle-check', 'pd-yes'], 'exclusions' => ['Exclusions', 'fa-circle-xmark', 'pd-maybe']] as $field => [$heading, $icon, $listClass]): ?>
<?php if (!empty($package[$field])): ?>
              <div>
                <h2 class="pd-title"><?= e($heading) ?></h2>
                <ul class="list-unstyled pd-list <?= e($listClass) ?>">
<?php foreach ($package[$field] as $item): ?>
                  <li><i class="fa-solid <?= e($icon) ?>" aria-hidden="true"></i> <?= e($item) ?></li>
<?php endforeach; ?>
                </ul>
              </div>
<?php endif; ?>
<?php endforeach; ?>
            </div>
<?php endif; ?>
