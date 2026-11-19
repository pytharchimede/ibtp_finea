<?php
declare(strict_types=1);
namespace App\Services\Procurement;
use PDO;use DomainException;
final class SupplierService {
 public function __construct(private PDO $db){}
 public function list():array{return $this->db->query("SELECT s.*,u.full_name creator_name FROM procurement_suppliers s JOIN users u ON u.id=s.created_by ORDER BY s.id DESC LIMIT 300")->fetchAll()?:[];}
 public function workflows():array{return $this->db->query("SELECT * FROM procurement_supplier_workflows ORDER BY id DESC")->fetchAll()?:[];}
 public function users():array{return $this->db->query("SELECT id,full_name FROM users WHERE status='active' ORDER BY full_name")->fetchAll()?:[];}
 public function workflowSteps(int $id):array{$q=$this->db->prepare("SELECT s.*,u.full_name assigned_name FROM procurement_supplier_workflow_steps s JOIN users u ON u.id=s.assigned_user_id WHERE workflow_id=? ORDER BY step_order");$q->execute([$id]);return $q->fetchAll()?:[];}
 public function create(array $data,int $actor):int{
  $type=(string)($data['person_type']??'');$name=trim((string)($type==='physique'?($data['individual_name']??''):($data['company_name']??'')));
  if(!in_array($type,['physique','morale'],true)||$name===''||mb_strlen($name)>200)throw new DomainException('Type et nom du fournisseur obligatoires.');
  $data['tax_identifier']=$type==='physique'?($data['individual_tax_identifier']??''):($data['tax_identifier']??'');
  $data['trade_register']=$type==='physique'?($data['individual_trade_register']??''):($data['trade_register']??'');
  $data['trade_name']=$type==='physique'?($data['individual_activity']??''):($data['trade_name']??'');
  $data['contact_name']=$type==='physique'?$name:($data['contact_name']??'');
  $code='FRN-'.bin2hex(random_bytes(6));$q=$this->db->prepare("INSERT INTO procurement_suppliers(supplier_code,person_type,legal_name,trade_name,contact_name,email,phone,address,country_code,tax_identifier,trade_register,bank_name,bank_iban,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $q->execute([$code,$type,$name,trim((string)($data['trade_name']??'')),trim((string)($data['contact_name']??'')),trim((string)($data['email']??'')),trim((string)($data['phone']??'')),trim((string)($data['address']??'')),strtoupper(substr((string)($data['country_code']??'CI'),0,2)),trim((string)($data['tax_identifier']??'')),trim((string)($data['trade_register']??'')),trim((string)($data['bank_name']??'')),trim((string)($data['bank_iban']??'')),$actor]);return (int)$this->db->lastInsertId();
 }
 public function createWorkflow(array $data):int{
  $label=trim((string)($data['label']??''));$assignees=array_values(array_filter(array_map('intval',(array)($data['assignees']??[]))));
  if($label===''||!$assignees||count($assignees)>12)throw new DomainException('Définissez un nom et 1 à 12 approbateurs.');
  if(count(array_unique($assignees))!==count($assignees))throw new DomainException('Un approbateur ne peut pas être affecté deux fois.');
  $this->db->beginTransaction();try{
   $q=$this->db->prepare("INSERT INTO procurement_supplier_workflows(code,label,active) VALUES(?,?,0)");$q->execute(['FRN-'.bin2hex(random_bytes(5)),$label]);$id=(int)$this->db->lastInsertId();
   $q=$this->db->prepare("INSERT INTO procurement_supplier_workflow_steps(workflow_id,step_order,label,assigned_user_id) VALUES(?,?,?,?)");
   foreach($assignees as $i=>$user){$q->execute([$id,$i+1,'Validation '.($i+1),$user]);}
   $this->db->commit();return $id;
  }catch(\Throwable $e){$this->db->rollBack();throw $e;}
 }
 public function activateWorkflow(int $id):void{
  $this->db->beginTransaction();try{
   $q=$this->db->prepare("SELECT COUNT(*) FROM procurement_supplier_workflow_steps WHERE workflow_id=?");$q->execute([$id]);if((int)$q->fetchColumn()<1)throw new DomainException('Circuit sans approbateur.');
   $this->db->exec("UPDATE procurement_supplier_workflows SET active=0");$q=$this->db->prepare("UPDATE procurement_supplier_workflows SET active=1 WHERE id=?");$q->execute([$id]);if(!$q->rowCount())throw new DomainException('Circuit introuvable.');
   $this->db->commit();
  }catch(\Throwable $e){$this->db->rollBack();throw $e;}
 }
 public function submit(int $id,int $actor):void{
  $this->db->beginTransaction();try{
   $q=$this->db->prepare("SELECT * FROM procurement_suppliers WHERE id=? FOR UPDATE");$q->execute([$id]);$supplier=$q->fetch();
   if(!$supplier||!in_array($supplier['status'],['brouillon','complement_requis'],true))throw new DomainException('Dossier non soumettable.');
   if((int)$supplier['created_by']!==$actor)throw new DomainException('Seul le créateur peut soumettre ce dossier.');
   $workflow=$this->db->query("SELECT id FROM procurement_supplier_workflows WHERE active=1 ORDER BY id LIMIT 1")->fetchColumn();
   if(!$workflow)throw new DomainException('Activez d’abord un circuit d’agrément.');
   $steps=$this->workflowSteps((int)$workflow);if(!$steps)throw new DomainException('Circuit sans étapes.');
   $q=$this->db->prepare("INSERT INTO procurement_supplier_approval_runs(supplier_id,workflow_id,submitted_by) VALUES(?,?,?)");$q->execute([$id,$workflow,$actor]);$run=(int)$this->db->lastInsertId();
   $q=$this->db->prepare("INSERT INTO procurement_supplier_approval_run_steps(run_id,step_order,label,assigned_user_id,status) VALUES(?,?,?,?,?)");
   foreach($steps as $i=>$step)$q->execute([$run,$step['step_order'],$step['label'],$step['assigned_user_id'],$i===0?'a_traiter':'en_attente']);
   $q=$this->db->prepare("UPDATE procurement_suppliers SET status='soumis',submitted_at=NOW() WHERE id=?");$q->execute([$id]);
   $this->history($id,$supplier['status'],'soumis','Dossier soumis au circuit',$actor);
   $this->db->commit();
  }catch(\Throwable $e){$this->db->rollBack();throw $e;}
 }
 public function decide(int $id,string $decision,string $comment,int $actor):void{
  if(!in_array($decision,['approuve','refuse','complement_requis'],true))throw new DomainException('Décision invalide.');
  if($decision!=='approuve'&&trim($comment)==='')throw new DomainException('Motif obligatoire.');
  $this->db->beginTransaction();try{
   $q=$this->db->prepare("SELECT * FROM procurement_suppliers WHERE id=? FOR UPDATE");$q->execute([$id]);$supplier=$q->fetch();if(!$supplier||$supplier['status']!=='soumis')throw new DomainException('Dossier non soumis.');
   $q=$this->db->prepare("SELECT * FROM procurement_supplier_approval_runs WHERE supplier_id=? AND status='en_cours' ORDER BY id DESC LIMIT 1 FOR UPDATE");$q->execute([$id]);$run=$q->fetch();if(!$run)throw new DomainException('Circuit en cours introuvable.');
   $q=$this->db->prepare("SELECT * FROM procurement_supplier_approval_run_steps WHERE run_id=? AND status='a_traiter' FOR UPDATE");$q->execute([$run['id']]);$step=$q->fetch();
   if(!$step||(int)$step['assigned_user_id']!==$actor)throw new DomainException('Vous n’êtes pas l’approbateur désigné.');
   $q=$this->db->prepare("UPDATE procurement_supplier_approval_run_steps SET status=?,decided_by=?,decided_at=NOW(),comment=? WHERE id=?");$q->execute([$decision,$actor,$comment,$step['id']]);
   if($decision==='approuve'){
    $q=$this->db->prepare("SELECT id FROM procurement_supplier_approval_run_steps WHERE run_id=? AND status='en_attente' ORDER BY step_order LIMIT 1");$q->execute([$run['id']]);$next=$q->fetchColumn();
    if($next){$q=$this->db->prepare("UPDATE procurement_supplier_approval_run_steps SET status='a_traiter' WHERE id=?");$q->execute([$next]);}
    else{$q=$this->db->prepare("UPDATE procurement_supplier_approval_runs SET status='approuve',completed_at=NOW() WHERE id=?");$q->execute([$run['id']]);$q=$this->db->prepare("UPDATE procurement_suppliers SET status='approuve',reviewed_at=NOW(),reviewed_by=?,review_comment=? WHERE id=?");$q->execute([$actor,$comment,$id]);}
   }else{
    $q=$this->db->prepare("UPDATE procurement_supplier_approval_runs SET status=?,completed_at=NOW() WHERE id=?");$q->execute([$decision,$run['id']]);
    $q=$this->db->prepare("UPDATE procurement_suppliers SET status=?,reviewed_at=NOW(),reviewed_by=?,review_comment=? WHERE id=?");$q->execute([$decision,$actor,$comment,$id]);
   }
   $this->history($id,'soumis',$decision,$comment,$actor);$this->db->commit();
  }catch(\Throwable $e){$this->db->rollBack();throw $e;}
 }
 private function history(int $id,string $old,string $new,string $comment,int $actor):void{$q=$this->db->prepare("INSERT INTO procurement_supplier_reviews(supplier_id,previous_status,new_status,comment,actor_user_id) VALUES(?,?,?,?,?)");$q->execute([$id,$old,$new,$comment,$actor]);}
 public function eligible():array{return $this->db->query("SELECT id,legal_name,person_type,supplier_code,email,phone,tax_identifier,address FROM procurement_suppliers WHERE status='approuve' ORDER BY legal_name")->fetchAll()?:[];}
 public function approved(int $id,bool $lock=false):?array{$q=$this->db->prepare("SELECT id,legal_name,person_type,supplier_code,email,phone,tax_identifier,address FROM procurement_suppliers WHERE id=? AND status='approuve'".($lock?' FOR UPDATE':''));$q->execute([$id]);return $q->fetch()?:null;}
}
