<?php
/**
 * Where trip enquiries are emailed, and how.
 *
 * Leave 'smtp_user' empty to send with PHP's built-in mail() (works on most web hosts).
 * For reliable delivery to Gmail, create an App Password for reachdreamtravel@gmail.com
 * (Google Account → Security → 2-Step Verification → App passwords) and fill in the
 * SMTP details below. On local XAMPP, SMTP is required — mail() is not set up there.
 */
return [
    'to'        => 'reachdreamtravel@gmail.com',
    'from_name' => 'Reach Dream Travel Website',

    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 465,                  // 465 = SSL
    'smtp_user' => '',                   // e.g. reachdreamtravel@gmail.com
    'smtp_pass' => '',                   // 16-character Gmail App Password
];
