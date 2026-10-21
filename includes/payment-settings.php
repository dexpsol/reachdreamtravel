<?php
/** Demo defaults. Saved Admin > Payment details values take precedence. */
require_once dirname(__DIR__) . '/backend/payment-settings.php';
return site_payment_settings([
    'demo' => true,
    'location' => "Demo Office, Mall Road, Shimla\nHimachal Pradesh - 171001",
    'bank' => [
        'accountName' => 'Reach Dream Travel (Demo)',
        'bankName' => 'Demo Bank',
        'accountNumber' => '00000000000000',
        'ifsc' => 'DEMO0000000',
        'branch' => 'Shimla (Demo)',
        'accountType' => 'Current',
    ],
    'upi' => [
        'id' => 'reachdreamtravel@demo',
        'name' => 'Reach Dream Travel (Demo)',
        'phone' => '0000000000',
        'qrImage' => '',
    ],
]);
