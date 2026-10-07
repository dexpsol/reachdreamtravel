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
$loadImageLibrary = !empty($_SESSION['admin']) && (
    (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['kind'] ?? '') === 'destination')
    || ($_GET['tab'] ?? '') === 'destinations'
    || ($_GET['kind'] ?? '') === 'destination'
);
$categories = ['domestic' => 'Domestic', 'international' => 'International', 'nature' => 'Nature', 'heritage' => 'Heritage', 'himachal' => 'Himachal tours', 'trekking' => 'Trekking', 'camping' => 'Camping', 'religious' => 'Religious / Temple', 'solo' => 'Solo trips', 'new-year' => 'New Year', 'group' => 'Group tours', 'adventure' => 'Adventure / Activities', 'special' => 'Special interest', 'hills' => 'Hill stations', 'road' => 'Long road trips', 'north' => 'Northern India'];
$imageFiles = [];
if ($loadImageLibrary) {
    foreach (['destinations', 'stays'] as $imageFolder) {
        foreach (glob($root . '/assets/images/' . $imageFolder . '/*') ?: [] as $imagePath) {
            if (in_array(strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $imageFiles[] = $imagePath;
            }
        }
    }
}
$imageNames = array_values(array_unique(array_map(static fn($path) => pathinfo($path, PATHINFO_FILENAME), $imageFiles)));
$imageOptions = [];
foreach ($imageFiles as $imagePath) {
    $imageName = pathinfo($imagePath, PATHINFO_FILENAME);
    $imageFolder = basename(dirname($imagePath));
    $imageOptions[$imageName] ??= [
        'name' => $imageName,
        'src' => '../assets/images/' . $imageFolder . '/' . basename($imagePath),
    ];
}
ksort($imageOptions, SORT_NATURAL | SORT_FLAG_CASE);
$taxiImages = array_values(array_map(static fn($path) => 'assets/images/car/' . basename($path), glob($root . '/assets/images/car/fleet-*.jpg') ?: []));
$message = (string) ($_SESSION['notice'] ?? '');
$error = '';
unset($_SESSION['notice']);

function admin_h($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
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
function admin_destination_upload(string $slug, string $root): string
{
    $file = $_FILES['image_upload'] ?? null;
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Destination image upload failed. Try another image.');
    if ((int) ($file['size'] ?? 0) > 6 * 1024 * 1024) throw new RuntimeException('Destination image must be 6 MB or smaller.');

    $info = getimagesize((string) $file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $extension = $extensions[$info['mime'] ?? ''] ?? '';
    if ($extension === '') throw new RuntimeException('Upload a JPG, PNG or WebP destination image.');

    $folder = $root . '/assets/images/destinations';
    if (!is_dir($folder) && !mkdir($folder, 0775, true)) throw new RuntimeException('Could not create the destination image folder.');
    $fileBase = ($slug !== '' ? $slug : 'destination') . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));
    $target = $folder . '/' . $fileBase . '.' . $extension;
    if (!move_uploaded_file((string) $file['tmp_name'], $target)) throw new RuntimeException('Could not save the uploaded destination image.');
    return $fileBase;
}
function admin_match_destination_packages(array $destination, array $packages): array
{
    $needles = array_merge([(string) ($destination['name'] ?? '')], $destination['see'] ?? []);
    $needles = array_values(array_unique(array_filter(array_map(static fn($value) => admin_slug((string) $value), $needles), static fn($value) => strlen($value) > 2)));
    $matches = [];
    foreach ($packages as $slug => $package) {
        if (!empty($package['deprecated_duplicate_of'])) continue;
        $parts = [$slug, $package['title'] ?? '', $package['route'] ?? '', $package['overview'] ?? ''];
        foreach ($package['highlights'] ?? [] as $highlight) $parts[] = $highlight;
        $haystack = admin_slug(implode(' ', array_map('strval', $parts)));
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                $matches[] = $slug;
                break;
            }
        }
    }
    return $matches;
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
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
                $error = app_db_initialize($packages ?: $defaultPackages, $vehicles ?: $defaultVehicles, $destinations ?: $defaultDestinations);
                if ($error === '') {
                    $_SESSION['notice'] = 'Database is ready. Current packages, destinations and taxis were imported.';
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
                if ($slug === '' || $name === '') throw new RuntimeException($kind === 'destination' ? 'Add a destination name.' : 'Add a name and a valid URL slug.');
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
                    $see = admin_lines((string) ($_POST['see'] ?? ''));
                    $previousSlug = admin_slug((string) ($_POST['old_slug'] ?? ''));
                    $previousDestination = $destinations[$previousSlug] ?? $destinations[$slug] ?? [];
                    $imageChoice = trim((string) ($_POST['image_choice'] ?? ''));
                    $image = $imageChoice !== '' ? $imageChoice : trim((string) ($_POST['image'] ?? ''));
                    $uploadedImage = admin_destination_upload($slug, $root);
                    if ($uploadedImage !== '') {
                        $image = $uploadedImage;
                    } elseif ($image === '' && !empty($previousDestination['image'])) {
                        $image = (string) $previousDestination['image'];
                    }
                    if (!$see) throw new RuntimeException('Add at least one sightseeing place.');
                    if ($image === '' || ($uploadedImage === '' && !in_array($image, $imageNames, true) && !preg_match('~^https?://~i', $image))) throw new RuntimeException('Upload a destination image.');
                    $record = [
                        'name' => $name,
                        'tagline' => trim((string) ($_POST['tagline'] ?? '')),
                        'image' => $image,
                        'altitude' => trim((string) ($_POST['altitude'] ?? 'Varies by route')),
                        'best' => trim((string) ($_POST['best'] ?? 'Plan around your dates')),
                        'drive' => trim((string) ($_POST['drive'] ?? 'Route planned around pickup')),
                        'text' => trim((string) ($_POST['text'] ?? '')),
                        'see' => $see,
                        'packages' => [],
                    ];
                    if ($record['tagline'] === '' || $record['text'] === '') throw new RuntimeException('Add a tagline and destination description.');
                    $record['packages'] = admin_match_destination_packages($record, $packages);
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
$requestedTab = (string) ($_GET['tab'] ?? 'dashboard');
$activeTab = in_array($requestedTab, ['dashboard', 'packages', 'taxis', 'destinations', 'enquiries'], true) ? $requestedTab : 'dashboard';
$editingSlug = (string) ($_GET['edit'] ?? '');
$editingKind = (string) ($_GET['kind'] ?? '');
if (!in_array($editingKind, ['package', 'taxi', 'destination'], true)) {
    $editingKind = ['packages' => 'package', 'taxis' => 'taxi', 'destinations' => 'destination'][$activeTab] ?? '';
}
$packageForm = $editingKind === 'package' ? ($packages[$editingSlug] ?? []) : [];
$destinationForm = $editingKind === 'destination' ? ($destinations[$editingSlug] ?? []) : [];
$selectedDestinationImage = (string) ($destinationForm['image'] ?? '');
$selectedDestinationImageName = pathinfo(basename($selectedDestinationImage), PATHINFO_FILENAME);
$destinationGroups = array_map(static function (array $destination): array {
    $destination['title'] = $destination['name'] ?? '';
    $destination['items'] = $destination['see'] ?? [];
    if (str_starts_with((string) ($destination['image'] ?? ''), 'http')) {
        $destination['image'] = 'hero-himachal';
    }
    return $destination;
}, $destinations);
$taxiForm = [];
foreach ($vehicles as $vehicle) if (($vehicle['_slug'] ?? admin_slug($vehicle['name'])) === $editingSlug && $editingKind === 'taxi') $taxiForm = $vehicle;
$selectedTab = ['package' => 'packages', 'taxi' => 'taxis', 'destination' => 'destinations'][$editingKind] ?? $activeTab;
$dbPackages = app_db_catalog('packages');
$dbTaxis = app_db_catalog('taxis');
$dbDestinationGroups = app_db_catalog('destination_groups');
$dbReady = app_db() !== null && !empty($dbPackages) && !empty($dbTaxis) && $dbDestinationGroups !== null;
$enquiryRows = [];
$enquiryCount = 0;
$viewEnquiryId = filter_var($_GET['view'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$selectedEnquiry = null;
if ($authenticated && $db && in_array($selectedTab, ['dashboard', 'enquiries'], true)) {
    try {
        if ($db->query("SHOW TABLES LIKE 'enquiries'")->fetchColumn()) {
            $enquiryCount = (int) $db->query('SELECT COUNT(*) FROM enquiries')->fetchColumn();
            if ($selectedTab === 'enquiries' && $viewEnquiryId === 0) {
                $enquiryRows = $db->query('SELECT id, category, payload, submitted_at FROM enquiries ORDER BY submitted_at DESC, id DESC LIMIT 100')->fetchAll();
            }
            if ($selectedTab === 'enquiries' && $viewEnquiryId > 0) {
                $findEnquiry = $db->prepare('SELECT id, category, payload, submitted_at FROM enquiries WHERE id = ?');
                $findEnquiry->execute([$viewEnquiryId]);
                $selectedEnquiry = $findEnquiry->fetch() ?: null;
            }
        }
    } catch (Throwable $exception) {
        error_log('Admin enquiry list failed: ' . $exception->getMessage());
        if ($selectedTab === 'enquiries') $error = 'Could not load enquiries. Check the database connection.';
    }
}
$enquiryLabels = [
    'name' => 'Name',
    'phone' => 'Phone',
    'email' => 'Email',
    'destination' => 'Destination',
    'duration' => 'Duration',
    'date' => 'Travel date',
    'travellers' => 'Travellers',
    'purpose' => 'Package for',
    'arrival' => 'Arrival date',
    'departure' => 'Departure date',
    'adults' => 'Adults',
    'kids' => 'Kids',
    'room_required' => 'Room required',
    'room_type' => 'Room type',
    'meal_plan' => 'Meal plan',
    'transportation' => 'Transportation',
    'vehicle' => 'Vehicle type',
    'message' => 'Notes',
];
$selectedEnquiryValues = $selectedEnquiry ? json_decode((string) $selectedEnquiry['payload'], true) : null;
$loginView = !$setupNeeded && !$authenticated;
$popularPackages = array_filter($packages, static fn($package) => !empty($package['popular']));
$pricedPackages = array_filter($packages, static fn($package) => !empty($package['price_regular']) || !empty($package['price_discount']));
$destinationPlaces = array_sum(array_map(static fn($destination) => count($destination['see'] ?? []), $destinations));
$dashboardStats = [
    ['icon' => 'fa-route', 'label' => 'Packages', 'value' => count($packages), 'meta' => count($popularPackages) . ' featured'],
    ['icon' => 'fa-map-location-dot', 'label' => 'Destinations', 'value' => count($destinations), 'meta' => $destinationPlaces . ' places'],
    ['icon' => 'fa-car-side', 'label' => 'Fleet options', 'value' => count($vehicles), 'meta' => count($taxiImages) . ' photos'],
    ['icon' => 'fa-tags', 'label' => 'Priced tours', 'value' => count($pricedPackages), 'meta' => count($categories) . ' categories'],
];
$dashboardRecentPackages = array_slice($packages, 0, 4, true);
$dashboardRecentDestinations = array_slice($destinationGroups, 0, 3, true);
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
    body{background:#f5f6f3;color:#24313a}.dash-header{background:#123b3a;color:#fff;padding:16px 24px}.dash-head-inner{max-width:1200px;margin:auto;display:flex;align-items:center;justify-content:space-between;gap:18px}.dash-brand{color:#fff;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:11px} .dash-brand small{display:block;font-size:12px;font-weight:400;opacity:.76}.dash-main{max-width:1200px;margin:28px auto;padding:0 18px}.dash-top{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:20px}.dash-top h1{font-size:30px;margin:0}.dash-top p{margin:5px 0 0;color:#68756f}.dash-status{padding:12px 15px;background:#fff;border:1px solid #dce2df;border-radius:5px;margin-bottom:18px}.dash-status.good{border-left:4px solid #25805e}.dash-status.bad{border-left:4px solid #bd483f}.dash-tabs{display:flex;gap:8px;border-bottom:1px solid #d7dfdb;margin-bottom:20px}.dash-tabs a{padding:12px 16px;text-decoration:none;color:#51605b;border-bottom:3px solid transparent;font-weight:600}.dash-tabs a.active{color:#146553;border-color:#cf9a3c}.dash-panel{background:#fff;border:1px solid #dce2df;border-radius:6px;padding:22px;margin-bottom:22px}.dash-panel h2{font-size:21px;margin:0 0 18px}.dash-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.dash-grid label{display:block;font-size:13px;font-weight:600;color:#44534d}.dash-grid input,.dash-grid textarea,.dash-grid select{margin-top:5px;width:100%;border:1px solid #cbd4d0;border-radius:4px;padding:10px;color:#24313a;background:#fff}.dash-grid textarea{min-height:96px}.dash-wide{grid-column:1/-1}.dash-list{border-top:1px solid #e5e9e7}.dash-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:13px 0;border-bottom:1px solid #e5e9e7}.dash-row-main{display:flex;align-items:center;gap:13px;min-width:0}.dash-thumb{width:68px;height:52px;object-fit:cover;border-radius:3px;background:#eee}.dash-row small{display:block;color:#74817b;margin-top:2px}.dash-actions{display:flex;gap:7px;flex-shrink:0}.dash-actions form{margin:0}.dash-actions .btn{border-radius:4px}.dash-note{color:#69766f;font-size:13px}.dash-empty{padding:16px 0;color:#69766f}.setup-box{max-width:520px;margin:70px auto}.dash-section-title{display:flex;justify-content:space-between;align-items:center;gap:14px}.dash-field-title{display:block;font-size:13px;font-weight:600;color:#44534d}.category-picker{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;margin-top:5px}.category-picker label{display:flex;align-items:center;gap:8px;padding:7px 9px;border:1px solid #dce2df;border-radius:4px;background:#fff;font-weight:500;cursor:pointer}.category-picker label:has(input:checked){border-color:#28705c;background:#f0f7f3}.category-picker input{width:16px;height:16px;margin:0;accent-color:#28705c;flex:0 0 auto}@media(max-width:720px){.dash-top{display:block}.dash-top .btn{margin-top:14px}.dash-grid{grid-template-columns:1fr}.dash-wide{grid-column:auto}.dash-row{align-items:flex-start;flex-direction:column}.dash-actions{flex-wrap:wrap}.dash-tabs a{padding:10px 12px}.dash-header{padding:14px}.dash-panel{padding:17px}}
    .dash-grid input::placeholder,.dash-grid textarea::placeholder{color:#89948f;opacity:1}.dash-note{display:block;margin-top:5px}
    .login-page{min-height:100vh;background:#f1f4f1;color:#203331}.login-page .dash-header{display:none}.login-main{display:grid;place-items:center;width:100%;max-width:none;min-height:100vh;margin:0;padding:32px}.login-layout{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(380px,.85fr);width:min(1120px,100%);min-height:min(700px,calc(100vh - 64px));overflow:hidden;background:#fff;border:1px solid #e0e7e2;border-radius:8px;box-shadow:0 24px 70px rgba(22,55,47,.12)}.login-visual{position:relative;isolation:isolate;min-height:640px;overflow:hidden;background:#17413b;color:#fff}.login-visual>img{position:absolute;z-index:-2;inset:0;width:100%;height:100%;object-fit:cover;object-position:center}.login-visual:after{position:absolute;z-index:-1;inset:0;background:rgba(9,38,35,.58);content:''}.login-visual-content{display:flex;flex-direction:column;justify-content:space-between;min-height:inherit;padding:38px 42px}.login-brand{display:inline-flex;align-items:center;gap:14px;width:max-content;color:#fff;text-decoration:none;font:700 18px/1.25 Montserrat,sans-serif}.login-brand img{flex:0 0 auto}.login-brand small{display:block;margin-top:6px;color:#e6c781;font:600 11px/1.4 'DM Sans',sans-serif}.login-intro{max-width:440px;margin-bottom:30px}.login-intro>span,.login-eyebrow{color:#a6c3b1;font-size:12px;font-weight:700}.login-intro h1{margin:14px 0;font:700 34px/1.2 Montserrat,sans-serif;color:#fff}.login-intro p{max-width:330px;margin:0;color:#e5eee9;font-size:16px;line-height:1.6}.login-panel{display:grid;place-items:center;padding:48px}.login-form-wrap{width:min(100%,360px)}.login-mark{display:grid;place-items:center;width:44px;height:44px;margin-bottom:30px;border-radius:6px;background:#edf3ef;color:#17604d;font-size:18px}.login-eyebrow{margin:0 0 8px;color:#497562}.login-panel h2{margin:0;color:#203331;font:700 30px/1.2 Montserrat,sans-serif}.login-copy{margin:10px 0 30px;color:#697973;font-size:15px;line-height:1.5}.login-form{display:grid;gap:9px}.login-form label{margin-top:8px;color:#31433e;font-size:13px;font-weight:700}.login-form .form-control{min-height:48px;border-color:#cad6cf;border-radius:4px;padding:11px 13px;color:#203331}.login-form .form-control:focus{border-color:#26725e;box-shadow:0 0 0 3px rgba(38,114,94,.14)}.login-submit{display:flex;align-items:center;justify-content:center;gap:10px;min-height:48px;margin-top:14px;border:0;border-radius:4px;background:#174f43;color:#fff;font-weight:700}.login-submit:hover,.login-submit:focus-visible{background:#103d34;color:#fff}.login-back{display:inline-flex;align-items:center;gap:8px;margin-top:28px;color:#526a60;font-size:13px;font-weight:600;text-decoration:none}.login-back:hover{color:#174f43}.login-notice,.login-error{margin:0 0 18px;padding:11px 12px;border:1px solid #cde2d6;border-radius:4px;background:#f1f8f3;color:#275b40;font-size:13px}.login-error{border-color:#ebcbc7;background:#fff5f3;color:#993e36}@media(max-width:760px){.login-main{padding:14px}.login-layout{grid-template-columns:1fr;min-height:0}.login-visual{min-height:250px}.login-visual-content{min-height:250px;padding:24px}.login-intro{margin:30px 0 2px}.login-intro h1{font-size:26px;margin:8px 0}.login-intro p{font-size:14px}.login-brand img{width:44px;height:44px}.login-panel{padding:36px 24px 32px}.login-mark{margin-bottom:20px}.login-copy{margin-bottom:22px}}@media(max-width:420px){.login-visual{min-height:220px}.login-visual-content{min-height:220px;padding:20px}.login-intro{margin-top:24px}.login-panel{padding:30px 20px}}
    body:not(.login-page){min-height:100vh;background:linear-gradient(135deg,#f7f5ed 0%,#eef4f0 42%,#f8f9f5 100%);color:#1f302c}.dash-header{position:sticky;top:0;z-index:20;border-bottom:1px solid rgba(255,255,255,.12);background:linear-gradient(135deg,#0b302d,#174f43 58%,#8a6425);box-shadow:0 18px 50px rgba(13,47,42,.18)}.dash-head-inner{max-width:1280px}.dash-brand{font:800 17px/1.2 Montserrat,sans-serif;letter-spacing:.01em}.dash-brand img{width:50px;height:50px;padding:4px;border-radius:10px;background:rgba(255,255,255,.95)}.dash-brand small{margin-top:4px;color:#e8d09c;font-size:11px;text-transform:uppercase;letter-spacing:.14em}.dash-main{max-width:1280px;margin:34px auto 54px}.dash-top{align-items:center;margin-bottom:22px}.dash-kicker{display:block;margin-bottom:8px;color:#8a6425;font-size:11px;font-weight:800;letter-spacing:.18em;text-transform:uppercase}.dash-top h1{color:#102c28;font:800 clamp(30px,4vw,46px)/1.05 Montserrat,sans-serif}.dash-top p{max-width:660px;color:#65736d;font-size:15px}.dash-status{border:0;border-radius:8px;box-shadow:0 12px 34px rgba(32,58,51,.09)}.dash-tabs{position:sticky;top:83px;z-index:15;overflow:auto;margin-bottom:24px;padding:8px;border:1px solid rgba(205,214,208,.75);border-radius:12px;background:rgba(255,255,255,.78);box-shadow:0 16px 40px rgba(24,54,49,.08);backdrop-filter:blur(18px)}.dash-tabs a{display:inline-flex;align-items:center;gap:9px;border:0;border-radius:8px;color:#52645e;white-space:nowrap}.dash-tabs a span{color:#8b9893;font-size:12px}.dash-tabs a.active{background:#123f38;color:#fff;box-shadow:0 10px 24px rgba(18,63,56,.22)}.dash-tabs a.active span{color:#d9c18c}.dash-panel{border:1px solid rgba(207,216,211,.8);border-radius:10px;background:rgba(255,255,255,.9);box-shadow:0 18px 52px rgba(31,61,54,.09)}.dash-panel h2{color:#1d332e;font:800 22px/1.2 Montserrat,sans-serif}.dash-panel-quiet{background:rgba(255,255,255,.72)}.dash-grid{gap:18px}.dash-grid label,.dash-field-title{color:#2d413b;font-size:12px;letter-spacing:.02em}.dash-grid input,.dash-grid textarea,.dash-grid select{min-height:46px;border-color:#d1d9d5;border-radius:7px;background:#fbfcfa}.dash-grid input:focus,.dash-grid textarea:focus,.dash-grid select:focus{border-color:#1d6d5a;box-shadow:0 0 0 3px rgba(29,109,90,.12);outline:0}.dash-grid textarea{min-height:118px}.category-picker{grid-template-columns:repeat(3,minmax(0,1fr))}.category-picker label{border-radius:7px;background:#fbfcfa}.dash-list{border-top:0}.dash-row{padding:15px 0;border-color:#e8ede9}.dash-row:first-child{padding-top:0}.dash-row:last-child{padding-bottom:0;border-bottom:0}.dash-row-compact{gap:10px}.dash-thumb{width:76px;height:56px;border-radius:8px;box-shadow:0 8px 20px rgba(20,45,39,.12)}.dash-row strong{color:#203631}.dash-row small{color:#718078}.dash-actions .btn,.dash-row .btn,.dash-section-title .btn{border-radius:7px;font-weight:700}.dash-section-title{margin-bottom:18px}.dash-section-title h2{margin:0}.btn-gold{border-color:#b9842e;background:#b9842e;color:#fff}.btn-gold:hover,.btn-gold:focus-visible{border-color:#94661e;background:#94661e;color:#fff}.dash-hero{display:block;margin-bottom:22px;padding:30px;border-radius:14px;background:linear-gradient(135deg,#113c36,#1d6657 58%,#b68131);color:#fff;box-shadow:0 24px 70px rgba(17,60,54,.23)}.dash-hero span{color:#e5c988;font-size:12px;font-weight:800;letter-spacing:.18em;text-transform:uppercase}.dash-hero h2{max-width:760px;margin:10px 0;color:#fff;font:800 clamp(28px,4vw,44px)/1.08 Montserrat,sans-serif}.dash-hero p{max-width:610px;margin:0;color:#edf5f1}.dash-stat-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:22px}.dash-stat{display:flex;gap:14px;align-items:center;min-height:126px;padding:20px;border:1px solid rgba(207,216,211,.8);border-radius:10px;background:#fff;box-shadow:0 16px 42px rgba(31,61,54,.08)}.dash-stat i{display:grid;place-items:center;width:42px;height:42px;border-radius:9px;background:#eef6f2;color:#17604d;font-size:17px}.dash-stat strong,.dash-stat span,.dash-stat small{display:block}.dash-stat strong{color:#102c28;font:800 34px/1 Montserrat,sans-serif}.dash-stat span{margin-top:5px;color:#2d413b;font-weight:800}.dash-stat small{margin-top:3px;color:#7a8781}.dash-dashboard-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:22px}.dash-action-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.dash-action-grid a{display:flex;align-items:center;gap:12px;min-height:70px;padding:15px;border:1px solid #dfe7e2;border-radius:9px;background:#fbfcfa;color:#213a34;font-weight:800;text-decoration:none}.dash-action-grid a:hover{border-color:#b9842e;color:#123f38;box-shadow:0 12px 28px rgba(35,69,61,.09)}.dash-action-grid i{display:grid;place-items:center;width:36px;height:36px;border-radius:8px;background:#123f38;color:#fff}.dash-health-list{display:grid;gap:11px}.dash-health-list div{display:grid;grid-template-columns:28px minmax(90px,1fr) minmax(0,1.4fr);align-items:center;gap:10px;padding:12px;border:1px solid #e5ebe7;border-radius:8px;background:#fbfcfa}.dash-health-list i{color:#17604d}.dash-health-list span{color:#75827c;font-size:13px}.dash-health-list strong{min-width:0;overflow:hidden;color:#233934;text-overflow:ellipsis;white-space:nowrap}.setup-box{border-radius:12px}.login-layout{border-radius:14px;box-shadow:0 30px 80px rgba(22,55,47,.18)}.login-submit,.login-mark{border-radius:8px}@media(max-width:980px){.dash-stat-grid,.dash-dashboard-grid{grid-template-columns:1fr 1fr}.category-picker{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:720px){.dash-main{margin-top:22px}.dash-tabs{top:79px;border-radius:10px}.dash-hero,.dash-panel{border-radius:10px}.dash-stat-grid,.dash-dashboard-grid,.dash-action-grid{grid-template-columns:1fr}.dash-health-list div{grid-template-columns:26px 1fr}.dash-health-list strong{grid-column:2}.category-picker{grid-template-columns:1fr}.dash-hero{padding:22px}.dash-hero h2{font-size:27px}}
  </style>
  <style>
    .dash-image-picker{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;margin-top:8px}
    .dash-image-option{position:relative;display:flex!important;flex-direction:column;gap:5px;padding:6px;border:2px solid #dce2df;border-radius:5px;background:#fff;cursor:pointer}
    .dash-image-option:has(input:checked){border-color:#28705c;background:#f0f7f3}
    .dash-image-option input{position:absolute;top:10px;left:10px;z-index:1;width:18px!important;height:18px;margin:0!important;padding:0;border:0;background:transparent;accent-color:#28705c}
    .dash-image-option img{width:100%;height:76px;object-fit:cover;border-radius:3px}
    .dash-image-option span{overflow:hidden;color:#44534d;font-size:11px;text-overflow:ellipsis;white-space:nowrap}
    @media(max-width:720px){.dash-image-picker{grid-template-columns:repeat(auto-fill,minmax(96px,1fr));gap:7px}.dash-image-option img{height:66px}}
    .dash-grid .dash-popular-toggle{display:flex;align-items:center;gap:12px;width:fit-content;max-width:100%;min-height:70px;padding:12px 15px;border:1px solid #dce5df;border-radius:8px;background:#f8faf8;cursor:pointer;transition:border-color .2s,background .2s,box-shadow .2s}
    .dash-grid .dash-popular-toggle:hover{border-color:#9cbaaa;background:#f2f7f3}
    .dash-grid .dash-popular-toggle:has(input:checked){border-color:#d3a144;background:#fffaf0;box-shadow:0 3px 12px rgba(138,100,37,.08)}
    .dash-grid .dash-popular-toggle input[type=checkbox]{flex:0 0 19px;width:19px;height:19px;margin:0;padding:0;border:1px solid #9aa9a1;border-radius:4px;accent-color:#17604d;cursor:pointer}
    .dash-popular-copy{display:grid;gap:3px}
    .dash-popular-copy strong{color:#263a32;font-size:13px}
    .dash-popular-copy small{color:#69766f;font-size:12px;font-weight:400}
    .dash-popular-badge{margin-left:8px;padding:4px 8px;border-radius:999px;background:#f4e6c8;color:#805716;font-size:10px;font-weight:700;letter-spacing:.03em}
    .dash-grid .dash-popular-toggle:has(input:checked) .dash-popular-badge{background:#c18a2d;color:#fff}
    .dash-grid .dash-popular-toggle:focus-within{outline:3px solid rgba(38,114,94,.2);outline-offset:2px}
    @media(max-width:480px){.dash-grid .dash-popular-toggle{width:100%;gap:9px;padding:11px}.dash-popular-copy small{line-height:1.3}.dash-popular-badge{margin-left:auto}}
    .dash-tabs{flex-wrap:wrap}
    .dash-table-wrap{overflow-x:auto;margin-top:12px}
    .dash-enquiry-table{width:100%;border-collapse:collapse;font-size:13px}
    .dash-enquiry-table th,.dash-enquiry-table td{padding:13px 12px;border-bottom:1px solid #e5e9e7;text-align:left;vertical-align:middle}
    .dash-enquiry-table th{background:#f5f8f6;color:#52645e;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}
    .dash-enquiry-table td{color:#263a32}
    .dash-enquiry-table tbody tr:hover{background:#f8faf8}
    .dash-enquiry-action{text-align:right!important}
    .dash-enquiry-detail-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:16px;border:1px solid #e5e9e7;border-radius:8px;background:#fbfcfb}
    .dash-enquiry-detail-head h3{margin:0;color:#183b32;font:700 18px/1.3 Montserrat,sans-serif}
    .dash-enquiry-detail-head span,.dash-enquiry-detail-head time{color:#69766f;font-size:12px}
    .dash-enquiry-detail-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 22px;margin:18px 0 0}
    .dash-enquiry-detail-list>div{padding:12px;border:1px solid #e5e9e7;border-radius:7px;background:#fff}
    .dash-enquiry-detail-list dt{margin-bottom:4px;color:#718078;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
    .dash-enquiry-detail-list dd{margin:0;color:#263a32;font-size:14px;overflow-wrap:anywhere;white-space:pre-wrap}
    .dash-enquiry-invalid{margin-top:12px;color:#993e36}
    @media(max-width:720px){.dash-enquiry-detail-list{grid-template-columns:1fr;gap:9px}}
    @media(max-width:600px){.dash-enquiry-table thead{display:none}.dash-enquiry-table,.dash-enquiry-table tbody,.dash-enquiry-table tr,.dash-enquiry-table td{display:block;width:100%}.dash-enquiry-table tr{padding:10px 0;border-bottom:1px solid #e5e9e7}.dash-enquiry-table td{display:grid;grid-template-columns:105px minmax(0,1fr);gap:10px;padding:5px 8px;border:0}.dash-enquiry-table td:before{content:attr(data-label);color:#718078;font-size:11px;font-weight:700;text-transform:uppercase}.dash-enquiry-table .dash-enquiry-action{display:flex;justify-content:flex-end}.dash-enquiry-table .dash-enquiry-action:before{content:none}.dash-enquiry-detail-head{flex-direction:column;gap:5px}}
  </style>
</head>
<body<?= $loginView ? ' class="login-page"' : '' ?>>
<?php if (!$loginView): ?><header class="dash-header"><div class="dash-head-inner"><a class="dash-brand" href="../index.php"><img src="../assets/logo.svg" alt="" width="44" height="44"> <span> <small>/ Dashboard</small></span></a><?php if ($authenticated): ?><form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><button class="btn btn-outline-light btn-sm" name="logout" value="1">Sign out</button></form><?php endif; ?></div></header><?php endif; ?>
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
  <div class="dash-top"><div><span class="dash-kicker">Reach Dream Travel Admin</span><h1><?= $selectedTab === 'dashboard' ? 'Dashboard' : ($selectedTab === 'enquiries' ? 'Enquiries' : 'Content studio') ?></h1><p><?= $selectedTab === 'enquiries' ? 'Review trip requests submitted through your website.' : 'Manage trips, destination collections and vehicles shown on your website.' ?></p></div><?php if (!$dbReady): ?><form method="post"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><button class="btn btn-gold" name="initialize_database" value="1"><i class="fa-solid fa-database" aria-hidden="true"></i> Initialize database</button></form><?php endif; ?></div>
  <nav class="dash-tabs" aria-label="Admin sections"><a class="<?= $selectedTab === 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> Dashboard</a><a class="<?= $selectedTab === 'packages' ? 'active' : '' ?>" href="?tab=packages"><i class="fa-solid fa-route" aria-hidden="true"></i> Packages <span>(<?= count($packages) ?>)</span></a><a class="<?= $selectedTab === 'destinations' ? 'active' : '' ?>" href="?tab=destinations"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Destinations <span>(<?= count($destinations) ?>)</span></a><a class="<?= $selectedTab === 'taxis' ? 'active' : '' ?>" href="?tab=taxis"><i class="fa-solid fa-car-side" aria-hidden="true"></i> Taxis <span>(<?= count($vehicles) ?>)</span></a><a class="<?= $selectedTab === 'enquiries' ? 'active' : '' ?>" href="?tab=enquiries"><i class="fa-solid fa-inbox" aria-hidden="true"></i> Enquiries <span>(<?= $enquiryCount ?>)</span></a></nav>
  <?php if ($selectedTab === 'dashboard'): ?>
    <section class="dash-hero" aria-label="Dashboard overview">
      <div>
        <span>Operations overview</span>
        <h2>Keep every itinerary, place and ride ready for the next guest.</h2>
        <p>Use this dashboard for a quick health check, then jump straight into the content that needs attention.</p>
      </div>
    </section>
    <section class="dash-stat-grid" aria-label="Content summary">
      <?php foreach ($dashboardStats as $stat): ?>
        <article class="dash-stat"><i class="fa-solid <?= admin_h($stat['icon']) ?>" aria-hidden="true"></i><div><strong><?= admin_h((string) $stat['value']) ?></strong><span><?= admin_h($stat['label']) ?></span><small><?= admin_h($stat['meta']) ?></small></div></article>
      <?php endforeach; ?>
    </section>
    <div class="dash-dashboard-grid">
      <section class="dash-panel dash-panel-quiet">
        <div class="dash-section-title"><h2>Quick actions</h2><span class="dash-note">Fast paths for regular updates</span></div>
        <div class="dash-action-grid">
          <a href="?tab=packages#editor"><i class="fa-solid fa-plus" aria-hidden="true"></i><span>Add package</span></a>
          <a href="?tab=destinations#editor"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span>Add destination</span></a>
          <a href="?tab=taxis#editor"><i class="fa-solid fa-car" aria-hidden="true"></i><span>Add taxi</span></a>
          <a href="?tab=enquiries"><i class="fa-solid fa-inbox" aria-hidden="true"></i><span>View enquiries (<?= $enquiryCount ?>)</span></a>
          <a href="../index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i><span>View website</span></a>
        </div>
      </section>
    </div>
    <div class="dash-dashboard-grid">
      <section class="dash-panel"><div class="dash-section-title"><h2>Recent packages</h2><a class="btn btn-sm btn-outline-secondary" href="?tab=packages">Manage all</a></div><div class="dash-list"><?php foreach ($dashboardRecentPackages as $slug => $package): ?><div class="dash-row dash-row-compact"><div class="dash-row-main"><img class="dash-thumb" src="<?= admin_h('../' . img($package['image'], true)) ?>" alt=""><div><strong><?= admin_h($package['title']) ?></strong><small><?= admin_h($package['duration'] ?? 'Custom duration') ?> &middot; <?= admin_h($package['season'] ?? 'All year') ?></small></div></div><a class="btn btn-sm btn-outline-secondary" href="?tab=packages&amp;kind=package&amp;edit=<?= rawurlencode($slug) ?>#editor">Edit</a></div><?php endforeach; ?></div></section>
      <section class="dash-panel"><div class="dash-section-title"><h2>Destination focus</h2><a class="btn btn-sm btn-outline-secondary" href="?tab=destinations">Manage all</a></div><div class="dash-list"><?php foreach ($dashboardRecentDestinations as $slug => $group): ?><div class="dash-row dash-row-compact"><div class="dash-row-main"><img class="dash-thumb" src="<?= admin_h('../' . img($group['image'], true)) ?>" alt=""><div><strong><?= admin_h($group['title']) ?></strong><small><?= admin_h((string) count($group['items'] ?? [])) ?> sightseeing places</small></div></div><a class="btn btn-sm btn-outline-secondary" href="?tab=destinations&amp;kind=destination&amp;edit=<?= rawurlencode($slug) ?>#editor">Edit</a></div><?php endforeach; ?></div></section>
    </div>
  <?php elseif ($selectedTab === 'enquiries'): ?>
    <section class="dash-panel">
      <?php if ($viewEnquiryId > 0): ?>
        <div class="dash-section-title">
          <h2>Enquiry details</h2>
          <a class="btn btn-sm btn-outline-secondary" href="?tab=enquiries"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to enquiries</a>
        </div>
        <?php if (!$selectedEnquiry): ?>
          <p class="dash-empty mb-0">This enquiry could not be found.</p>
        <?php else: ?>
          <div class="dash-enquiry-detail-head">
            <div><h3><?= admin_h(is_array($selectedEnquiryValues) ? ($selectedEnquiryValues['name'] ?? 'Website enquiry') : 'Website enquiry') ?></h3><span><?= admin_h($selectedEnquiry['category']) ?></span></div>
            <time datetime="<?= admin_h($selectedEnquiry['submitted_at']) ?>"><?= admin_h(date('M j, Y · g:i a', strtotime($selectedEnquiry['submitted_at']))) ?></time>
          </div>
          <?php if (is_array($selectedEnquiryValues)): ?>
            <dl class="dash-enquiry-detail-list">
              <?php foreach ($selectedEnquiryValues as $field => $value): if (!is_string($value) || trim($value) === '') continue; ?>
                <?php $label = $enquiryLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)); ?>
                <div><dt><?= admin_h($label) ?></dt><dd><?= nl2br(admin_h($value)) ?></dd></div>
              <?php endforeach; ?>
            </dl>
          <?php else: ?>
            <p class="dash-enquiry-invalid mb-0">The saved enquiry details could not be read.</p>
          <?php endif; ?>
        <?php endif; ?>
      <?php else: ?>
        <div class="dash-section-title"><h2>Website enquiries</h2><span class="dash-note"><?= $enquiryCount ?> total</span></div>
        <?php if (!$enquiryRows): ?>
          <p class="dash-empty mb-0">No enquiries have been submitted yet.</p>
        <?php else: ?>
          <?php if ($enquiryCount > count($enquiryRows)): ?><p class="dash-note">Showing the 100 most recent enquiries.</p><?php endif; ?>
          <div class="dash-table-wrap">
            <table class="dash-enquiry-table">
              <thead><tr><th scope="col">Submitted</th><th scope="col">Name</th><th scope="col">Phone</th><th scope="col">Destination</th><th scope="col">Type</th><th scope="col"><span class="visually-hidden">Action</span></th></tr></thead>
              <tbody>
                <?php foreach ($enquiryRows as $enquiry): $values = json_decode((string) $enquiry['payload'], true); ?>
                  <tr>
                    <td data-label="Submitted"><?= admin_h(date('M j, Y · g:i a', strtotime($enquiry['submitted_at']))) ?></td>
                    <td data-label="Name"><?= admin_h(is_array($values) ? ($values['name'] ?? '—') : '—') ?></td>
                    <td data-label="Phone"><?= admin_h(is_array($values) ? ($values['phone'] ?? '—') : '—') ?></td>
                    <td data-label="Destination"><?= admin_h(is_array($values) ? ($values['destination'] ?? '—') : '—') ?></td>
                    <td data-label="Type"><?= admin_h($enquiry['category']) ?></td>
                    <td class="dash-enquiry-action"><a class="btn btn-sm btn-outline-secondary" href="?tab=enquiries&amp;view=<?= (int) $enquiry['id'] ?>">View</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  <?php elseif ($selectedTab === 'packages'): ?>
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
        <label class="dash-popular-toggle">
          <input type="checkbox" name="popular" value="1"<?= !empty($packageForm['popular']) ? ' checked' : '' ?>>
          <span class="dash-popular-copy"><strong>Mark as popular</strong><small>Highlight this package with a popular badge.</small></span>
          <span class="dash-popular-badge">Popular</span>
        </label>
      </div><p class="mt-3 mb-0"><button class="btn btn-gold" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save package</button></p></form>
    </section>
    <section class="dash-panel"><h2>All packages</h2><div class="dash-list"><?php foreach ($packages as $slug => $package): $packageCategoryLabels = array_map(static fn($category) => $categories[$category] ?? $category, preg_split('/\s+/', trim((string) $package['cat'])) ?: []); ?><div class="dash-row"><div class="dash-row-main"><img class="dash-thumb" src="<?= admin_h('../' . img($package['image'], true)) ?>" alt=""><div><strong><?= admin_h($package['title']) ?></strong><small><?= admin_h($slug) ?> · <?= admin_h(implode(', ', $packageCategoryLabels)) ?><?php if (!empty($package['deprecated_duplicate_of'])): ?> · Saved duplicate of <?= admin_h($packages[$package['deprecated_duplicate_of']]['title'] ?? $package['deprecated_duplicate_of']) ?><?php endif; ?></small></div></div><div class="dash-actions"><a class="btn btn-sm btn-outline-secondary" href="?tab=packages&amp;kind=package&amp;edit=<?= rawurlencode($slug) ?>#editor">Edit</a><a class="btn btn-sm btn-outline-success" target="_blank" href="../package.php?slug=<?= rawurlencode($slug) ?>">View</a><form method="post" onsubmit="return confirm('Delete this package?')"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="package"><input type="hidden" name="action" value="delete"><input type="hidden" name="slug" value="<?= admin_h($slug) ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div></div><?php endforeach; ?></div></section>
  <?php elseif ($selectedTab === 'destinations'): ?>
    <section class="dash-panel" id="editor"><div class="dash-section-title"><h2><?= $destinationForm ? 'Update destination' : 'Add destination' ?></h2><?php if ($destinationForm): ?><a class="btn btn-outline-secondary btn-sm" href="?tab=destinations">New destination</a><?php endif; ?></div>
      <form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="destination"><input type="hidden" name="action" value="save"><div class="dash-grid">
        <input type="hidden" name="old_slug" value="<?= admin_h($editingKind === 'destination' ? $editingSlug : '') ?>">
        <input type="hidden" name="image" value="<?= admin_h($destinationForm['image'] ?? '') ?>">
        <label>Destination name<input name="name" required value="<?= admin_h($destinationForm['name'] ?? '') ?>" placeholder="e.g. Shimla"></label>
        <label>Tagline<input name="tagline" required value="<?= admin_h($destinationForm['tagline'] ?? '') ?>" placeholder="e.g. The Queen of Hills"></label>
        <div class="dash-wide">
          <span class="dash-field-title" id="destinationImageLibraryLabel">Choose an existing image</span>
          <div class="dash-image-picker" role="radiogroup" aria-labelledby="destinationImageLibraryLabel">
            <?php foreach ($imageOptions as $imageName => $imageOption): ?>
              <label class="dash-image-option">
                <input type="radio" name="image_choice" value="<?= admin_h($imageName) ?>"<?= $selectedDestinationImageName === $imageName ? ' checked' : '' ?>>
                <img src="<?= admin_h($imageOption['src']) ?>" alt="" loading="lazy">
                <span><?= admin_h($imageName) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <span class="dash-note">Select a thumbnail or upload a new JPG, PNG or WebP image below.</span>
        </div>
        <label>Upload a new destination image<input type="file" name="image_upload" accept="image/jpeg,image/png,image/webp"><span class="dash-note">New images are added to the library automatically. Maximum size: 6 MB.</span></label>
        <label>Drive time<input name="drive" value="<?= admin_h($destinationForm['drive'] ?? 'Route planned around pickup') ?>" placeholder="e.g. 7-8 hrs by road"></label>
        <label>Altitude<input name="altitude" value="<?= admin_h($destinationForm['altitude'] ?? 'Varies by route') ?>" placeholder="e.g. 2,200 m"></label>
        <label>Best season<input name="best" value="<?= admin_h($destinationForm['best'] ?? 'Plan around your dates') ?>" placeholder="e.g. March-June"></label>
        <label class="dash-wide">Destination description<textarea name="text" required placeholder="A short introduction shown on the Destinations page."><?= admin_h($destinationForm['text'] ?? '') ?></textarea></label>
        <label class="dash-wide">Sightseeing places<textarea name="see" required placeholder="The Ridge & Christ Church&#10;Mall Road&#10;Jakhu Temple"><?= admin_h(implode("\n", $destinationForm['see'] ?? [])) ?></textarea><span class="dash-note">One sightseeing place per line. These appear under Don't miss.</span></label>
      </div><p class="mt-3 mb-0"><button class="btn btn-gold" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save destination</button></p></form>
    </section>
    <section class="dash-panel"><h2>All destinations</h2><div class="dash-list"><?php foreach ($destinationGroups as $slug => $group): ?><div class="dash-row"><div class="dash-row-main"><img class="dash-thumb" src="<?= admin_h('../' . img($group['image'], true)) ?>" alt=""><div><strong><?= admin_h($group['title']) ?></strong><small><?= admin_h($slug) ?> · <?= admin_h(implode(', ', $group['items'])) ?></small></div></div><div class="dash-actions"><a class="btn btn-sm btn-outline-secondary" href="?tab=destinations&amp;kind=destination&amp;edit=<?= rawurlencode($slug) ?>#editor">Edit</a><form method="post" onsubmit="return confirm('Delete this destination?')"><input type="hidden" name="csrf" value="<?= admin_h(admin_csrf()) ?>"><input type="hidden" name="kind" value="destination"><input type="hidden" name="action" value="delete"><input type="hidden" name="slug" value="<?= admin_h($slug) ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div></div><?php endforeach; ?></div></section>
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
