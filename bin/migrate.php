<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI uniquement'); }
require dirname(__DIR__).'/bootstrap/app.php';
try {
    // bootstrap/app.php runs the idempotent migrations on startup.
    echo "Migrations FINEA terminées.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Échec migration : ".$e->getMessage()."\n");
    exit(1);
}
