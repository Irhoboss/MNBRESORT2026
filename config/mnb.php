<?php

return [
    'database' => [
        'host' => env('MNB_DB_HOST', 'localhost'),
        'name' => env('MNB_DB_NAME', 'mnb_resort'),
        'user' => env('MNB_DB_USER', 'root'),
        'password' => env('MNB_DB_PASSWORD', ''),
    ],
    'mail' => [
        'host' => env('MNB_SMTP_HOST', 'smtp.gmail.com'),
        'port' => (int) env('MNB_SMTP_PORT', 587),
        'encryption' => env('MNB_SMTP_ENCRYPTION', 'tls'),
        'username' => env('MNB_SMTP_USER', ''),
        'password' => env('MNB_SMTP_PASS', ''),
        'from_email' => env('MNB_MAIL_FROM', env('MNB_SMTP_USER', '')),
        'from_name' => env('MNB_MAIL_NAME', 'MNB Resort'),
    ],
];
