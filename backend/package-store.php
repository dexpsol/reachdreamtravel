<?php
function package_store_dir(): string
{
    return dirname(__DIR__) . '/storage';
}

function package_store_read(): array
{
    $path = package_store_dir() . '/packages.json';
    if (!is_file($path)) {
        return ['items' => [], 'deleted' => []];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data + ['items' => [], 'deleted' => []] : ['items' => [], 'deleted' => []];
}

function package_store_write(array $data): bool
{
    $dir = package_store_dir();
    if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
        return false;
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    $temp = $dir . '/packages.json.tmp';
    if (file_put_contents($temp, $json, LOCK_EX) === false) {
        return false;
    }
    return rename($temp, $dir . '/packages.json');
}

function package_catalog(array $defaults): array
{
    require_once __DIR__ . '/database.php';
    $databaseCatalog = app_db_catalog('packages');
    if ($databaseCatalog !== null) {
        $db = app_db();
        if (!$db) return $databaseCatalog;
        $db->exec('CREATE TABLE IF NOT EXISTS hidden_packages (slug VARCHAR(190) PRIMARY KEY) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $hidden = $db->query('SELECT slug FROM hidden_packages')->fetchAll(PDO::FETCH_COLUMN);
        $hiddenMap = array_fill_keys($hidden, true);
        $newSlugs = ['nainital', 'mussoorie', 'chakrata', 'rishikesh', 'solo-rishikesh-activity-break', 'solo-manali-adventure-break', 'solo-kasol-backpacking-break', 'solo-chakrata-nature-activity', 'solo-dharamshala-triund-activity', 'haridwar', 'auli', 'jim-corbett', 'kashmir-valley', 'dharamshala-mcleodganj', 'triund-trek', 'kareri-lake-trek', 'rupin-pass-trek', 'buran-ghati-trek', 'prashar-lake-trek', 'bijli-mahadev-trek', 'indrahara-pass-trek', 'fairy-forest-pulga', 'kalatop-wildlife-sanctuary', 'shimla-water-catchment-wildlife-sanctuary', 'dhungri-van-vihar', 'shoja-jibhi-forest-escape', 'chail-wildlife-sanctuary', 'dalhousie-khajjiar', 'jammu-katra-patnitop', 'katra-vaishno-devi', 'char-dham-hemkund-sahib-yatra', 'pathankot-nurpur-dharamshala-kangra', 'amritsar-pathankot-dalhousie', 'amritsar-katra', 'international-coming-soon'];
        $insert = $db->prepare('INSERT IGNORE INTO packages (slug, payload) VALUES (?, ?)');
        foreach ($newSlugs as $slug) {
            if (isset($defaults[$slug]) && !isset($databaseCatalog[$slug]) && !isset($hiddenMap[$slug])) {
                $package = $defaults[$slug];
                $insert->execute([$slug, json_encode($package, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)]);
                $databaseCatalog[$slug] = $package;
            }
        }
        foreach ($databaseCatalog as $slug => $package) {
            if (isset($hiddenMap[$slug])) unset($databaseCatalog[$slug]);
            elseif (isset($package['cat'])) {
                $categories = preg_split('/\s+/', trim((string) $package['cat'])) ?: [];
                if (in_array('city', $categories, true)) {
                    $categories = array_values(array_unique(array_map(static fn($category) => $category === 'city' ? 'heritage' : $category, $categories)));
                    $package['cat'] = implode(' ', $categories);
                    $databaseCatalog[$slug] = $package;
                    try {
                        $update = $db->prepare('UPDATE packages SET payload = ? WHERE slug = ?');
                        $update->execute([json_encode($package, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), $slug]);
                    } catch (Throwable $ignored) {
                        // The in-memory category mapping still keeps the site filters correct.
                    }
                }
            }
        }
        $db->exec('CREATE TABLE IF NOT EXISTS catalog_migrations (migration_key VARCHAR(190) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $migrationSlugs = ['shimla-local', 'shakti-peeths-himachal', 'chandratal-lahaul', 'nainital', 'mussoorie', 'chakrata', 'rishikesh', 'solo-rishikesh-activity-break', 'solo-manali-adventure-break', 'solo-kasol-backpacking-break', 'solo-chakrata-nature-activity', 'solo-dharamshala-triund-activity', 'haridwar', 'auli', 'jim-corbett', 'kashmir-valley', 'dharamshala-mcleodganj', 'dalhousie-khajjiar', 'jammu-katra-patnitop', 'katra-vaishno-devi', 'char-dham-hemkund-sahib-yatra', 'pathankot-nurpur-dharamshala-kangra', 'amritsar-pathankot-dalhousie', 'amritsar-katra', 'international-coming-soon'];
        $migrationCheck = $db->prepare('SELECT 1 FROM catalog_migrations WHERE migration_key = ?');
        $migrationWrite = $db->prepare('INSERT IGNORE INTO catalog_migrations (migration_key) VALUES (?)');
        $packageUpdate = $db->prepare('UPDATE packages SET payload = ? WHERE slug = ?');
        foreach ($migrationSlugs as $slug) {
            $migrationKey = 'north-india-route-content-2026-10-v2-' . $slug;
            $migrationCheck->execute([$migrationKey]);
            if ($migrationCheck->fetchColumn() || !isset($databaseCatalog[$slug], $defaults[$slug])) continue;
            $updatedPackage = array_replace($databaseCatalog[$slug], $defaults[$slug]);
            if (!empty($databaseCatalog[$slug]['image_customized'])) {
                $updatedPackage['image'] = $databaseCatalog[$slug]['image'];
                $updatedPackage['image_customized'] = true;
            }
            $payload = json_encode($updatedPackage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $packageUpdate->execute([$payload, $slug]);
            $migrationWrite->execute([$migrationKey]);
            $databaseCatalog[$slug] = $updatedPackage;
        }
        $migrationKey = 'mussoorie-visiting-places-2026-10-v1';
        $migrationCheck->execute([$migrationKey]);
        if (!$migrationCheck->fetchColumn() && isset($databaseCatalog['mussoorie'], $defaults['mussoorie'])) {
            $updatedPackage = array_replace($databaseCatalog['mussoorie'], [
                'route' => $defaults['mussoorie']['route'],
                'overview' => $defaults['mussoorie']['overview'],
                'highlights' => $defaults['mussoorie']['highlights'],
            ]);
            if (isset($defaults['mussoorie']['days'])) {
                $updatedPackage['days'] = $defaults['mussoorie']['days'];
            }
            if (!empty($databaseCatalog['mussoorie']['image_customized'])) {
                $updatedPackage['image'] = $databaseCatalog['mussoorie']['image'];
                $updatedPackage['image_customized'] = true;
            }
            $payload = json_encode($updatedPackage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $packageUpdate->execute([$payload, 'mussoorie']);
            $migrationWrite->execute([$migrationKey]);
            $databaseCatalog['mussoorie'] = $updatedPackage;
        }
        $refreshSlugs = ['mussoorie', 'chakrata', 'rishikesh', 'solo-rishikesh-activity-break', 'solo-manali-adventure-break', 'solo-kasol-backpacking-break', 'solo-chakrata-nature-activity', 'solo-dharamshala-triund-activity', 'haridwar', 'kashmir-valley', 'jammu-katra-patnitop', 'katra-vaishno-devi', 'international-coming-soon'];
        $migrationKey = 'requested-packages-refresh-2026-10-v3';
        $migrationCheck->execute([$migrationKey]);
        if (!$migrationCheck->fetchColumn()) {
            foreach ($refreshSlugs as $slug) {
                if (!isset($databaseCatalog[$slug], $defaults[$slug])) continue;
                $updatedPackage = array_replace($databaseCatalog[$slug], $defaults[$slug]);
                if (!empty($databaseCatalog[$slug]['image_customized'])) {
                    $updatedPackage['image'] = $databaseCatalog[$slug]['image'];
                    $updatedPackage['image_customized'] = true;
                }
                $payload = json_encode($updatedPackage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                $packageUpdate->execute([$payload, $slug]);
                $databaseCatalog[$slug] = $updatedPackage;
            }
            $migrationWrite->execute([$migrationKey]);
        }
        $duplicateRoutes = [
            'dharamshala-mcleod-ganj-trek' => 'dharamshala-mcleodganj',
            'dalhousie-khajjiar-nature' => 'dalhousie-khajjiar',
            'shimla-kufri-narkanda' => 'shimla-local',
            'himachal-six-shakti-peeth' => 'shakti-peeths-himachal',
        ];
        // Preserve the old package records while marking their existing canonical routes.
        foreach ($duplicateRoutes as $duplicateSlug => $canonicalSlug) {
            if (isset($databaseCatalog[$duplicateSlug]) && (isset($databaseCatalog[$canonicalSlug]) || isset($defaults[$canonicalSlug]))) {
                $databaseCatalog[$duplicateSlug]['deprecated_duplicate_of'] = $canonicalSlug;
            }
        }
        return $databaseCatalog;
    }
    $saved = package_store_read();
    foreach ($saved['deleted'] as $slug) {
        unset($defaults[$slug]);
    }
    foreach ($saved['items'] as $slug => $package) {
        if (is_array($package)) {
            $categories = preg_split('/\s+/', trim((string) ($package['cat'] ?? ''))) ?: [];
            if (in_array('city', $categories, true)) {
                $package['cat'] = implode(' ', array_values(array_unique(array_map(static fn($category) => $category === 'city' ? 'heritage' : $category, $categories))));
            }
            $defaults[$slug] = $package;
        }
    }
    foreach ($defaults as &$package) {
        $categories = preg_split('/\s+/', trim((string) ($package['cat'] ?? ''))) ?: [];
        if (in_array('city', $categories, true)) {
            $package['cat'] = implode(' ', array_values(array_unique(array_map(static fn($category) => $category === 'city' ? 'heritage' : $category, $categories))));
        }
    }
    unset($package);
    $duplicateRoutes = [
        'dharamshala-mcleod-ganj-trek' => 'dharamshala-mcleodganj',
        'dalhousie-khajjiar-nature' => 'dalhousie-khajjiar',
        'shimla-kufri-narkanda' => 'shimla-local',
        'himachal-six-shakti-peeth' => 'shakti-peeths-himachal',
    ];
    foreach ($duplicateRoutes as $duplicateSlug => $canonicalSlug) {
        if (isset($defaults[$duplicateSlug], $defaults[$canonicalSlug])) {
            $defaults[$duplicateSlug]['deprecated_duplicate_of'] = $canonicalSlug;
        }
    }
    return $defaults;
}
