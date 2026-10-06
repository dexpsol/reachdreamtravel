<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function enquiry_response(int $status, bool $ok, string $message): never
{
    http_response_code($status);
    echo json_encode(['ok' => $ok, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    enquiry_response(405, false, 'Use the package request form to send an enquiry.');
}

require_once __DIR__ . '/database.php';
$mail = require __DIR__ . '/mail-config.php';

if (trim((string) ($_POST['website'] ?? '')) !== '') {
    enquiry_response(200, true, 'Your package request has been sent by email.');
}

$fields = [
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

$values = [];
foreach ($fields as $key => $label) {
    $raw = $_POST[$key] ?? '';
    if (!is_string($raw)) continue;
    $value = trim(strip_tags($raw));
    $values[$key] = function_exists('mb_substr') ? mb_substr($value, 0, 1500) : substr($value, 0, 1500);
}

if (($values['name'] ?? '') === '' || ($values['phone'] ?? '') === '') {
    enquiry_response(422, false, 'Please provide your name and phone number.');
}
if (($values['email'] ?? '') !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
    enquiry_response(422, false, 'Please enter a valid email address.');
}

$customPackage = isset($_POST['purpose']) || isset($_POST['arrival']) || isset($_POST['departure']);
$subject = $customPackage ? 'Customized package request' : 'Trip enquiry';
$db = app_db();
if (!$db) {
    enquiry_response(503, false, 'We could not save your request. Please contact us on WhatsApp.');
}
try {
    $db->exec('CREATE TABLE IF NOT EXISTS enquiries (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, category VARCHAR(64) NOT NULL, payload LONGTEXT NOT NULL, submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_enquiries_submitted_at (submitted_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $saveEnquiry = $db->prepare('INSERT INTO enquiries (category, payload) VALUES (?, ?)');
    $saveEnquiry->execute([
        $subject,
        json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    ]);
} catch (Throwable $error) {
    error_log('Package enquiry database save failed: ' . $error->getMessage());
    enquiry_response(503, false, 'We could not save your request. Please contact us on WhatsApp.');
}

if ($mail['to'] === '' || !filter_var($mail['to'], FILTER_VALIDATE_EMAIL) || $mail['from'] === '' || !filter_var($mail['from'], FILTER_VALIDATE_EMAIL)) {
    enquiry_response(500, false, 'Your request was saved, but email settings are incomplete. Please contact us on WhatsApp.');
}
if ($mail['smtp_host'] === '' || $mail['smtp_user'] === '' || $mail['smtp_password'] === '' || $mail['smtp_port'] < 1) {
    enquiry_response(500, false, 'Your request was saved, but email settings are incomplete. Please contact us on WhatsApp.');
}

$body = $subject . "\n\n";
foreach ($fields as $key => $label) {
    if (($values[$key] ?? '') !== '') $body .= $label . ': ' . $values[$key] . "\n";
}
require_once __DIR__ . '/smtp-mailer.php';
try {
    smtp_send_message(
        $mail,
        $mail['to'],
        $subject,
        $body,
        ($values['email'] ?? '') !== '' ? $values['email'] : null
    );
} catch (Throwable $error) {
    error_log('Package enquiry email failed: ' . $error->getMessage());
    if (str_contains($error->getMessage(), 'authentication')) {
        enquiry_response(503, false, 'Your request was saved, but email sign-in failed. Please contact us on WhatsApp.');
    }
    enquiry_response(503, false, 'Your request was saved, but email could not be sent. Please contact us on WhatsApp.');
}

enquiry_response(200, true, 'Your request was emailed. WhatsApp is open with the message ready; tap Send there too.');
