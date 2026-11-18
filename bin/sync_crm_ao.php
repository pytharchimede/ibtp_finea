<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit('CLI only');}
require_once dirname(__DIR__).'/bootstrap/app.php';
try{
    $report=(new \App\Services\Crm\Ao\AoSyncService(\App\Models\Database::getConnection()))->run();
    echo json_encode(['ok'=>$report['errors']===0,'report'=>$report],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;
    exit($report['errors']===0?0:1);
}catch(\Throwable $e){
    fwrite(STDERR,json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE).PHP_EOL);
    exit(1);
}
