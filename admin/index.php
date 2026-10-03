<?php
declare(strict_types=1);
session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']), 'samesite' => 'Strict']);
session_start();
require_once dirname(__DIR__) . '/includes/data.php';
require_once dirname(__DIR__) . '/backend/database.php';

$root = dirname(__DIR__);
$db = app_db();
$setupNeeded = true;
if ($db) {
    $db->exec('CREATE TABLE IF NOT EXISTS users (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, role VARCHAR(32) NOT NULL DEFAULT \'admin\', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $setupNeeded = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0;
}
$categories = ['nature' => 'Nature', 'heritage' => 'Heritage', 'himachal' => 'Himachal tours', 'trekking' => 'Trekking', 'camping' => 'Camping', 'religious' => 'Religious / Temple', 'solo' => 'Solo trips', 'new-year' => 'New Year', 'group' => 'Group tours', 'adventure' => 'Adventure / Activities', 'special' => 'Special interest', 'hills' => 'Hill stations', 'road' => 'Long road trips', 'north' => 'Northern India'];
$imageFiles = [];
foreach (['destinations', 'stays'] as $imageFolder) {
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $imageExtension) {
        $imageFiles = array_merge($imageFiles, glob($root . '/assets/images/' . $imageFolder . '/*.' . $imageExtension) ?: []);
    }
}
$imageNames = array_values(array_unique(array_map(static fn($path) => pathinfo($path, PATHINFO_FILENAME), $imageFiles)));
$taxiImages = array_values(array_map(static fn($path) => 'assets/images/car/' . basename($path), glob($root . '/assets/images/car/fleet-*.jpg') ?: []));
$message = (string) ($_SESSION['notice'] ?? '');
$error = '';
unset($_SESSION['notice']);

