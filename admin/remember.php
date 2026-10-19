<?php
declare(strict_types=1);

const ADMIN_REMEMBER_COOKIE = 'reach_admin_remember';
const ADMIN_REMEMBER_LIFETIME = 30 * 24 * 60 * 60;

function admin_remember_cookie(string $token, int $expires): void
{
    setcookie(ADMIN_REMEMBER_COOKIE, $token, [
        'expires' => $expires,
        'path' => rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/admin/index.php')), '/') . '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function admin_remember_table(PDO $db): void
{
    $db->exec('CREATE TABLE IF NOT EXISTS admin_remember_tokens (
        token_hash CHAR(64) PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        expires_at BIGINT UNSIGNED NOT NULL,
        INDEX (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
}

function admin_remember_forget(?PDO $db): void
{
    $token = (string) ($_COOKIE[ADMIN_REMEMBER_COOKIE] ?? '');
    if ($db && preg_match('/^[a-f0-9]{64}$/D', $token)) {
        admin_remember_table($db);
        $delete = $db->prepare('DELETE FROM admin_remember_tokens WHERE token_hash = ?');
        $delete->execute([hash('sha256', $token)]);
    }
    admin_remember_cookie('', time() - 3600);
    unset($_COOKIE[ADMIN_REMEMBER_COOKIE]);
}

function admin_remember_save(PDO $db, array $user): void
{
    admin_remember_forget($db);
    admin_remember_table($db);
    $db->exec('DELETE FROM admin_remember_tokens WHERE expires_at <= ' . time());
    $token = bin2hex(random_bytes(32));
    $expires = time() + ADMIN_REMEMBER_LIFETIME;
    $insert = $db->prepare('INSERT INTO admin_remember_tokens (token_hash, user_id, password_hash, expires_at) VALUES (?, ?, ?, ?)');
    $insert->execute([hash('sha256', $token), $user['id'], $user['password_hash'], $expires]);
    admin_remember_cookie($token, $expires);
}

function admin_remember_restore(?PDO $db): void
{
    if (!empty($_SESSION['admin']) || !isset($_COOKIE[ADMIN_REMEMBER_COOKIE])) {
        return;
    }
    $token = (string) $_COOKIE[ADMIN_REMEMBER_COOKIE];
    if (!$db || !preg_match('/^[a-f0-9]{64}$/D', $token)) {
        admin_remember_forget(null);
        return;
    }
    admin_remember_table($db);
    $find = $db->prepare("SELECT u.username FROM admin_remember_tokens t
        INNER JOIN users u ON u.id = t.user_id
        WHERE t.token_hash = ? AND t.expires_at > ? AND u.role = 'admin'
        AND BINARY t.password_hash = BINARY u.password_hash LIMIT 1");
    $find->execute([hash('sha256', $token), time()]);
    $user = $find->fetch();
    if (!$user) {
        admin_remember_forget($db);
        return;
    }
    session_regenerate_id(true);
    $_SESSION['admin'] = true;
    $_SESSION['username'] = $user['username'];
    unset($_SESSION['csrf']);
}
