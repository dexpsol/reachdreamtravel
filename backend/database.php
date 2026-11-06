<?php
// Records created before status controls were introduced remain active.
function app_catalog_is_active(array $record): bool
{
    return (bool) ($record['active'] ?? true);
}

function app_load_env(): void
{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    $loaded = true;
    $path = dirname(__DIR__) . '/.env';

    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        if (app_env_value_is_quoted($value)) {
            $value = substr($value, 1, -1);
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function app_env_value_is_quoted(string $value): bool
{
    if (strlen($value) < 2) {
        return false;
    }

    $first = $value[0];
    $last = substr($value, -1);

    return ($first === '"' && $last === '"') || ($first === "'" && $last === "'");
}

app_load_env();

function app_db(bool $serverOnly = false, bool $refresh = false): ?PDO
{
    static $connections = [];

    $key = $serverOnly ? 'server' : 'database';

    if (!$refresh && array_key_exists($key, $connections)) {
        return $connections[$key];
    }

    $host = getenv('REACH_DB_HOST');
    $name = getenv('REACH_DB_NAME');
    $user = getenv('REACH_DB_USER');
    $password = getenv('REACH_DB_PASSWORD');

    if ($host === false || $name === false || $user === false || $password === false) {
        $connections[$key] = null;
        return null;
    }

    $dsn = 'mysql:host=' . $host . ';charset=utf8mb4';

    if (!$serverOnly) {
        $dsn .= ';dbname=' . $name;
    }

    try {
        $connections[$key] = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $e) {
        $connections[$key] = null;
    }

    return $connections[$key];
}

function app_db_initialize(array $packages, array $vehicles, array $destinationGroups = []): string
{
    $name = getenv('REACH_DB_NAME');

    if ($name === false || $name === '') {
        return 'Set REACH_DB_NAME in the .env file.';
    }

    $server = app_db(true);

    if (!$server) {
        return 'Cannot connect to MySQL. Check the REACH_DB_* settings and start MySQL.';
    }

    $quotedName = '`' . str_replace('`', '``', $name) . '`';
    $server->exec('CREATE DATABASE IF NOT EXISTS ' . $quotedName . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $db = app_db(false, true);

    if (!$db) {
        return 'Database created, but the application could not connect to it.';
    }

    app_db_create_tables($db);
    app_db_insert_packages($db, $packages);
    app_db_insert_taxis($db, $vehicles);
    app_db_insert_destination_groups($db, $destinationGroups);

    return '';
}

function app_db_create_tables(PDO $db): void
{
    $db->exec('CREATE TABLE IF NOT EXISTS packages (slug VARCHAR(190) PRIMARY KEY, payload LONGTEXT NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $db->exec('CREATE TABLE IF NOT EXISTS taxis (slug VARCHAR(190) PRIMARY KEY, payload LONGTEXT NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $db->exec('CREATE TABLE IF NOT EXISTS destination_groups (slug VARCHAR(190) PRIMARY KEY, payload LONGTEXT NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $db->exec("CREATE TABLE IF NOT EXISTS users (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, role VARCHAR(32) NOT NULL DEFAULT 'admin', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function app_db_insert_packages(PDO $db, array $packages): void
{
    $insert = $db->prepare('INSERT IGNORE INTO packages (slug, payload) VALUES (?, ?)');

    foreach ($packages as $slug => $package) {
        $insert->execute([$slug, app_db_json($package)]);
    }
}

function app_db_insert_taxis(PDO $db, array $vehicles): void
{
    $insert = $db->prepare('INSERT IGNORE INTO taxis (slug, payload) VALUES (?, ?)');

    foreach ($vehicles as $vehicle) {
        $slug = app_db_slug((string) ($vehicle['name'] ?? ''));
        $insert->execute([$slug, app_db_json($vehicle)]);
    }
}

function app_db_insert_destination_groups(PDO $db, array $destinationGroups): void
{
    $insert = $db->prepare('INSERT IGNORE INTO destination_groups (slug, payload) VALUES (?, ?)');

    foreach ($destinationGroups as $slug => $group) {
        $insert->execute([$slug, app_db_json($group)]);
    }
}

function app_db_catalog(string $table): ?array
{
    if (!in_array($table, ['packages', 'taxis', 'destination_groups'], true)) {
        return null;
    }

    $db = app_db();

    if (!$db) {
        return null;
    }

    try {
        $rows = $db->query('SELECT slug, payload FROM `' . $table . '` ORDER BY updated_at DESC')->fetchAll();

        if (!$rows && !app_db_table_exists($db, $table)) {
            return null;
        }

        return app_db_rows_to_catalog($rows, $table);
    } catch (Throwable $e) {
        return null;
    }
}

function app_db_table_exists(PDO $db, string $table): bool
{
    return (bool) $db->query('SHOW TABLES LIKE ' . $db->quote($table))->fetchColumn();
}

function app_db_rows_to_catalog(array $rows, string $table): array
{
    $catalog = [];

    foreach ($rows as $row) {
        $value = json_decode($row['payload'], true);

        if (!is_array($value)) {
            continue;
        }

        if ($table === 'taxis') {
            $value['_slug'] = $row['slug'];
        }

        $catalog[$row['slug']] = $value;
    }

    return $catalog;
}

function app_db_json(array $value): string
{
    return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function app_db_slug(string $value): string
{
    return trim(strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $value)), '-');
}

/** One-time conversion of existing tours to explicit main destinations. */
function app_db_migrate_package_destinations(PDO $db, array $packages, array $destinations): array
{
    $db->exec('CREATE TABLE IF NOT EXISTS catalog_migrations (migration_key VARCHAR(190) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $migrationKey = 'explicit-main-destinations-2026-11-v1';
    $check = $db->prepare('SELECT 1 FROM catalog_migrations WHERE migration_key = ?');
    $check->execute([$migrationKey]);
    if ($check->fetchColumn()) return $packages;
    $savedPackages = app_db_catalog('packages');
    if ($savedPackages === null) return $packages;

    // Reviewed tour assignments, used only for this migration. Pickup cities and
    // passing stops are deliberately excluded; future edits use the admin form.
    $assignments = [
        'shimla-spiti-kinnaur' => ['spiti'],
        'shimla-kullu-manali' => ['shimla', 'manali', 'kasol'],
        'shimla-local' => ['shimla'],
        'kasol-parvati' => ['kasol'],
        'manali-solang' => ['manali'],
        'kinnaur-chitkul' => ['kinnaur'],
        'chandratal-lahaul' => ['chandratal'],
        'shakti-peeths-himachal' => ['kangra', 'palampur'],
        'golden-triangle-delhi-agra-jaipur' => ['delhi', 'agra', 'rajasthan'],
        'rajasthan-heritage-trail' => ['rajasthan'],
        'delhi-agra-weekend' => ['delhi', 'agra'],
        'delhi-heritage-city-tour' => ['delhi'],
        'agra-heritage-tour' => ['agra'],
        'nainital' => ['nainital'],
        'bhimtal' => ['bhimtal'],
        'mussoorie' => ['mussoorie'],
        'chakrata' => ['chakrata'],
        'rishikesh' => ['rishikesh'],
        'haridwar' => ['haridwar', 'rishikesh'],
        'auli' => ['auli'],
        'jim-corbett' => ['jim-corbett'],
        'uttarakhand-hills-ganga-wildlife-tour' => ['uttarakhand', 'mussoorie', 'rishikesh', 'haridwar', 'nainital', 'jim-corbett'],
        'kashmir-valley' => ['jammu-kashmir', 'srinagar', 'gulmarg', 'pahalgam', 'sonamarg'],
        'jammu-kashmir-six-day-tour' => ['jammu-kashmir', 'jammu-katra', 'srinagar', 'gulmarg', 'pahalgam', 'sonamarg'],
        'jammu-katra-patnitop' => ['patnitop'],
        'katra-vaishno-devi' => ['jammu-katra'],
        'dharamshala-mcleodganj' => ['dharamshala', 'mcleod-ganj'],
        'triund-trek' => ['dharamshala', 'mcleod-ganj'],
        'kareri-lake-trek' => ['dharamshala'],
        'rupin-pass-trek' => ['shimla'],
        'buran-ghati-trek' => ['shimla'],
        'bijli-mahadev-trek' => ['kasol'],
        'indrahara-pass-trek' => ['dharamshala', 'mcleod-ganj'],
        'fairy-forest-pulga' => ['kasol'],
        'kalatop-wildlife-sanctuary' => ['dalhousie-khajjiar'],
        'shimla-water-catchment-wildlife-sanctuary' => ['shimla'],
        'dhungri-van-vihar' => ['manali'],
        'shoja-jibhi-forest-escape' => ['kasol'],
        'chail-wildlife-sanctuary' => ['chail'],
        'chail-tour' => ['chail'],
        'kufri-tour' => ['kufri'],
        'dalhousie-khajjiar' => ['dalhousie-khajjiar', 'khajjiar'],
        'char-dham-hemkund-sahib-yatra' => ['uttarakhand', 'yamunotri', 'gangotri', 'kedarnath', 'badrinath', 'hemkund-sahib'],
        'pathankot-nurpur-dharamshala-kangra' => ['pathankot', 'dharamshala', 'mcleod-ganj', 'kangra'],
        'amritsar-pathankot-dalhousie' => ['amritsar', 'pathankot', 'dalhousie-khajjiar'],
        'amritsar-katra' => ['amritsar', 'jammu-katra'],
        'solo-rishikesh-activity-break' => ['rishikesh'],
        'solo-manali-adventure-break' => ['manali'],
        'solo-kasol-backpacking-break' => ['kasol'],
        'solo-chakrata-nature-activity' => ['chakrata'],
        'solo-dharamshala-triund-activity' => ['dharamshala', 'mcleod-ganj'],
        'dharamshala-mcleod-ganj-trek' => ['dharamshala', 'mcleod-ganj'],
        'dalhousie-khajjiar-nature' => ['dalhousie-khajjiar', 'khajjiar'],
        'shimla-kufri-narkanda' => ['shimla', 'kufri', 'narkanda'],
        'himachal-six-shakti-peeth' => ['kangra', 'palampur'],
        'jaipur-jaisalmer-test' => ['rajasthan'],
    ];

    $db->beginTransaction();
    try {
        $update = $db->prepare('UPDATE packages SET payload = ?, updated_at = updated_at WHERE slug = ?');
        // Read the original payloads so display defaults are never written back.
        foreach ($savedPackages as $slug => $package) {
            if (array_key_exists('destination_slugs', $package)) continue;
            $package['destination_slugs'] = array_values(array_filter($assignments[$slug] ?? [], static fn($destination) => isset($destinations[$destination])));
            $update->execute([app_db_json($package), $slug]);
            if (isset($packages[$slug])) $packages[$slug]['destination_slugs'] = $package['destination_slugs'];
        }
        $updateDestination = $db->prepare('UPDATE destination_groups SET payload = ?, updated_at = updated_at WHERE slug = ?');
        foreach (app_db_catalog('destination_groups') ?? [] as $slug => $destination) {
            if (!array_key_exists('packages', $destination)) continue;
            unset($destination['packages']);
            $updateDestination->execute([app_db_json($destination), $slug]);
        }
        $mark = $db->prepare('INSERT IGNORE INTO catalog_migrations (migration_key) VALUES (?)');
        $mark->execute([$migrationKey]);
        $db->commit();
    } catch (Throwable $exception) {
        $db->rollBack();
        throw $exception;
    }
    return $packages;
}

function app_package_destinations_from_post(array $post, array $destinations): array
{
    $selected = $post['destination_slugs'] ?? [];
    if (!is_array($selected) || array_filter($selected, static fn($slug) => !is_string($slug))
        || array_diff($selected, array_keys($destinations))) {
        throw new RuntimeException('Choose valid main destinations.');
    }
    return array_values(array_unique($selected));
}
