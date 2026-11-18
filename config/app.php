<?php

return [
    'name' => 'FINEA Commercial',
    'tagline' => 'ERP modulaire de gestion d’entreprise, configurable par organisation.',
    'url' => rtrim((string) (getenv('APP_URL') ?: 'http://localhost/ibtp_finea'), '/'),

    'theme' => [
        'primary' => '#1d2b57',
        'secondary' => '#fabd02',
    ],
];
