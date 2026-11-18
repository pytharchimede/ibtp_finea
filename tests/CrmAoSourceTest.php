<?php
declare(strict_types=1);
/** Test CLI sans connexion réseau ni base de données. */
require_once dirname(__DIR__) . '/app/Services/Crm/Ao/AoSourceAdapterInterface.php';
require_once dirname(__DIR__) . '/app/Services/Crm/Ao/AoOpportunity.php';
require_once dirname(__DIR__) . '/app/Services/Crm/Ao/AoScoringService.php';
require_once dirname(__DIR__) . '/app/Services/Crm/Ao/SigomapAoAdapter.php';
require_once dirname(__DIR__) . '/app/Services/Crm/Ao/ContenuLocalAoAdapter.php';

use App\Services\Crm\Ao\AoOpportunity;
use App\Services\Crm\Ao\AoScoringService;
use App\Services\Crm\Ao\SigomapAoAdapter;
use App\Services\Crm\Ao\ContenuLocalAoAdapter;

function check(bool $ok,string $message):void {
    if(!$ok)throw new RuntimeException($message);
}

$ao=AoOpportunity::prepare([
    'source'=>'DGMP public','reference'=>' AO 2026 / 001 ',
    'titre'=>'Travaux de construction d’une école',
    'date_limite'=>'31/12/2026',
]);
check($ao['reference_normalized']==='AO2026001','Normalisation de référence invalide.');
check($ao['date_limite']==='2026-12-31','Date limite invalide.');
check($ao['signature_hash']!=='' && $ao['hash_unique']!=='','Signature AO absente.');

$scorer=new AoScoringService();
$score=$scorer->score(['titre'=>'Travaux de construction d’une école','resume'=>'Réhabilitation bâtiment']);
check($score>=60 && $score<=100,'Scoring métier BTP inattendu.');

$dgmp=(new SigomapAoAdapter())->normalizeOpportunity([
    'reference'=>'MP-01','titre'=>'Construction route','date_limite'=>'2026-12-31'
]);
check($dgmp['source']==='DGMP public','Libellé source DGMP invalide.');

$dgh=(new ContenuLocalAoAdapter(['username'=>'','password'=>'']))->normalizeOpportunity([
    'id'=>55,'title'=>'Réhabilitation d’un bâtiment','publishedAt'=>'2026-10-10T12:00:00',
    'launchEndAt'=>'2026-12-03T18:00:00','status'=>'PUBLISHED'
]);
check($dgh['reference']==='DGH-55','Identifiant DGH inattendu.');
check($dgh['date_limite']==='2026-12-03','Date DGH inattendue.');

echo "Tests AO : normalisation, pertinence, DGMP et DGH OK\n";
