<?php
require_once __DIR__ . '/database.php';

// Keep display order separate from content so edits never reset positions.
function app_catalog_order(array $catalog, string $scope): array
{
    $db = app_db();
    if (!$db) return $catalog;
    try {
        $statement = $db->prepare('SELECT slugs FROM catalog_orders WHERE scope = ?');
        $statement->execute([$scope]);
        $slugs = json_decode((string) $statement->fetchColumn(), true);
        return is_array($slugs) ? app_catalog_apply_order($catalog, $slugs) : $catalog;
    } catch (Throwable $exception) {
        // Existing installations work before the first order is saved.
        return $catalog;
    }
}

function app_catalog_apply_order(array $catalog, array $slugs): array
{
    $ordered = [];
    foreach ($slugs as $slug) {
        if (is_string($slug) && isset($catalog[$slug])) $ordered[$slug] = $catalog[$slug];
    }
    return $ordered + $catalog;
}

function app_catalog_save_order(PDO $db, string $scope, array $slugs, array $catalog): void
{
    if (!in_array($scope, ['packages', 'solo', 'destinations'], true)) {
        throw new RuntimeException('Choose a valid list.');
    }
    if (count($slugs) !== count($catalog) || count(array_unique($slugs, SORT_STRING)) !== count($slugs)
        || array_filter($slugs, static fn($slug) => !is_string($slug) || !isset($catalog[$slug]))) {
        throw new RuntimeException('This list has changed. Refresh the page and arrange it again.');
    }
    $db->exec('CREATE TABLE IF NOT EXISTS catalog_orders (scope VARCHAR(32) PRIMARY KEY, slugs LONGTEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $statement = $db->prepare('INSERT INTO catalog_orders (scope, slugs) VALUES (?, ?) ON DUPLICATE KEY UPDATE slugs = VALUES(slugs)');
    $statement->execute([$scope, json_encode(array_values($slugs), JSON_THROW_ON_ERROR)]);
}
