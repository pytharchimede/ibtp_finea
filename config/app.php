<?php

$brand = require __DIR__ . '/branding.php';

return [
    'name' => (string) ($brand['product_name'] ?? 'FINEA'),
    'tagline' => (string) ($brand['tagline'] ?? ''),
    'url' => rtrim((string) (getenv('APP_URL') ?: 'http://localhost/ibtp_finea'), '/'),

    'theme' => [
        'primary' => (string) ($brand['colors']['primary'] ?? '#1d2b57'),
        'secondary' => (string) ($brand['colors']['accent'] ?? '#fabd02'),
    ],
];
