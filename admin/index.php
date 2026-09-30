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
$categories = ['road' => 'Long road trips', 'hills' => 'Hill stations', 'nature' => 'Nature & adventure', 'heritage' => 'Heritage & culture', 'city' => 'City escapes'];
$imageFiles = [];
foreach (['destinations', 'stays'] as $imageFolder) {
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $imageExtension) {
        $imageFiles = array_merge($imageFiles, glob($root . '/assets/images/' . $imageFolder . '/*.' . $imageExtension) ?: []);
    }
}
$imageNames = array_values(array_unique(array_map(static fn($path) => pathinfo($path, PATHINFO_FILENAME), $imageFiles)));
$imagePaths = [];
foreach ($imageFiles as $imageFile) $imagePaths[pathinfo($imageFile, PATHINFO_FILENAME)] = '../assets/images/' . basename(dirname($imageFile)) . '/' . basename($imageFile);
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
function admin_upload_package_image(array $upload, string $slug, string $root): string
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if (($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('The image upload did not finish. Please try again.');
    if (($upload['size'] ?? 0) < 1 || $upload['size'] > 10 * 1024 * 1024) throw new RuntimeException('Choose an image smaller than 10 MB.');

    $imageInfo = @getimagesize((string) ($upload['tmp_name'] ?? ''));
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = $imageInfo['mime'] ?? '';
    if (!$imageInfo || !isset($extensions[$mime])) throw new RuntimeException('Upload a valid JPEG, PNG or WebP image.');
    if ($imageInfo[0] < 600 || $imageInfo[1] < 350 || $imageInfo[0] * $imageInfo[1] > 25000000) {
        throw new RuntimeException('Use an image at least 600 x 350 pixels and no larger than 25 megapixels.');
    }

    $directory = $root . '/assets/images/destinations';
    if (!is_dir($directory) || !is_writable($directory)) throw new RuntimeException('The destinations image folder is not writable.');
    $fileName = 'package-' . substr($slug, 0, 70) . '-' . bin2hex(random_bytes(5)) . '.' . $extensions[$mime];
    if (!move_uploaded_file((string) $upload['tmp_name'], $directory . '/' . $fileName)) {
        throw new RuntimeException('Could not store the uploaded image. Check folder permissions.');
    }
    return pathinfo($fileName, PATHINFO_FILENAME);
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
                $error = app_db_initialize($packages ?: $defaultPackages, $vehicles ?: $defaultVehicles);
                if ($error === '') {
                    $_SESSION['notice'] = 'Database is ready. Current packages and taxis were imported.';
                    admin_redirect();
                }
            } catch (Throwable $exception) {
                $error = 'Database setup failed. Check MySQL is running and the configured account can create databases and tables.';
            }
        } else {
            $kind = (string) ($_POST['kind'] ?? 'package');
            $table = $kind === 'taxi' ? 'taxis' : 'packages';
            $action = (string) ($_POST['action'] ?? 'save');
            $slug = admin_slug((string) ($_POST['slug'] ?? ''));
            try {
                $db = app_db();
                if (!$db) throw new RuntimeException('Start MySQL and use Initialize database before saving records.');
                if ($action === 'delete') {
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
                } else {
                    $category = (string) ($_POST['cat'] ?? 'heritage');
                    $image = (string) ($_POST['image'] ?? '');
                    if (!isset($categories[$category])) throw new RuntimeException('Choose a valid package category.');
                    $highlights = admin_lines((string) ($_POST['highlights'] ?? ''));
                    $days = [];
                    foreach (admin_lines((string) ($_POST['days'] ?? '')) as $line) {
                        $parts = explode('|', $line, 2);
                        if (count($parts) === 2 && trim($parts[0]) !== '' && trim($parts[1]) !== '') $days[] = [trim($parts[0]), trim($parts[1])];
                    }
                    if (!$highlights || !$days) throw new RuntimeException('Add at least one highlight and one day formatted as “Title | details”.');
                    $uploadedImage = admin_upload_package_image($_FILES['package_image'] ?? [], $slug, $root);
                    if ($uploadedImage !== '') {
                        $image = $uploadedImage;
                    } elseif (!in_array($image, $imageNames, true)) {
                        throw new RuntimeException('Upload a package image or choose one from the image library.');
                    }
                    $record = [
                        'title' => $name, 'label' => trim((string) ($_POST['label'] ?? '')), 'cat' => $category,
                        'duration' => trim((string) ($_POST['duration'] ?? '')), 'short' => trim((string) ($_POST['short'] ?? '')),
                        'popular' => !empty($_POST['popular']), 'image' => $image, 'image_customized' => true, 'difficulty' => trim((string) ($_POST['difficulty'] ?? 'Easy')),
                        'season' => trim((string) ($_POST['season'] ?? 'All year')), 'start' => trim((string) ($_POST['start'] ?? 'Your home or hotel')),
                        'end' => trim((string) ($_POST['end'] ?? 'Your home or hotel')), 'route' => trim((string) ($_POST['route'] ?? '')),
                        'overview' => trim((string) ($_POST['overview'] ?? '')), 'highlights' => $highlights, 'days' => $days,
                    ];
                }
                $oldSlug = admin_slug((string) ($_POST['old_slug'] ?? ''));
                if ($oldSlug !== '' && $oldSlug !== $slug) {
                    $removeOld = $db->prepare('DELETE FROM `' . $table . '` WHERE slug = ?');
                    $removeOld->execute([$oldSlug]);
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
$activeTab = ($_GET['tab'] ?? 'packages') === 'taxis' ? 'taxis' : 'packages';
$editingSlug = (string) ($_GET['edit'] ?? '');
$editingKind = (string) ($_GET['kind'] ?? 'package');
$packageForm = $editingKind === 'package' ? ($packages[$editingSlug] ?? []) : [];
$taxiForm = [];
foreach ($vehicles as $vehicle) if (($vehicle['_slug'] ?? admin_slug($vehicle['name'])) === $editingSlug && $editingKind === 'taxi') $taxiForm = $vehicle;
$selectedTab = $editingKind === 'taxi' ? 'taxis' : $activeTab;
$dbPackages = app_db_catalog('packages');
$dbTaxis = app_db_catalog('taxis');
$dbReady = app_db() !== null && !empty($dbPackages) && !empty($dbTaxis);
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
    body{background:#f5f6f3;color:#24313a}.dash-header{background:#123b3a;color:#fff;padding:16px 24px}.dash-head-inner{max-width:1200px;margin:auto;display:flex;align-items:center;justify-content:space-between;gap:18px}.dash-brand{color:#fff;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:11px}.dash-brand img{display:block;width:44px;height:44px}.dash-brand small{display:block;font-size:12px;font-weight:400;opacity:.76}.dash-main{max-width:1200px;margin:28px auto;padding:0 18px}.dash-top{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:20px}.dash-top h1{font-size:30px;margin:0}.dash-top p{margin:5px 0 0;color:#68756f}.dash-status{padding:12px 15px;background:#fff;border:1px solid #dce2df;border-radius:5px;margin-bottom:18px}.dash-status.good{border-left:4px solid #25805e}.dash-status.bad{border-left:4px solid #bd483f}.dash-tabs{display:flex;gap:8px;border-bottom:1px solid #d7dfdb;margin-bottom:20px}.dash-tabs a{padding:12px 16px;text-decoration:none;color:#51605b;border-bottom:3px solid transparent;font-weight:600}.dash-tabs a.active{color:#146553;border-color:#cf9a3c}.dash-panel{background:#fff;border:1px solid #dce2df;border-radius:6px;padding:22px;margin-bottom:22px}.dash-panel h2{font-size:21px;margin:0 0 18px}.dash-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.dash-grid label{display:block;font-size:13px;font-weight:600;color:#44534d}.dash-grid input,.dash-grid textarea,.dash-grid select{margin-top:5px;width:100%;border:1px solid #cbd4d0;border-radius:4px;padding:10px;color:#24313a;background:#fff}.dash-grid textarea{min-height:96px}.dash-wide{grid-column:1/-1}.dash-list{border-top:1px solid #e5e9e7}.dash-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:13px 0;border-bottom:1px solid #e5e9e7}.dash-row-main{display:flex;align-items:center;gap:13px;min-width:0}.dash-thumb{width:68px;height:52px;object-fit:cover;border-radius:3px;background:#eee}.dash-row small{display:block;color:#74817b;margin-top:2px}.dash-actions{display:flex;gap:7px;flex-shrink:0}.dash-actions form{margin:0}.dash-actions .btn{border-radius:4px}.dash-note{color:#69766f;font-size:13px}.dash-empty{padding:16px 0;color:#69766f}.setup-box{max-width:520px;margin:70px auto}.dash-section-title{display:flex;justify-content:space-between;align-items:center;gap:14px}@media(max-width:720px){.dash-top{display:block}.dash-top .btn{margin-top:14px}.dash-grid{grid-template-columns:1fr}.dash-wide{grid-column:auto}.dash-row{align-items:flex-start;flex-direction:column}.dash-actions{flex-wrap:wrap}.dash-tabs a{padding:10px 12px}.dash-header{padding:14px}.dash-panel{padding:17px}}
    .dash-grid input::placeholder,.dash-grid textarea::placeholder{color:#89948f;opacity:1}.dash-grid input[type=file]{padding:8px;background:#fafbf9}.dash-image-preview-wrap{min-height:0}.dash-package-preview{display:block;width:min(100%,520px);max-height:270px;aspect-ratio:16/9;object-fit:cover;border:1px solid #dce2df;border-radius:4px;background:#f3f5f3}.dash-package-preview[hidden]{display:none}.dash-note{display:block;margin-top:5px}
  </style>
</head>
<body>
<header class="dash-header"><div class="dash-head-inner"><a class="dash-brand" href="../index.php"><img src="../assets/logo.svg" alt="" width="44" height="44"> <span>Reach Dream Travel <small>/ Dashboard</small></span></a><?php if ($authenticated): ?><form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><button class="btn btn-outline-light btn-sm" name="logout" value="1">Sign out</button></form><?php endif; ?></div></header>
<main class="dash-main">
<?php if ($message): ?><div class="dash-status good"><?= admin_h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="dash-status bad"><?= admin_h($error) ?></div><?php endif; ?>
<?php if ($setupNeeded): ?>
  <section class="dash-panel setup-box"><h1 class="h3">Create dashboard account</h1><p>Create the first admin user. Passwords require at least 12 characters.</p><form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><label class="d-block mb-3">Username<input class="form-control mt-1" type="text" name="username" minlength="3" maxlength="100" required autocomplete="username"></label><label class="d-block mb-3">Password<input class="form-control mt-1" type="password" name="password" minlength="12" required autocomplete="new-password"></label><label class="d-block mb-3">Confirm password<input class="form-control mt-1" type="password" name="confirm_password" minlength="12" required autocomplete="new-password"></label><button class="btn btn-success" name="setup" value="1">Create admin user</button></form></section>
<?php elseif (!$authenticated): ?>
  <section class="dash-panel setup-box"><h1 class="h3">Sign in</h1><form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><label class="d-block mb-3">Username<input class="form-control mt-1" type="text" name="username" required autocomplete="username"></label><label class="d-block mb-3">Password<input class="form-control mt-1" type="password" name="password" required autocomplete="current-password"></label><button class="btn btn-success" name="login" value="1">Sign in</button></form></section>
<?php else: ?>
  <div class="dash-top"><div><h1>Content dashboard</h1><p>Manage the trips and vehicles shown on your website.</p></div><?php if (!$dbReady): ?><form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><button class="btn btn-gold" name="initialize_database" value="1"><i class="fa-solid fa-database" aria-hidden="true"></i> Initialize database</button></form><?php else: ?><span class="badge text-bg-success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> MySQL connected</span><?php endif; ?></div>
  <?php if (!$dbReady): ?><div class="dash-status">MySQL is not initialized. Start MySQL, then choose <strong>Initialize database</strong> to create the database and import the current catalog.</div><?php endif; ?>
  <nav class="dash-tabs" aria-label="Content types"><a class="<?= $selectedTab === 'packages' ? 'active' : '' ?>" href="?tab=packages"><i class="fa-solid fa-route" aria-hidden="true"></i> Packages <span>(<?= count($packages) ?>)</span></a><a class="<?= $selectedTab === 'taxis' ? 'active' : '' ?>" href="?tab=taxis"><i class="fa-solid fa-car-side" aria-hidden="true"></i> Taxis <span>(<?= count($vehicles) ?>)</span></a></nav>
  <?php if ($selectedTab === 'packages'): ?>
    <section class="dash-panel" id="editor"><div class="dash-section-title"><h2><?= $packageForm ? 'Update package' : 'Add package' ?></h2><?php if ($packageForm): ?><a class="btn btn-outline-secondary btn-sm" href="?tab=packages">New package</a><?php endif; ?></div>
      <form method="post" enctype="multipart/form-data" data-package-form><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="package"><input type="hidden" name="action" value="save"><div class="dash-grid">
        <input type="hidden" name="old_slug" value="<?= admin_h($editingKind === 'package' ? $editingSlug : '') ?>">
        <label>Package title<input name="title" required value="<?= admin_h($packageForm['title'] ?? '') ?>" placeholder="e.g. Jaipur & Jaisalmer Heritage Trail"></label>
        <label>URL slug<input name="slug" data-package-slug value="<?= admin_h($editingKind === 'package' ? $editingSlug : '') ?>" placeholder="Generated from package title"></label>
        <label>Card label<input name="label" value="<?= admin_h($packageForm['label'] ?? '') ?>" placeholder="e.g. Desert & heritage"></label>
        <label>Category<select name="cat" required><option value="" disabled<?= empty($packageForm['cat']) ? ' selected' : '' ?>>Choose a category</option><?php foreach ($categories as $key => $label): ?><option value="<?= admin_h($key) ?>"<?= ($packageForm['cat'] ?? '') === $key ? ' selected' : '' ?>><?= admin_h($label) ?></option><?php endforeach; ?></select></label>
        <label>Duration<input name="duration" value="<?= admin_h($packageForm['duration'] ?? '') ?>" placeholder="e.g. 5 days · 4 nights"></label>
        <label>Short duration<input name="short" value="<?= admin_h($packageForm['short'] ?? '') ?>" placeholder="e.g. 5D · 4N"></label>
        <label>Difficulty<input name="difficulty" value="<?= admin_h($packageForm['difficulty'] ?? 'Easy') ?>" placeholder="Easy, moderate or challenging"></label>
        <label>Best season<input name="season" value="<?= admin_h($packageForm['season'] ?? 'All year') ?>" placeholder="e.g. October to March"></label>
        <label>Package image<select name="image" data-package-image><option value="">Choose an image from the library</option><?php foreach ($imageNames as $image): ?><option value="<?= admin_h($image) ?>"<?= ($packageForm['image'] ?? '') === $image ? ' selected' : '' ?>><?= admin_h($image) ?></option><?php endforeach; ?></select></label>
        <label>Upload package image<input type="file" name="package_image" accept="image/jpeg,image/png,image/webp" data-package-upload><span class="dash-note">JPEG, PNG or WebP. Minimum 600 × 350 px; maximum 10 MB. Upload overrides the library selection.</span></label>
        <div class="dash-wide dash-image-preview-wrap"><img class="dash-package-preview" data-package-preview src="<?= $packageForm ? admin_h('../' . img($packageForm['image'], true)) : '' ?>" alt="Current package image preview"<?= $packageForm ? '' : ' hidden' ?>></div>
        <label>Route<input name="route" value="<?= admin_h($packageForm['route'] ?? '') ?>" placeholder="e.g. Jaipur · Jodhpur · Jaisalmer"></label>
        <label>Starts at<input name="start" value="<?= admin_h($packageForm['start'] ?? 'Your home or hotel') ?>" placeholder="e.g. Jaipur airport or hotel"></label>
        <label>Ends at<input name="end" value="<?= admin_h($packageForm['end'] ?? 'Your home or hotel') ?>" placeholder="e.g. Jaipur airport or hotel"></label>
        <label class="dash-wide">Overview<textarea name="overview" required placeholder="Describe the feel of the trip, who it suits and what guests can expect."><?= admin_h($packageForm['overview'] ?? '') ?></textarea></label>
        <label class="dash-wide">Highlights<textarea name="highlights" required placeholder="Amber Fort at opening time&#10;Old-city food walk&#10;Sunset over the dunes"><?= admin_h(implode("\n", $packageForm['highlights'] ?? [])) ?></textarea><span class="dash-note">One highlight per line.</span></label>
        <label class="dash-wide">Day-by-day plan<textarea name="days" required placeholder="Day 1: Arrival | Airport pickup and hotel check-in&#10;Day 2: Jaipur | Fort visit and old-city walk"><?= admin_h(implode("\n", array_map(static fn($day) => $day[0] . ' | ' . $day[1], $packageForm['days'] ?? []))) ?></textarea><span class="dash-note">One day per line: Day title | day description</span></label>
        <label><span><input type="checkbox" name="popular" value="1"<?= !empty($packageForm['popular']) ? ' checked' : '' ?>> Mark as popular</span></label>
      </div><p class="mt-3 mb-0"><button class="btn btn-gold" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save package</button></p></form>
    </section>
    <section class="dash-panel"><h2>All packages</h2><div class="dash-list"><?php foreach ($packages as $slug => $package): ?><div class="dash-row"><div class="dash-row-main"><img class="dash-thumb" src="<?= admin_h('../' . img($package['image'], true)) ?>" alt=""><div><strong><?= admin_h($package['title']) ?></strong><small><?= admin_h($slug) ?> · <?= admin_h($categories[$package['cat']] ?? $package['cat']) ?></small></div></div><div class="dash-actions"><a class="btn btn-sm btn-outline-secondary" href="?tab=packages&amp;kind=package&amp;edit=<?= rawurlencode($slug) ?>#editor">Edit</a><a class="btn btn-sm btn-outline-success" target="_blank" href="../package.php?slug=<?= rawurlencode($slug) ?>">View</a><form method="post" onsubmit="return confirm('Delete this package?')"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="package"><input type="hidden" name="action" value="delete"><input type="hidden" name="slug" value="<?= admin_h($slug) ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div></div><?php endforeach; ?></div></section>
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
  const imageSelect = form.querySelector('[data-package-image]');
  const upload = form.querySelector('[data-package-upload]');
  const preview = form.querySelector('[data-package-preview]');
  const images = <?= json_encode($imagePaths, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  let slugWasEdited = false;

  const makeSlug = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  slug.addEventListener('input', () => { slugWasEdited = true; });
  title.addEventListener('input', () => { if (!slugWasEdited) slug.value = makeSlug(title.value); });

  const showPreview = (src) => {
    if (!src) { preview.hidden = true; preview.removeAttribute('src'); return; }
    preview.src = src;
    preview.hidden = false;
  };
  imageSelect.addEventListener('change', () => {
    upload.value = '';
    showPreview(images[imageSelect.value] || '');
  });
  upload.addEventListener('change', () => {
    if (!upload.files.length) { showPreview(images[imageSelect.value] || ''); return; }
    showPreview(URL.createObjectURL(upload.files[0]));
  });
})();
</script>
<?php endif; ?>
</body></html>
