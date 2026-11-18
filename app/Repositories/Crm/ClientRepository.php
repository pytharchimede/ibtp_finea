<?php
declare(strict_types=1);
namespace App\Repositories\Crm;
use PDO;
final class ClientRepository {
 public function __construct(private PDO $pdo){}
 public function all(string|array $search=''):array{
  if(is_array($search))return $this->filter($search);
  $q=$this->pdo->prepare('SELECT id,type,name,contact_name,email,phone,city,status FROM crm_clients WHERE name LIKE :q OR contact_name LIKE :q2 ORDER BY id DESC LIMIT 200');
  $q->execute(['q'=>'%'.$search.'%','q2'=>'%'.$search.'%']);return $q->fetchAll(PDO::FETCH_ASSOC);
 }
 public function filter(array $filters):array{
  $sql='SELECT id,type,name,contact_name,email,phone,country,city,sector,status,legal_form,identity_country,identifier_kind,identifier_value,cni_present,verification_status,verification_note,verification_override_reason FROM crm_clients WHERE 1=1';
  $params=[];
  foreach(['q','type','status','city','verification_status','identity_country'] as $key){
   $value=mb_substr(trim((string)($filters[$key]??'')),0,100);
   if($value==='')continue;
   if($key==='q'){$sql.=' AND (name LIKE :q1 OR contact_name LIKE :q2 OR email LIKE :q3 OR phone LIKE :q4 OR identifier_value LIKE :q5)';foreach(['q1','q2','q3','q4','q5'] as $p)$params[$p]='%'.$value.'%';}
   elseif($key==='city'){$sql.=' AND city LIKE :city';$params['city']='%'.$value.'%';}
   else{$sql.=' AND '.$key.' = :'.$key;$params[$key]=$value;}
  }
  $sql.=' ORDER BY id DESC LIMIT 1000';
  $q=$this->pdo->prepare($sql);$q->execute($params);return $q->fetchAll(PDO::FETCH_ASSOC);
 }
 public function changeType(int $id,string $type):void{
  $q=$this->pdo->prepare('UPDATE crm_clients SET type=:type,updated_at=NOW() WHERE id=:id');
  $q->execute(['type'=>$type,'id'=>$id]);
  if(!$this->exists($id))throw new \DomainException('Client introuvable.');
 }
 public function create(array $data,int $actor):int{
  $q=$this->pdo->prepare('INSERT INTO crm_clients(type,name,contact_name,email,phone,country,city,sector,status,notes,legal_form,identity_country,identifier_kind,identifier_value,cni_present,verification_status,verification_override_reason,verification_override_by,verification_override_at,verification_note,created_by) VALUES(:type,:name,:contact_name,:email,:phone,:country,:city,:sector,:status,:notes,:legal_form,:identity_country,:identifier_kind,:identifier_value,:cni_present,:verification_status,:verification_override_reason,:verification_override_by,:verification_override_at,:verification_note,:created_by)');
  $q->execute($data+['created_by'=>$actor]);return (int)$this->pdo->lastInsertId();
 }
 public function update(int $id,array $data):void{
  $data['id']=$id;$q=$this->pdo->prepare('UPDATE crm_clients SET type=:type,name=:name,contact_name=:contact_name,email=:email,phone=:phone,country=:country,city=:city,sector=:sector,status=:status,notes=:notes,legal_form=:legal_form,identity_country=:identity_country,identifier_kind=:identifier_kind,identifier_value=:identifier_value,cni_present=:cni_present,verification_status=:verification_status,verification_override_reason=:verification_override_reason,verification_override_by=:verification_override_by,verification_override_at=:verification_override_at,verification_note=:verification_note,verification_provider=NULL,verified_at=NULL,updated_at=NOW() WHERE id=:id');
  $q->execute($data);if($q->rowCount()===0 && !$this->exists($id))throw new \DomainException('Client introuvable.');
 }
 public function exists(int $id):bool{$q=$this->pdo->prepare('SELECT 1 FROM crm_clients WHERE id=?');$q->execute([$id]);return (bool)$q->fetchColumn();}
 public function find(int $id):?array{$q=$this->pdo->prepare('SELECT * FROM crm_clients WHERE id=?');$q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:null;}
}
