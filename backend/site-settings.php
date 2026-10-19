<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';

function site_contact_settings(): array
{
    $db = app_db();
    if (!$db) return [];
    try {
        $query = $db->prepare('SELECT payload FROM site_settings WHERE setting_key = ?');
        $query->execute(['contact']);
        $settings = json_decode((string) $query->fetchColumn(), true);
        return is_array($settings) ? array_intersect_key($settings, array_flip(['phone', 'phoneLink', 'otherPhones', 'whatsapp', 'email', 'address'])) : [];
    } catch (PDOException $exception) {
        return [];
    }
}

function site_contact_from_post(array $post): array
{
    $text = static function (string $key) use ($post): string {
        if (!is_string($post[$key] ?? '')) throw new RuntimeException('Enter valid contact details.');
        return trim($post[$key] ?? '');
    };
    $phoneLink = static function (string $phone): string {
        if (!preg_match('/^[+\d\s().-]+$/D', $phone)) throw new RuntimeException('Enter phone numbers with country codes.');
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) < 8 || strlen($digits) > 15) throw new RuntimeException('Phone numbers must have 8 to 15 digits including country code.');
        return '+' . $digits;
    };
    $phone = $text('contact_phone');
    $primaryLink = $phoneLink($phone);
    $otherPhones = [];
    foreach (preg_split('/\r?\n/', $text('contact_other_phones')) ?: [] as $other) {
        $other = trim($other);
        if ($other !== '') $otherPhones[] = ['phone' => $other, 'phoneLink' => $phoneLink($other)];
    }
    $whatsapp = ltrim($phoneLink($text('contact_whatsapp')), '+');
    $email = $text('contact_email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email address.');
    $address = $text('contact_address');
    if (strlen($address) > 1500) throw new RuntimeException('Keep the address within 1500 characters.');
    return ['phone' => $phone, 'phoneLink' => $primaryLink, 'otherPhones' => $otherPhones,
        'whatsapp' => $whatsapp, 'email' => $email, 'address' => $address];
}

function site_contact_save(PDO $db, array $settings): void
{
    $db->exec('CREATE TABLE IF NOT EXISTS site_settings (setting_key VARCHAR(64) PRIMARY KEY, payload LONGTEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $save = $db->prepare('INSERT INTO site_settings (setting_key, payload) VALUES (?, ?) ON DUPLICATE KEY UPDATE payload = VALUES(payload)');
    $save->execute(['contact', json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)]);
}
