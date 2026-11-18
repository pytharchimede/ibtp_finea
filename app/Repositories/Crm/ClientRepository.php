<?php
declare(strict_types=1);
namespace App\Repositories\Crm;
use PDO;
final class ClientRepository {
 public function __construct(private PDO $pdo){}
 public function all(string $search=''):array{
  $q=$this->pdo->prepare('SELECT id,type,name,contact_name,email,phone,city,status FROM crm_clients WHERE name LIKE :q OR contact_name LIKE :q2 ORDER BY id DESC LIMIT 200');
  $q->execute(['q'=>'%'.$search.'%','q2'=>'%'.$search.'%']);return $q->fetchAll(PDO::FETCH_ASSOC);
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
