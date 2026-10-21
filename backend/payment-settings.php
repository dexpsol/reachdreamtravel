<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';

function site_payment_settings(array $defaults): array
{
    $db = app_db();
    if (!$db) return $defaults;
    try {
        $query = $db->prepare('SELECT payload FROM site_settings WHERE setting_key = ?');
        $query->execute(['payment']);
        $saved = json_decode((string) $query->fetchColumn(), true);
        if (!is_array($saved)) return $defaults;
        foreach (['bank', 'upi'] as $group) {
            foreach ($defaults[$group] as $key => $value) {
                if (is_string($saved[$group][$key] ?? null)) $defaults[$group][$key] = $saved[$group][$key];
            }
        }
        if (is_string($saved['location'] ?? null)) $defaults['location'] = $saved['location'];
        $defaults['demo'] = (bool) ($saved['demo'] ?? true);
        return $defaults;
    } catch (PDOException $exception) {
        return $defaults;
    }
}

function site_payment_from_post(array $post, array $current): array
{
    $text = static function (string $key, int $max = 150) use ($post): string {
        $value = $post[$key] ?? '';
        if (!is_string($value) || strlen($value) > $max) throw new RuntimeException('Enter valid payment details within the field limits.');
        return trim($value);
    };
    $settings = ['demo' => ($post['payment_demo'] ?? '') === '1', 'location' => $text('payment_location', 1500), 'bank' => [], 'upi' => []];
    foreach (array_keys($current['bank']) as $key) $settings['bank'][$key] = $text('payment_bank_' . $key);
    foreach (['id', 'name', 'phone'] as $key) $settings['upi'][$key] = $text('payment_upi_' . $key);
    $settings['upi']['qrImage'] = !empty($post['payment_remove_qr']) ? '' : $current['upi']['qrImage'];
    $bank = $settings['bank'];
    if ($bank['accountNumber'] !== '' && !preg_match('/^[0-9]{6,34}$/D', $bank['accountNumber'])) throw new RuntimeException('Enter an account number with 6 to 34 digits.');
    if ($bank['ifsc'] !== '' && !preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/D', $bank['ifsc'])) throw new RuntimeException('Enter a valid 11-character IFSC code in uppercase.');
    $upi = $settings['upi'];
    if ($upi['id'] !== '' && !preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+$/D', $upi['id'])) throw new RuntimeException('Enter a valid UPI ID, such as yourname@yourbank.');
    if ($upi['phone'] !== '' && !preg_match('/^\+?[0-9 ()-]{10,25}$/D', $upi['phone'])) throw new RuntimeException('Enter a valid Google Pay phone number.');
    if (!$settings['demo']) {
        foreach (['accountName', 'bankName', 'accountNumber', 'accountType', 'ifsc'] as $key) {
            if ($bank[$key] === '') throw new RuntimeException('Fill in the bank account details before turning off demo mode.');
        }
        if (preg_match('/^0+$/D', $bank['accountNumber']) || str_starts_with($bank['ifsc'], 'DEMO') || str_ends_with(strtolower($upi['id']), '@demo')) throw new RuntimeException('Replace the dummy account and UPI details before turning off demo mode.');
    }
    return $settings;
}

function site_payment_qr_upload(array $file, string $root): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('QR image upload failed. Choose the image again.');
    if ((int) ($file['size'] ?? 0) > 6 * 1024 * 1024) throw new RuntimeException('The QR image must be 6 MB or smaller.');
    $info = @getimagesize((string) ($file['tmp_name'] ?? ''));
    $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? '';
    if ($extension === '') throw new RuntimeException('Upload a JPG, PNG or WebP QR image.');
    $relative = 'assets/images/payments/upi-' . bin2hex(random_bytes(12)) . '.' . $extension;
    if (!move_uploaded_file((string) $file['tmp_name'], $root . '/' . $relative)) throw new RuntimeException('Could not save the QR image. Check the payment image folder permissions.');
    return $relative;
}

function site_payment_save(PDO $db, array $settings): void
{
    $db->exec('CREATE TABLE IF NOT EXISTS site_settings (setting_key VARCHAR(64) PRIMARY KEY, payload LONGTEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $save = $db->prepare('INSERT INTO site_settings (setting_key, payload) VALUES (?, ?) ON DUPLICATE KEY UPDATE payload = VALUES(payload)');
    $save->execute(['payment', json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)]);
}
