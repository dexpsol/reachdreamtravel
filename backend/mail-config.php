<?php
require_once __DIR__ . '/database.php';

return [
    'to' => getenv('REACH_MAIL_TO') ?: '',
    'from' => getenv('REACH_MAIL_FROM') ?: '',
    'from_name' => 'Reach Dream Travel Website',
    'smtp_host' => getenv('REACH_SMTP_HOST') ?: '',
    'smtp_port' => (int) (getenv('REACH_SMTP_PORT') ?: 465),
    'smtp_user' => getenv('REACH_SMTP_USER') ?: '',
    'smtp_password' => preg_replace('/\s+/', '', getenv('REACH_SMTP_PASSWORD') ?: ''),
];
