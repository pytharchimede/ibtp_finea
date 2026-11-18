<?php

declare(strict_types=1);

// Ne jamais embarquer de mot de passe DB dans un dépôt ou un profil commercial.
$user = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
if (getenv('APP_ENV') === 'production'
    && ($user === false || $user === '' || $password === false || $password === '')) {
    throw new RuntimeException('Identifiants base de données non configurés.');
}

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => (int) (getenv('DB_PORT') ?: 3306),
    'dbname' => getenv('DB_DATABASE') ?: 'finea_db',
    'username' => $user !== false ? (string) $user : 'root',
    'password' => $password !== false ? (string) $password : '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
];
