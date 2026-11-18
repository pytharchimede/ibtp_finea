<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI uniquement'); }
require dirname(__DIR__).'/bootstrap/app.php';
use App\Database\MigrationRunner;
use App\Models\Database;
try {
    (new MigrationRunner(Database::getConnection()))->run();
    echo "Migrations FINEA terminées.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Échec migration : ".$e->getMessage()."\n");
    exit(1);
}
