<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/Services/Crm/NccVerificationService.php';
use App\Services\Crm\NccVerificationService;
$service=new NccVerificationService();
$cases=[
 ['name'=>'cessation','payload'=>['ncc'=>'2242142V','name'=>'SOCIETE BECANTY GROUP','taxRegime'=>'TEE','status'=>true,'statusActivity'=>'cessation'],'reject'=>true],
 ['name'=>'active','payload'=>['ncc'=>'2242142V','name'=>'SOCIETE TEST','status'=>true,'statusActivity'=>'active'],'reject'=>false],
 ['name'=>'unknown','payload'=>['ncc'=>'2242142V','status'=>false,'statusActivity'=>'active'],'reject'=>true],
 ['name'=>'mismatch','payload'=>['ncc'=>'DIFFERENT','status'=>true,'statusActivity'=>'active'],'reject'=>true],
 ['name'=>'unrecognized activity','payload'=>['ncc'=>'2242142V','status'=>true,'statusActivity'=>'unknown'],'reject'=>true],
 ['name'=>'missing activity','payload'=>['ncc'=>'2242142V','status'=>true],'reject'=>true],
 ['name'=>'malformed','payload'=>['ncc'=>'2242142V','status'=>'true','statusActivity'=>'active'],'reject'=>true],
];
foreach($cases as $case){
 $rejected=false;
 try{$service->validateResponse('2242142V',$case['payload']);}
 catch(\DomainException $e){$rejected=true;}
 if($rejected!==$case['reject']){fwrite(STDERR,'FAILED: '.$case['name'].PHP_EOL);exit(1);}
 echo 'PASS: '.$case['name'].PHP_EOL;
}
