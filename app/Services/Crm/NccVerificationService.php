<?php
declare(strict_types=1);
namespace App\Services\Crm;
use DomainException;
final class NccVerificationService {
 private const ENDPOINT='https://www.services.fne.dgi.gouv.ci/ws/ncc-verification';
 public function check(string $ncc):array {
  $ncc=strtoupper(trim($ncc));
  if(!preg_match('/^[A-Z0-9-]{4,24}$/',$ncc))throw new DomainException('Format NCC invalide.');
  if(!function_exists('curl_init'))throw new DomainException('Extension cURL indisponible : vérification NCC impossible.');
  $ch=curl_init(self::ENDPOINT.'?'.http_build_query(['ncc'=>$ncc]));
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_CONNECTTIMEOUT=>4,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_HTTPHEADER=>['Accept: application/json','Cache-Control: no-cache']]);
  $body=curl_exec($ch);$http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
  if(!is_string($body)||$http!==200)throw new DomainException('Service NCC temporairement indisponible. Enregistrement non validé.');
  $data=json_decode($body,true);
  return $this->validateResponse($ncc,$data); 
 }
 public function validateResponse(string $ncc,mixed $data):array {
  if(!is_array($data)||!array_key_exists('ncc',$data)||!array_key_exists('status',$data)||!is_bool($data['status']))throw new DomainException('Réponse NCC inexploitable.');
  if(strtoupper((string)$data['ncc'])!==$ncc)throw new DomainException('Incohérence de numéro NCC dans la réponse officielle.');
  if($data['status']!==true)throw new DomainException('NCC non reconnu par le service DGI.');
  $activity=mb_strtolower(trim((string)($data['statusActivity']??'')));
  if($activity===''||in_array($activity,['cessation','cesse','cessée','inactive','inactif','suspendu','suspension','radiation','radie'],true))throw new DomainException('NCC trouvé, mais activité non confirmée comme active (statut : '.($activity?:'inconnu').').');
  // Un statut non documenté n'est pas assimilé à une activité active.
  if(!in_array($activity,['active','actif','en activité','en activite'],true))throw new DomainException('Statut d’activité NCC non reconnu : '.$activity.'. Vérification manuelle nécessaire.');
  return ['name'=>trim((string)($data['name']??'')),'activity'=>$activity,'ncc'=>$ncc];
 }
}
