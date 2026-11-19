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
   $missing=$this->missingDocuments($id);
   if($missing)throw new DomainException('Pièces obligatoires manquantes : '.implode(', ',$missing));
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

 public function requirements(string $type):array{
  $q=$this->db->prepare("SELECT document_type,label,required FROM procurement_supplier_document_requirements WHERE person_type=? AND active=1 ORDER BY sort_order,id");
  $q->execute([$type]);return $q->fetchAll(PDO::FETCH_ASSOC)?:[];
 }
 public function documents(int $id):array{
  $q=$this->db->prepare("SELECT id,document_type,original_name,file_size,uploaded_at FROM procurement_supplier_documents WHERE supplier_id=? ORDER BY uploaded_at DESC,id DESC");
  $q->execute([$id]);return $q->fetchAll(PDO::FETCH_ASSOC)?:[];
 }
 public function dossier(int $id):?array{
  $q=$this->db->prepare("SELECT * FROM procurement_suppliers WHERE id=?");$q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:null;
 }
 public function canReadDossier(int $id,int $actor,bool $admin):bool{
  if($admin)return true;
  $q=$this->db->prepare("SELECT 1 FROM procurement_suppliers WHERE id=? AND created_by=?");
  $q->execute([$id,$actor]);if($q->fetchColumn())return true;
  $q=$this->db->prepare("SELECT 1 FROM procurement_supplier_approval_runs r JOIN procurement_supplier_approval_run_steps st ON st.run_id=r.id WHERE r.supplier_id=? AND st.assigned_user_id=? LIMIT 1");
  $q->execute([$id,$actor]);return (bool)$q->fetchColumn();
 }
 public function documentRecord(int $id):?array{
  $q=$this->db->prepare("SELECT d.*,s.created_by,s.status FROM procurement_supplier_documents d JOIN procurement_suppliers s ON s.id=d.supplier_id WHERE d.id=?");
  $q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:null;
 }
 public function missingDocuments(int $id):array{
  $supplier=$this->dossier($id);if(!$supplier)throw new DomainException('Fournisseur introuvable.');
  $q=$this->db->prepare("SELECT r.label FROM procurement_supplier_document_requirements r WHERE r.person_type=? AND r.active=1 AND r.required=1 AND NOT EXISTS (SELECT 1 FROM procurement_supplier_documents d WHERE d.supplier_id=? AND d.document_type=r.document_type) ORDER BY r.sort_order");
  $q->execute([$supplier['person_type'],$id]);return $q->fetchAll(PDO::FETCH_COLUMN)?:[];
 }
 public function uploadDocument(int $id,string $type,array $file,int $actor):void{
  $supplier=$this->dossier($id);
  if(!$supplier||!in_array($supplier['status'],['brouillon','complement_requis'],true))throw new DomainException('Ce dossier ne peut plus être modifié.');
  if((int)$supplier['created_by']!==$actor)throw new DomainException('Seul le créateur peut déposer des pièces.');
  $requirements=$this->requirements($supplier['person_type']);
  if(!in_array($type,array_column($requirements,'document_type'),true))throw new DomainException('Type de document non autorisé.');
  if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_uploaded_file($file['tmp_name']??''))throw new DomainException('Fichier non reçu.');
  $size=(int)($file['size']??0);
  if($size<1||$size>10*1024*1024)throw new DomainException('Fichier limité à 10 Mo.');
  $mime=(new \\finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
  $extensions=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png'];
  if(!isset($extensions[$mime]))throw new DomainException('Formats acceptés : PDF, JPG et PNG.');
  $folder=BASE_PATH.'/storage/private/suppliers/'.$id;
  if(!is_dir($folder)&&!mkdir($folder,0700,true)&&!is_dir($folder))throw new DomainException('Stockage indisponible.');
  $name=bin2hex(random_bytes(18)).'.'.$extensions[$mime];$path=$folder.'/'.$name;
  if(!move_uploaded_file($file['tmp_name'],$path))throw new DomainException('Enregistrement du fichier impossible.');
  chmod($path,0600);
  try{
   $q=$this->db->prepare("INSERT INTO procurement_supplier_documents(supplier_id,document_type,original_name,storage_name,mime_type,file_size,sha256,uploaded_by) VALUES(?,?,?,?,?,?,?,?)");
   $original=basename((string)($file['name']??'document'));$q->execute([$id,$type,mb_substr($original,0,255),$name,$mime,$size,hash_file('sha256',$path),$actor]);
  }catch(\\Throwable $e){@unlink($path);throw $e;}
 }
 public function setRequirement(string $type,string $docType,bool $required,bool $active):void{
  if(!in_array($type,['physique','morale'],true))throw new DomainException('Nature juridique invalide.');
  $q=$this->db->prepare("UPDATE procurement_supplier_document_requirements SET required=?,active=? WHERE person_type=? AND document_type=?");
  $q->execute([(int)$required,(int)$active,$type,$docType]);
  if(!$q->rowCount()){
   $q=$this->db->prepare("SELECT COUNT(*) FROM procurement_supplier_document_requirements WHERE person_type=? AND document_type=?");$q->execute([$type,$docType]);
   if(!(int)$q->fetchColumn())throw new DomainException('Exigence introuvable.');
  }
 }

 public function eligible():array{return $this->db->query("SELECT id,legal_name,person_type,supplier_code,email,phone,tax_identifier,address FROM procurement_suppliers WHERE status='approuve' ORDER BY legal_name")->fetchAll()?:[];}
 public function approved(int $id,bool $lock=false):?array{$q=$this->db->prepare("SELECT id,legal_name,person_type,supplier_code,email,phone,tax_identifier,address FROM procurement_suppliers WHERE id=? AND status='approuve'".($lock?' FOR UPDATE':''));$q->execute([$id]);return $q->fetch()?:null;}
}
