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
    if ($databaseCatalog !== null) return $databaseCatalog;
    $saved = package_store_read();
    foreach ($saved['deleted'] as $slug) {
        unset($defaults[$slug]);
    }
    foreach ($saved['items'] as $slug => $package) {
        if (is_array($package)) {
            $defaults[$slug] = $package;
        }
    }
    return $defaults;
}
