<?php
function app_load_env(): void
{
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;
    $path = dirname(__DIR__) . '/.env';
    if (!is_readable($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key === '' || getenv($key) !== false) continue;
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

app_load_env();

function app_db(bool $serverOnly = false, bool $refresh = false): ?PDO
{
    static $connections = [];
    $key = $serverOnly ? 'server' : 'database';
    if (!$refresh && array_key_exists($key, $connections)) return $connections[$key];
    $host = getenv('REACH_DB_HOST');
    $name = getenv('REACH_DB_NAME');
    $user = getenv('REACH_DB_USER');
    $password = getenv('REACH_DB_PASSWORD');
    if ($host === false || $name === false || $user === false || $password === false) {
        $connections[$key] = null;
        return null;
    }
    try {
        $dsn = 'mysql:host=' . $host . ';charset=utf8mb4' . ($serverOnly ? '' : ';dbname=' . $name);
        $connections[$key] = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
    } catch (Throwable $e) {
        $connections[$key] = null;
    }
    return $connections[$key];
}

function app_db_initialize(array $packages, array $vehicles): string
{
    $name = getenv('REACH_DB_NAME');
    if ($name === false || $name === '') return 'Set REACH_DB_NAME in the .env file.';
    $server = app_db(true);
    if (!$server) return 'Cannot connect to MySQL. Check the REACH_DB_* settings and start MySQL.';
    $quotedName = '`' . str_replace('`', '``', $name) . '`';
    $server->exec('CREATE DATABASE IF NOT EXISTS ' . $quotedName . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $db = app_db(false, true);
    if (!$db) return 'Database created, but the application could not connect to it.';
    $db->exec('CREATE TABLE IF NOT EXISTS packages (slug VARCHAR(190) PRIMARY KEY, payload LONGTEXT NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $db->exec('CREATE TABLE IF NOT EXISTS taxis (slug VARCHAR(190) PRIMARY KEY, payload LONGTEXT NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $db->exec("CREATE TABLE IF NOT EXISTS users (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, role VARCHAR(32) NOT NULL DEFAULT 'admin', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $packageInsert = $db->prepare('INSERT IGNORE INTO packages (slug, payload) VALUES (?, ?)');
    foreach ($packages as $slug => $package) $packageInsert->execute([$slug, json_encode($package, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    $taxiInsert = $db->prepare('INSERT IGNORE INTO taxis (slug, payload) VALUES (?, ?)');
    foreach ($vehicles as $vehicle) {
        $slug = trim(strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $vehicle['name'])), '-');
        $taxiInsert->execute([$slug, json_encode($vehicle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
    return '';
}

function app_db_catalog(string $table): ?array
{
    if (!in_array($table, ['packages', 'taxis'], true)) return null;
    $db = app_db();
    if (!$db) return null;
    try {
        $rows = $db->query('SELECT slug, payload FROM `' . $table . '` ORDER BY updated_at DESC')->fetchAll();
        if (!$rows && !$db->query("SHOW TABLES LIKE " . $db->quote($table))->fetchColumn()) return null;
        $catalog = [];
        foreach ($rows as $row) {
            $value = json_decode($row['payload'], true);
            if (is_array($value)) {
                if ($table === 'taxis') $value['_slug'] = $row['slug'];
                $catalog[$row['slug']] = $value;
            }
        }
        return $catalog;
    } catch (Throwable $e) {
        return null;
    }
}
