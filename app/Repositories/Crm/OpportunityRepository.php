<?php
declare(strict_types=1);
namespace App\Repositories\Crm;
use PDO;
final class OpportunityRepository {
 public function __construct(private PDO $pdo){}
 public function search(array $filters):array{
  $sql="SELECT o.id,o.client_id,o.title,o.stage,o.estimated_amount,o.currency,o.expected_close_date,o.deadline,o.probability,o.description,c.name AS client_name,(SELECT COUNT(*) FROM crm_opportunity_attachments a WHERE a.opportunity_id=o.id) AS attachment_count FROM crm_opportunities o INNER JOIN crm_clients c ON c.id=o.client_id WHERE 1=1";
  $params=[];
  $q=trim((string)($filters['q']??''));
  if($q!==''){$sql.=" AND (o.title LIKE :q1 OR c.name LIKE :q2 OR o.description LIKE :q3)";foreach(['q1','q2','q3'] as $key)$params[$key]='%'.mb_substr($q,0,100).'%';}
  $stage=(string)($filters['stage']??'');
  if(in_array($stage,['qualification','proposition','negociation','gagnee','perdue'],true)){$sql.=" AND o.stage=:stage";$params['stage']=$stage;}
  $client=(int)($filters['client_id']??0);
  if($client>0){$sql.=" AND o.client_id=:client_id";$params['client_id']=$client;}
  foreach(['deadline_from','deadline_to'] as $key){
   $date=(string)($filters[$key]??'');
   if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)&&checkdate((int)substr($date,5,2),(int)substr($date,8,2),(int)substr($date,0,4))){
    $sql.=' AND o.deadline '.($key==='deadline_from'?'>=':'<=').' :'.$key;$params[$key]=$date;
   }
  }
  $sql.=" ORDER BY o.id DESC LIMIT 1000";
  $stmt=$this->pdo->prepare($sql);$stmt->execute($params);return $stmt->fetchAll(PDO::FETCH_ASSOC);
 }
 public function clients():array{return $this->pdo->query('SELECT DISTINCT c.id,c.name FROM crm_clients c JOIN crm_opportunities o ON o.client_id=c.id ORDER BY c.name')->fetchAll(PDO::FETCH_ASSOC);}
}