function admin_h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function admin_csrf(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function admin_redirect(): void
{
    header('Location: index.php');
    exit;
}
function admin_lines(string $value): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $value) ?: []), static fn($line) => $line !== ''));
}
function admin_slug(string $value): string
{
    return trim(strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $value)), '-');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(admin_csrf(), (string) ($_POST['csrf'] ?? ''))) {
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif (isset($_POST['setup'])) {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if (!$db) {
            $error = 'Cannot connect to the database. Start MySQL and refresh this page.';
        } elseif (!$setupNeeded) {
            $error = 'Admin setup is already complete.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,100}$/', $username)) {
            $error = 'Choose a username with 3–100 letters, numbers, dots, dashes, or underscores.';
        } elseif (strlen($password) < 12 || $password !== (string) ($_POST['confirm_password'] ?? '')) {
            $error = 'Use matching passwords with at least 12 characters.';
        } else {
            $insertUser = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')");
            try {
                $insertUser->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
                session_regenerate_id(true);
                $_SESSION['admin'] = true;
                $_SESSION['username'] = $username;
                admin_redirect();
            } catch (Throwable $exception) {
                $error = 'Could not create the admin account. The username may already be taken.';
            }
        }
    } elseif (isset($_POST['login'])) {
        $username = trim((string) ($_POST['username'] ?? ''));
        $findUser = $db ? $db->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1') : null;
        if ($findUser) $findUser->execute([$username]);
        $user = $findUser ? $findUser->fetch() : false;
        if ($user && $user['role'] === 'admin' && password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['username'] = $user['username'];
            admin_redirect();
        }
        $error = 'The password was not accepted.';
    } elseif (isset($_POST['logout'])) {
        $_SESSION = [];
        session_destroy();
        admin_redirect();
    } elseif (!empty($_SESSION['admin'])) {
        if (isset($_POST['initialize_database'])) {
            try {
                $error = app_db_initialize($packages ?: $defaultPackages, $vehicles ?: $defaultVehicles, $destinationGroups ?: $defaultDestinationGroups);
                if ($error === '') {
                    $_SESSION['notice'] = 'Database is ready. Current packages and taxis were imported.';
                    admin_redirect();
                }
            } catch (Throwable $exception) {
                $error = 'Database setup failed. Check MySQL is running and the configured account can create databases and tables.';
            }
        } else {
            $kind = (string) ($_POST['kind'] ?? 'package');
            $table = 'packages';
            $action = (string) ($_POST['action'] ?? 'save');
            $slug = admin_slug((string) ($_POST['slug'] ?? ''));
            try {
                if (!in_array($kind, ['package', 'taxi', 'destination'], true)) {
                    throw new RuntimeException('Choose a valid content type.');
                }
                $table = ['package' => 'packages', 'taxi' => 'taxis', 'destination' => 'destination_groups'][$kind];
                $db = app_db();
                if (!$db) throw new RuntimeException('Start MySQL and use Initialize database before saving records.');
                if ($action === 'delete') {
                    if ($table === 'packages') {
                        $db->exec('CREATE TABLE IF NOT EXISTS hidden_packages (slug VARCHAR(190) PRIMARY KEY) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
                        $hide = $db->prepare('INSERT IGNORE INTO hidden_packages (slug) VALUES (?)');
                        $hide->execute([$slug]);
                    }
                    $statement = $db->prepare('DELETE FROM `' . $table . '` WHERE slug = ?');
                    $statement->execute([$slug]);
                    $_SESSION['notice'] = ucfirst($kind) . ' deleted.';
                    admin_redirect();
                }
                $name = trim((string) ($_POST['name'] ?? $_POST['title'] ?? ''));
                if ($slug === '') $slug = admin_slug($name);
                if ($slug === '' || $name === '') throw new RuntimeException('Add a name and a valid URL slug.');
                if ($kind === 'taxi') {
                    $image = (string) ($_POST['image'] ?? '');
                    if (!in_array($image, $taxiImages, true)) throw new RuntimeException('Choose one of the available fleet photos.');
                    $record = [
                        'name' => $name, 'type' => trim((string) ($_POST['type'] ?? 'car')), 'tag' => trim((string) ($_POST['tag'] ?? '')),
                        'group' => trim((string) ($_POST['group'] ?? 'small')), 'image' => $image, 'alt' => trim((string) ($_POST['alt'] ?? $name)),
                        'summary' => trim((string) ($_POST['summary'] ?? '')), 'details' => trim((string) ($_POST['details'] ?? '')),
                        'seats' => trim((string) ($_POST['seats'] ?? '')), 'bags' => trim((string) ($_POST['bags'] ?? '')),
                        'extra' => [trim((string) ($_POST['extra_icon'] ?? 'fa-snowflake')), trim((string) ($_POST['extra_label'] ?? 'AC'))],
                        'best' => trim((string) ($_POST['best'] ?? '')), 'terrain' => trim((string) ($_POST['terrain'] ?? '')),
                        'features' => admin_lines((string) ($_POST['features'] ?? '')), 'routes' => admin_lines((string) ($_POST['routes'] ?? '')),
                    ];
                } elseif ($kind === 'destination') {
                    $items = admin_lines((string) ($_POST['items'] ?? ''));
                    $image = (string) ($_POST['image'] ?? '');
                    if (!$items) throw new RuntimeException('Add at least one place or attraction.');
                    if (!in_array($image, $imageNames, true)) throw new RuntimeException('Choose an image from the destination image library.');
                    $record = [
                        'title' => $name,
                        'image' => $image,
                        'description' => trim((string) ($_POST['description'] ?? '')),
                        'items' => $items,
                    ];
                    if ($record['description'] === '') throw new RuntimeException('Add a short description for this destination group.');
                } else {
                    $categoryValues = $_POST['cat'] ?? ['heritage'];
                    if (!is_array($categoryValues)) $categoryValues = [$categoryValues];
                    $categoryValues = array_values(array_unique(array_filter(array_map('strval', $categoryValues), static fn($category) => $category !== '')));
                    $previousSlug = admin_slug((string) ($_POST['old_slug'] ?? ''));
                    $previousPackage = $packages[$previousSlug] ?? $packages[$slug] ?? [];
                    $image = $previousPackage['image'] ?? 'hero-himachal';
                    if (!$categoryValues || array_diff($categoryValues, array_keys($categories))) throw new RuntimeException('Choose at least one valid package category.');
                    $highlights = admin_lines((string) ($_POST['highlights'] ?? ''));
                    $days = [];
                    foreach (admin_lines((string) ($_POST['days'] ?? '')) as $line) {
                        $parts = explode('|', $line, 2);
                        if (count($parts) === 2 && trim($parts[0]) !== '' && trim($parts[1]) !== '') $days[] = [trim($parts[0]), trim($parts[1])];
                    }
                    if (!$highlights || !$days) throw new RuntimeException('Add at least one highlight and one day formatted as “Title | details”.');
                    $regularPriceInput = trim((string) ($_POST['price_regular'] ?? ''));
                    $discountPriceInput = trim((string) ($_POST['price_discount'] ?? ''));
                    foreach (['Regular price' => $regularPriceInput, 'Discount price' => $discountPriceInput] as $priceLabel => $priceInput) {
                        if ($priceInput !== '' && !preg_match('/^\d{1,10}$/', $priceInput)) {
                            throw new RuntimeException($priceLabel . ' must be a whole amount in INR, or left blank.');
                        }
                    }
                    $regularPrice = $regularPriceInput === '' ? null : (int) $regularPriceInput;
                    $discountPrice = $discountPriceInput === '' ? null : (int) $discountPriceInput;
                    if ($discountPrice !== null && $regularPrice === null) {
                        throw new RuntimeException('Enter a regular price before adding a discount price.');
                    }
                    if ($regularPrice !== null && $discountPrice !== null && $discountPrice >= $regularPrice) {
                        throw new RuntimeException('Discount price must be lower than regular price.');
                    }
                    $record = [
                        'title' => $name, 'label' => trim((string) ($_POST['label'] ?? '')), 'cat' => implode(' ', $categoryValues),
                        'duration' => trim((string) ($_POST['duration'] ?? '')) ?: 'Custom duration', 'short' => $previousPackage['short'] ?? 'Flexible',
                        'popular' => !empty($_POST['popular']), 'image' => $image, 'image_customized' => $previousPackage['image_customized'] ?? false,
                        'price_regular' => $regularPrice, 'price_discount' => $discountPrice,
                        'season' => trim((string) ($_POST['season'] ?? 'All year')), 'start' => trim((string) ($_POST['start'] ?? 'Your home or hotel')),
                        'end' => trim((string) ($_POST['end'] ?? 'Your home or hotel')), 'route' => trim((string) ($_POST['route'] ?? '')),
                        'overview' => trim((string) ($_POST['overview'] ?? '')), 'highlights' => $highlights, 'days' => $days,
                    ];
                }
                $oldSlug = admin_slug((string) ($_POST['old_slug'] ?? ''));
                if ($oldSlug !== '' && $oldSlug !== $slug) {
                    if ($kind === 'package') {
                        $db->exec('CREATE TABLE IF NOT EXISTS hidden_packages (slug VARCHAR(190) PRIMARY KEY) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
                        $hide = $db->prepare('INSERT IGNORE INTO hidden_packages (slug) VALUES (?)');
                        $hide->execute([$oldSlug]);
                    }
                    $removeOld = $db->prepare('DELETE FROM `' . $table . '` WHERE slug = ?');
                    $removeOld->execute([$oldSlug]);
                }
                if ($kind === 'package') {
                    $db->exec('CREATE TABLE IF NOT EXISTS hidden_packages (slug VARCHAR(190) PRIMARY KEY) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
                    $unhide = $db->prepare('DELETE FROM hidden_packages WHERE slug = ?');
                    $unhide->execute([$slug]);
                }
                $statement = $db->prepare('INSERT INTO `' . $table . '` (slug, payload) VALUES (?, ?) ON DUPLICATE KEY UPDATE payload = VALUES(payload)');
                $statement->execute([$slug, json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)]);
                $_SESSION['notice'] = ucfirst($kind) . ' saved.';
                admin_redirect();
            } catch (Throwable $exception) {
                $error = $exception instanceof RuntimeException ? $exception->getMessage() : 'Could not save to the database. Initialize it first and check MySQL permissions.';
            }
        }
    }
}

$authenticated = !empty($_SESSION['admin']);
$requestedTab = (string) ($_GET['tab'] ?? 'packages');
$activeTab = in_array($requestedTab, ['packages', 'taxis', 'destinations'], true) ? $requestedTab : 'packages';
$editingSlug = (string) ($_GET['edit'] ?? '');
$editingKind = (string) ($_GET['kind'] ?? 'package');
$packageForm = $editingKind === 'package' ? ($packages[$editingSlug] ?? []) : [];
$destinationForm = $editingKind === 'destination' ? ($destinationGroups[$editingSlug] ?? []) : [];
$taxiForm = [];
foreach ($vehicles as $vehicle) if (($vehicle['_slug'] ?? admin_slug($vehicle['name'])) === $editingSlug && $editingKind === 'taxi') $taxiForm = $vehicle;
$selectedTab = ['package' => 'packages', 'taxi' => 'taxis', 'destination' => 'destinations'][$editingKind] ?? $activeTab;
$dbPackages = app_db_catalog('packages');
$dbTaxis = app_db_catalog('taxis');
$dbDestinationGroups = app_db_catalog('destination_groups');
$dbReady = app_db() !== null && !empty($dbPackages) && !empty($dbTaxis) && $dbDestinationGroups !== null;
$loginView = !$setupNeeded && !$authenticated;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Travel Dashboard | Reach Dream Travel</title>
  <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="../style.css">
  <style>
    body{background:#f5f6f3;color:#24313a}.dash-header{background:#123b3a;color:#fff;padding:16px 24px}.dash-head-inner{max-width:1200px;margin:auto;display:flex;align-items:center;justify-content:space-between;gap:18px}.dash-brand{color:#fff;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:11px}.dash-brand img{display:block;width:44px;height:44px}.dash-brand small{display:block;font-size:12px;font-weight:400;opacity:.76}.dash-main{max-width:1200px;margin:28px auto;padding:0 18px}.dash-top{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:20px}.dash-top h1{font-size:30px;margin:0}.dash-top p{margin:5px 0 0;color:#68756f}.dash-status{padding:12px 15px;background:#fff;border:1px solid #dce2df;border-radius:5px;margin-bottom:18px}.dash-status.good{border-left:4px solid #25805e}.dash-status.bad{border-left:4px solid #bd483f}.dash-tabs{display:flex;gap:8px;border-bottom:1px solid #d7dfdb;margin-bottom:20px}.dash-tabs a{padding:12px 16px;text-decoration:none;color:#51605b;border-bottom:3px solid transparent;font-weight:600}.dash-tabs a.active{color:#146553;border-color:#cf9a3c}.dash-panel{background:#fff;border:1px solid #dce2df;border-radius:6px;padding:22px;margin-bottom:22px}.dash-panel h2{font-size:21px;margin:0 0 18px}.dash-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.dash-grid label{display:block;font-size:13px;font-weight:600;color:#44534d}.dash-grid input,.dash-grid textarea,.dash-grid select{margin-top:5px;width:100%;border:1px solid #cbd4d0;border-radius:4px;padding:10px;color:#24313a;background:#fff}.dash-grid textarea{min-height:96px}.dash-wide{grid-column:1/-1}.dash-list{border-top:1px solid #e5e9e7}.dash-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:13px 0;border-bottom:1px solid #e5e9e7}.dash-row-main{display:flex;align-items:center;gap:13px;min-width:0}.dash-thumb{width:68px;height:52px;object-fit:cover;border-radius:3px;background:#eee}.dash-row small{display:block;color:#74817b;margin-top:2px}.dash-actions{display:flex;gap:7px;flex-shrink:0}.dash-actions form{margin:0}.dash-actions .btn{border-radius:4px}.dash-note{color:#69766f;font-size:13px}.dash-empty{padding:16px 0;color:#69766f}.setup-box{max-width:520px;margin:70px auto}.dash-section-title{display:flex;justify-content:space-between;align-items:center;gap:14px}.dash-field-title{display:block;font-size:13px;font-weight:600;color:#44534d}.category-picker{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;margin-top:5px}.category-picker label{display:flex;align-items:center;gap:8px;padding:7px 9px;border:1px solid #dce2df;border-radius:4px;background:#fff;font-weight:500;cursor:pointer}.category-picker label:has(input:checked){border-color:#28705c;background:#f0f7f3}.category-picker input{width:16px;height:16px;margin:0;accent-color:#28705c;flex:0 0 auto}@media(max-width:720px){.dash-top{display:block}.dash-top .btn{margin-top:14px}.dash-grid{grid-template-columns:1fr}.dash-wide{grid-column:auto}.dash-row{align-items:flex-start;flex-direction:column}.dash-actions{flex-wrap:wrap}.dash-tabs a{padding:10px 12px}.dash-header{padding:14px}.dash-panel{padding:17px}}
    .dash-grid input::placeholder,.dash-grid textarea::placeholder{color:#89948f;opacity:1}.dash-note{display:block;margin-top:5px}
    .login-page{min-height:100vh;background:#f1f4f1;color:#203331}.login-page .dash-header{display:none}.login-main{display:grid;place-items:center;width:100%;max-width:none;min-height:100vh;margin:0;padding:32px}.login-layout{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(380px,.85fr);width:min(1120px,100%);min-height:min(700px,calc(100vh - 64px));overflow:hidden;background:#fff;border:1px solid #e0e7e2;border-radius:8px;box-shadow:0 24px 70px rgba(22,55,47,.12)}.login-visual{position:relative;isolation:isolate;min-height:640px;overflow:hidden;background:#17413b;color:#fff}.login-visual>img{position:absolute;z-index:-2;inset:0;width:100%;height:100%;object-fit:cover;object-position:center}.login-visual:after{position:absolute;z-index:-1;inset:0;background:rgba(9,38,35,.58);content:''}.login-visual-content{display:flex;flex-direction:column;justify-content:space-between;min-height:inherit;padding:38px 42px}.login-brand{display:inline-flex;align-items:center;gap:14px;width:max-content;color:#fff;text-decoration:none;font:700 18px/1.25 Montserrat,sans-serif}.login-brand img{flex:0 0 auto}.login-brand small{display:block;margin-top:6px;color:#e6c781;font:600 11px/1.4 'DM Sans',sans-serif}.login-intro{max-width:440px;margin-bottom:30px}.login-intro>span,.login-eyebrow{color:#a6c3b1;font-size:12px;font-weight:700}.login-intro h1{margin:14px 0;font:700 34px/1.2 Montserrat,sans-serif;color:#fff}.login-intro p{max-width:330px;margin:0;color:#e5eee9;font-size:16px;line-height:1.6}.login-panel{display:grid;place-items:center;padding:48px}.login-form-wrap{width:min(100%,360px)}.login-mark{display:grid;place-items:center;width:44px;height:44px;margin-bottom:30px;border-radius:6px;background:#edf3ef;color:#17604d;font-size:18px}.login-eyebrow{margin:0 0 8px;color:#497562}.login-panel h2{margin:0;color:#203331;font:700 30px/1.2 Montserrat,sans-serif}.login-copy{margin:10px 0 30px;color:#697973;font-size:15px;line-height:1.5}.login-form{display:grid;gap:9px}.login-form label{margin-top:8px;color:#31433e;font-size:13px;font-weight:700}.login-form .form-control{min-height:48px;border-color:#cad6cf;border-radius:4px;padding:11px 13px;color:#203331}.login-form .form-control:focus{border-color:#26725e;box-shadow:0 0 0 3px rgba(38,114,94,.14)}.login-submit{display:flex;align-items:center;justify-content:center;gap:10px;min-height:48px;margin-top:14px;border:0;border-radius:4px;background:#174f43;color:#fff;font-weight:700}.login-submit:hover,.login-submit:focus-visible{background:#103d34;color:#fff}.login-back{display:inline-flex;align-items:center;gap:8px;margin-top:28px;color:#526a60;font-size:13px;font-weight:600;text-decoration:none}.login-back:hover{color:#174f43}.login-notice,.login-error{margin:0 0 18px;padding:11px 12px;border:1px solid #cde2d6;border-radius:4px;background:#f1f8f3;color:#275b40;font-size:13px}.login-error{border-color:#ebcbc7;background:#fff5f3;color:#993e36}@media(max-width:760px){.login-main{padding:14px}.login-layout{grid-template-columns:1fr;min-height:0}.login-visual{min-height:250px}.login-visual-content{min-height:250px;padding:24px}.login-intro{margin:30px 0 2px}.login-intro h1{font-size:26px;margin:8px 0}.login-intro p{font-size:14px}.login-brand img{width:44px;height:44px}.login-panel{padding:36px 24px 32px}.login-mark{margin-bottom:20px}.login-copy{margin-bottom:22px}}@media(max-width:420px){.login-visual{min-height:220px}.login-visual-content{min-height:220px;padding:20px}.login-intro{margin-top:24px}.login-panel{padding:30px 20px}}
  </style>
</head>
<body<?= $loginView ? ' class="login-page"' : '' ?>>
<?php if (!$loginView): ?><header class="dash-header"><div class="dash-head-inner"><a class="dash-brand" href="../index.php"><img src="../assets/logo.svg" alt="" width="44" height="44"> <span>Reach Dream Travel <small>/ Dashboard</small></span></a><?php if ($authenticated): ?><form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><button class="btn btn-outline-light btn-sm" name="logout" value="1">Sign out</button></form><?php endif; ?></div></header><?php endif; ?>
<main class="dash-main<?= $loginView ? ' login-main' : '' ?>">
<?php if ($message && !$loginView): ?><div class="dash-status good"><?= admin_h($message) ?></div><?php endif; ?>
<?php if ($error && !$loginView): ?><div class="dash-status bad"><?= admin_h($error) ?></div><?php endif; ?>
<?php if ($setupNeeded): ?>
  <section class="dash-panel setup-box"><h1 class="h3">Create dashboard account</h1><p>Create the first admin user. Passwords require at least 12 characters.</p><form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><label class="d-block mb-3">Username<input class="form-control mt-1" type="text" name="username" minlength="3" maxlength="100" required autocomplete="username"></label><label class="d-block mb-3">Password<input class="form-control mt-1" type="password" name="password" minlength="12" required autocomplete="new-password"></label><label class="d-block mb-3">Confirm password<input class="form-control mt-1" type="password" name="confirm_password" minlength="12" required autocomplete="new-password"></label><button class="btn btn-success" name="setup" value="1">Create admin user</button></form></section>
<?php elseif (!$authenticated): ?>
  <div class="login-layout">
    <section class="login-visual" aria-label="Reach Dream Travel">
      <img src="../assets/images/destinations/hero-himachal.jpg" alt="" fetchpriority="high">
      <div class="login-visual-content">
        <a class="login-brand" href="../index.php"><img src="../assets/logo.svg" alt="" width="170px" height="auto"><span><small>TRAVEL DASHBOARD</small></span></a>
        <div class="login-intro"><span>ADMIN ACCESS</span><h1>Your journeys,<br>all in one place.</h1><p>Manage packages, destination lists and vehicles for every trip ahead.</p></div>
      </div>
    </section>
    <section class="login-panel" aria-labelledby="loginTitle">
      <div class="login-form-wrap">
        <span class="login-mark" aria-hidden="true"><i class="fa-solid fa-route"></i></span>
        <p class="login-eyebrow">Welcome back</p>
        <h2 id="loginTitle">Sign in</h2>
        <p class="login-copy">Enter your dashboard credentials to continue.</p>
<?php if ($message): ?><div class="login-notice" role="status"><?= admin_h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="login-error" role="alert"><?= admin_h($error) ?></div><?php endif; ?>
        <form method="post" class="login-form">
          <input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>">
          <label for="loginUsername">Username</label>
          <input class="form-control" id="loginUsername" type="text" name="username" required autocomplete="username">
          <label for="loginPassword">Password</label>
          <input class="form-control" id="loginPassword" type="password" name="password" required autocomplete="current-password">
          <button class="btn login-submit" name="login" value="1">Sign in <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </form>
        <a class="login-back" href="../index.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to website</a>
      </div>
    </section>
  </div>
<?php else: ?>
  <div class="dash-top"><div><h1>Content dashboard</h1><p>Manage trips, destination collections and vehicles shown on your website.</p></div><?php if (!$dbReady): ?><form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><button class="btn btn-gold" name="initialize_database" value="1"><i class="fa-solid fa-database" aria-hidden="true"></i> Initialize database</button></form><?php endif; ?></div>
  <nav class="dash-tabs" aria-label="Content types"><a class="<?= $selectedTab === 'packages' ? 'active' : '' ?>" href="?tab=packages"><i class="fa-solid fa-route" aria-hidden="true"></i> Packages <span>(<?= count($packages) ?>)</span></a><a class="<?= $selectedTab === 'destinations' ? 'active' : '' ?>" href="?tab=destinations"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Destinations <span>(<?= count($destinationGroups) ?>)</span></a><a class="<?= $selectedTab === 'taxis' ? 'active' : '' ?>" href="?tab=taxis"><i class="fa-solid fa-car-side" aria-hidden="true"></i> Taxis <span>(<?= count($vehicles) ?>)</span></a></nav>
  <?php if ($selectedTab === 'packages'): ?>
    <section class="dash-panel" id="editor"><div class="dash-section-title"><h2><?= $packageForm ? 'Update package' : 'Add package' ?></h2><?php if ($packageForm): ?><a class="btn btn-outline-secondary btn-sm" href="?tab=packages">New package</a><?php endif; ?></div>
      <form method="post" data-package-form><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="package"><input type="hidden" name="action" value="save"><div class="dash-grid">
        <input type="hidden" name="old_slug" value="<?= admin_h($editingKind === 'package' ? $editingSlug : '') ?>">
        <label>Package title<input name="title" required value="<?= admin_h($packageForm['title'] ?? '') ?>" placeholder="e.g. Jaipur & Jaisalmer Heritage Trail"></label>
        <label>URL slug<input name="slug" data-package-slug value="<?= admin_h($editingKind === 'package' ? $editingSlug : '') ?>" placeholder="Generated from package title"></label>
        <label>Card label<input name="label" value="<?= admin_h($packageForm['label'] ?? '') ?>" placeholder="e.g. Desert & heritage"></label>
        <div class="dash-field"><span class="dash-field-title">Categories</span><div class="category-picker"><?php $selectedCategories = preg_split('/\s+/', (string) ($packageForm['cat'] ?? 'heritage')) ?: []; foreach ($categories as $key => $label): ?><label><input type="checkbox" name="cat[]" value="<?= admin_h($key) ?>"<?= in_array($key, $selectedCategories, true) ? ' checked' : '' ?>><span><?= admin_h($label) ?></span></label><?php endforeach; ?></div></div>
        <label>Duration<input name="duration" value="<?= admin_h($packageForm['duration'] ?? '') ?>" placeholder="e.g. 5 days · 4 nights"></label>
        <label>Regular price (INR)<input type="number" name="price_regular" min="0" step="1" inputmode="numeric" value="<?= admin_h($packageForm['price_regular'] ?? '') ?>" placeholder="Optional"></label>
        <label>Discount price (INR)<input type="number" name="price_discount" min="0" step="1" inputmode="numeric" value="<?= admin_h($packageForm['price_discount'] ?? '') ?>" placeholder="Optional"></label>
        <p class="dash-wide dash-note">Leave prices blank to hide them. If both are entered, the discount must be lower than the regular price.</p>
        <label>Best season<input name="season" value="<?= admin_h($packageForm['season'] ?? 'All year') ?>" placeholder="e.g. October to March"></label>
        <label>Route<input name="route" value="<?= admin_h($packageForm['route'] ?? '') ?>" placeholder="e.g. Jaipur · Jodhpur · Jaisalmer"></label>
        <label>Pickup point<input name="start" list="package-pickup-options" value="<?= admin_h($packageForm['start'] ?? 'Your home or hotel') ?>" placeholder="Choose or enter a pickup point"><datalist id="package-pickup-options"><option value="Your home or hotel"><option value="Airport"><option value="Railway station"><option value="Bus stand"></datalist><span class="dash-note">Where the trip begins, such as an airport, station or hotel.</span></label>
        <label>Drop-off point<input name="end" list="package-dropoff-options" value="<?= admin_h($packageForm['end'] ?? 'Your home or hotel') ?>" placeholder="Choose or enter a drop-off point"><datalist id="package-dropoff-options"><option value="Your home or hotel"><option value="Airport"><option value="Railway station"><option value="Bus stand"></datalist><span class="dash-note">Where guests are dropped off at the end of the trip.</span></label>
        <label class="dash-wide">Overview<textarea name="overview" required placeholder="Describe the feel of the trip, who it suits and what guests can expect."><?= admin_h($packageForm['overview'] ?? '') ?></textarea></label>
        <label class="dash-wide">Highlights<textarea name="highlights" required placeholder="Amber Fort at opening time&#10;Old-city food walk&#10;Sunset over the dunes"><?= admin_h(implode("\n", $packageForm['highlights'] ?? [])) ?></textarea><span class="dash-note">One highlight per line.</span></label>
        <label class="dash-wide">Day-by-day plan<textarea name="days" required placeholder="Day 1: Arrival | Airport pickup and hotel check-in&#10;Day 2: Jaipur | Fort visit and old-city walk"><?= admin_h(implode("\n", array_map(static fn($day) => $day[0] . ' | ' . $day[1], $packageForm['days'] ?? []))) ?></textarea><span class="dash-note">One day per line: Day title | day description</span></label>
        <label><span><input type="checkbox" name="popular" value="1"<?= !empty($packageForm['popular']) ? ' checked' : '' ?>> Mark as popular</span></label>
      </div><p class="mt-3 mb-0"><button class="btn btn-gold" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save package</button></p></form>
    </section>
    <section class="dash-panel"><h2>All packages</h2><div class="dash-list"><?php foreach ($packages as $slug => $package): $packageCategoryLabels = array_map(static fn($category) => $categories[$category] ?? $category, preg_split('/\s+/', trim((string) $package['cat'])) ?: []); ?><div class="dash-row"><div class="dash-row-main"><img class="dash-thumb" src="<?= admin_h('../' . img($package['image'], true)) ?>" alt=""><div><strong><?= admin_h($package['title']) ?></strong><small><?= admin_h($slug) ?> · <?= admin_h(implode(', ', $packageCategoryLabels)) ?><?php if (!empty($package['deprecated_duplicate_of'])): ?> · Saved duplicate of <?= admin_h($packages[$package['deprecated_duplicate_of']]['title'] ?? $package['deprecated_duplicate_of']) ?><?php endif; ?></small></div></div><div class="dash-actions"><a class="btn btn-sm btn-outline-secondary" href="?tab=packages&amp;kind=package&amp;edit=<?= rawurlencode($slug) ?>#editor">Edit</a><a class="btn btn-sm btn-outline-success" target="_blank" href="../package.php?slug=<?= rawurlencode($slug) ?>">View</a><form method="post" onsubmit="return confirm('Delete this package?')"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="package"><input type="hidden" name="action" value="delete"><input type="hidden" name="slug" value="<?= admin_h($slug) ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div></div><?php endforeach; ?></div></section>
  <?php elseif ($selectedTab === 'destinations'): ?>
    <section class="dash-panel" id="editor"><div class="dash-section-title"><h2><?= $destinationForm ? 'Update destination group' : 'Add destination group' ?></h2><?php if ($destinationForm): ?><a class="btn btn-outline-secondary btn-sm" href="?tab=destinations">New destination group</a><?php endif; ?></div>
      <form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="destination"><input type="hidden" name="action" value="save"><div class="dash-grid">
        <input type="hidden" name="old_slug" value="<?= admin_h($editingKind === 'destination' ? $editingSlug : '') ?>">
        <label>Group title<input name="name" required value="<?= admin_h($destinationForm['title'] ?? '') ?>" placeholder="e.g. Punjab"></label>
        <label>URL slug<input name="slug" value="<?= admin_h($editingKind === 'destination' ? $editingSlug : '') ?>" placeholder="Generated from group title"></label>
        <label>Destination image<select name="image" required><option value="">Choose an image from the library</option><?php foreach ($imageNames as $image): ?><option value="<?= admin_h($image) ?>"<?= ($destinationForm['image'] ?? '') === $image ? ' selected' : '' ?>><?= admin_h($image) ?></option><?php endforeach; ?></select></label>
        <label class="dash-wide">Description<textarea name="description" required placeholder="A short introduction to this region or attraction list."><?= admin_h($destinationForm['description'] ?? '') ?></textarea></label>
        <label class="dash-wide">Places or attractions<textarea name="items" required placeholder="One destination or attraction per line."><?= admin_h(implode("\n", $destinationForm['items'] ?? [])) ?></textarea><span class="dash-note">One place per line. These appear on the Destinations page.</span></label>
      </div><p class="mt-3 mb-0"><button class="btn btn-gold" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save destination group</button></p></form>
    </section>
    <section class="dash-panel"><h2>Destination groups</h2><div class="dash-list"><?php foreach ($destinationGroups as $slug => $group): ?><div class="dash-row"><div class="dash-row-main"><img class="dash-thumb" src="<?= admin_h('../' . img($group['image'], true)) ?>" alt=""><div><strong><?= admin_h($group['title']) ?></strong><small><?= admin_h($slug) ?> · <?= admin_h(implode(', ', $group['items'])) ?></small></div></div><div class="dash-actions"><a class="btn btn-sm btn-outline-secondary" href="?tab=destinations&amp;kind=destination&amp;edit=<?= rawurlencode($slug) ?>#editor">Edit</a><form method="post" onsubmit="return confirm('Delete this destination group?')"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="destination"><input type="hidden" name="action" value="delete"><input type="hidden" name="slug" value="<?= admin_h($slug) ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div></div><?php endforeach; ?></div></section>
  <?php else: ?>
    <section class="dash-panel" id="editor"><div class="dash-section-title"><h2><?= $taxiForm ? 'Update taxi' : 'Add taxi' ?></h2><?php if ($taxiForm): ?><a class="btn btn-outline-secondary btn-sm" href="?tab=taxis">New taxi</a><?php endif; ?></div>
      <form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="taxi"><input type="hidden" name="action" value="save"><div class="dash-grid">
        <input type="hidden" name="old_slug" value="<?= admin_h($taxiForm['_slug'] ?? '') ?>"><label>Taxi name<input name="name" required value="<?= admin_h($taxiForm['name'] ?? '') ?>"></label>
        <label>URL slug<input name="slug" value="<?= admin_h($editingKind === 'taxi' ? $editingSlug : '') ?>" placeholder="made-from-name"></label>
        <label>Type<select name="type"><?php foreach (['car' => 'Car', 'suv' => 'SUV / MUV', 'group' => 'Group vehicle'] as $key => $value): ?><option value="<?= $key ?>"<?= ($taxiForm['type'] ?? 'car') === $key ? ' selected' : '' ?>><?= $value ?></option><?php endforeach; ?></select></label>
        <label>Fleet label<input name="tag" value="<?= admin_h($taxiForm['tag'] ?? '') ?>"></label>
        <label>Group size<select name="group"><?php foreach (['small' => '1–4', 'medium' => '5–7', 'large' => '8–17'] as $key => $value): ?><option value="<?= $key ?>"<?= ($taxiForm['group'] ?? 'small') === $key ? ' selected' : '' ?>><?= $value ?></option><?php endforeach; ?></select></label>
        <label>Fleet photo<select name="image"><?php foreach ($taxiImages as $image): ?><option value="<?= admin_h($image) ?>"<?= ($taxiForm['image'] ?? '') === $image ? ' selected' : '' ?>><?= admin_h(basename($image, '.jpg')) ?></option><?php endforeach; ?></select></label>
        <label>Seats<input name="seats" value="<?= admin_h($taxiForm['seats'] ?? '') ?>"></label>
        <label>Luggage<input name="bags" value="<?= admin_h($taxiForm['bags'] ?? '') ?>"></label>
        <label>Comfort icon<input name="extra_icon" value="<?= admin_h($taxiForm['extra'][0] ?? 'fa-snowflake') ?>"></label>
        <label>Comfort label<input name="extra_label" value="<?= admin_h($taxiForm['extra'][1] ?? 'AC') ?>"></label>
        <label>Best for<input name="best" value="<?= admin_h($taxiForm['best'] ?? '') ?>"></label>
        <label>Suitable terrain<input name="terrain" value="<?= admin_h($taxiForm['terrain'] ?? '') ?>"></label>
        <label>Short summary<input name="summary" value="<?= admin_h($taxiForm['summary'] ?? '') ?>"></label>
        <label>Image description<input name="alt" value="<?= admin_h($taxiForm['alt'] ?? '') ?>"></label>
        <label class="dash-wide">Details<textarea name="details"><?= admin_h($taxiForm['details'] ?? '') ?></textarea></label>
        <label class="dash-wide">Features<textarea name="features"><?= admin_h(implode("\n", $taxiForm['features'] ?? [])) ?></textarea><span class="dash-note">One feature per line.</span></label>
        <label class="dash-wide">Recommended routes<textarea name="routes"><?= admin_h(implode("\n", $taxiForm['routes'] ?? [])) ?></textarea><span class="dash-note">One route per line.</span></label>
      </div><p class="mt-3 mb-0"><button class="btn btn-gold" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save taxi</button></p></form>
    </section>
    <section class="dash-panel"><h2>All taxis</h2><div class="dash-list"><?php foreach ($vehicles as $vehicle): $slug = $vehicle['_slug'] ?? admin_slug($vehicle['name']); ?><div class="dash-row"><div class="dash-row-main"><img class="dash-thumb" src="<?= admin_h('../' . $vehicle['image']) ?>" alt=""><div><strong><?= admin_h($vehicle['name']) ?></strong><small><?= admin_h($vehicle['tag']) ?> · <?= admin_h($vehicle['seats']) ?> seats · <?= admin_h($vehicle['best']) ?></small></div></div><div class="dash-actions"><a class="btn btn-sm btn-outline-secondary" href="?tab=taxis&amp;kind=taxi&amp;edit=<?= rawurlencode($slug) ?>#editor">Edit</a><a class="btn btn-sm btn-outline-success" href="../vehicles.php#fleet">View fleet</a><form method="post" onsubmit="return confirm('Delete this taxi?')"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="taxi"><input type="hidden" name="action" value="delete"><input type="hidden" name="slug" value="<?= admin_h($slug) ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div></div><?php endforeach; ?></div></section>
  <?php endif; ?>
<?php endif; ?>
</main>
<?php if ($selectedTab === 'packages'): ?>
<script>
(() => {
  const form = document.querySelector('[data-package-form]');
  if (!form) return;
  const title = form.querySelector('[name="title"]');
  const slug = form.querySelector('[data-package-slug]');
  let slugWasEdited = false;

  const makeSlug = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  slug.addEventListener('input', () => { slugWasEdited = true; });
  title.addEventListener('input', () => { if (!slugWasEdited) slug.value = makeSlug(title.value); });
})();
</script>
<?php endif; ?>
</body></html>
