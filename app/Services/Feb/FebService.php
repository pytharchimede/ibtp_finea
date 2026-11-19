<?php
declare(strict_types=1);
namespace App\Services\Feb;
use App\Repositories\Feb\FebRepository;
use DomainException;use PDO;
final class FebService {
 public function __construct(private FebRepository $repo){}
 public function create(array $data,array $files,int $actor):int{
  $scope=(string)($data['scope']??'chantier');
  if(!in_array($scope,['chantier','bureau'],true))throw new DomainException('Type de FEB invalide.');
  $chantier=(int)($data['chantier_id']??0);
  $service=(int)($data['service_id']??0);
  $allocation=(int)($data['office_allocation_id']??0);
  // Le circuit FEB est un paramètre administrateur, jamais un choix du demandeur.
  $activeWorkflows=$this->repo->workflows();
  if(!$activeWorkflows)throw new DomainException('Aucun circuit FEB actif. Contactez l’administrateur.');
  $workflow=(int)$activeWorkflows[0]['id'];
  $type=(string)($data['beneficiary_type']??'');
  $employee=$type==='beneficiaire'?$this->repo->employeeForUser($actor):null;
  if($type==='beneficiaire'&&!$employee)throw new DomainException('Votre compte doit être rattaché à une fiche salarié RH active.');
  $supplierId=$type==='fournisseur'?(int)($data['supplier_id']??0):0;
  $name=$employee?(string)$employee['full_name']:($type==='fournisseur'?'En attente de vérification':'');
  $purpose=trim((string)($data['purpose']??''));
  $date=(string)($data['needed_at']??'');
  $parsedDate=\DateTimeImmutable::createFromFormat('!Y-m-d',$date);
  if(!$workflow||$name===''||$purpose===''||!in_array($type,['fournisseur','beneficiaire'],true)||!$parsedDate||$parsedDate->format('Y-m-d')!==$date)throw new DomainException('Informations générales incomplètes.');
  if($scope==='chantier'&&!$chantier)throw new DomainException('Sélectionnez un chantier.');
  if($scope==='bureau'&&(!$service||!$allocation))throw new DomainException('Sélectionnez un service et son affectation budgétaire.');
  $lines=$this->parseLines((array)($data['lines']??[]),$scope);
  if(!$lines)throw new DomainException('Ajoutez au moins une ligne de besoin.');
  $pdo=$this->repo->pdo();$uploads=[];$uploader=new FebUploadService();
  $pdo->beginTransaction();
  try{
   $supplier=null;
   if($type==='fournisseur'){
    $supplier=$supplierId>0?$this->repo->approvedSupplier($supplierId,true):null;
    if(!$supplier)throw new DomainException('Sélectionnez un fournisseur dont le dossier est approuvé.');
    $name=(string)$supplier['legal_name'];
   }
   if($scope==='chantier'){
    if(!$this->repo->hasChantierAccess($actor,$chantier,'demander',true))throw new DomainException('Vous n’êtes pas affecté à ce chantier pour créer une FEB.');
    $allowed=array_column($this->repo->budgetLines($chantier,true),null,'id');
    foreach($lines as $line){
     $id=$line['budget_line_id'];if(!isset($allowed[$id]))throw new DomainException('Ligne hors du déboursé validé de ce chantier.');
     if((int)$line['quote_line_id']!==(int)$allowed[$id]['quote_line_id']||(int)$line['debourse_id']!==(int)$allowed[$id]['budget_id'])
      throw new DomainException('La chaîne devis → déboursé → ligne de déboursé est incohérente.');
    }
   }else{
    if(!$this->repo->officeRoleAuthorized($actor,'demander'))throw new DomainException('Création de FEB bureau non autorisée.');
    if(!$this->repo->officeAllocation($allocation,$service,true))throw new DomainException('Affectation budgétaire bureau indisponible ou non validée.');
   }
   $q=$pdo->prepare("INSERT INTO feb_requests(requester_id,chantier_id,workflow_id,beneficiary_type,beneficiary_name,beneficiary_details,purpose,needed_at,scope,service_id,office_allocation_id,supplier_id) SELECT ?,?,?,?,?,?,?,?,?,?,?,? FROM feb_workflows w WHERE w.id=? AND w.active=1");
   $q->execute([$actor,$scope==='chantier'?$chantier:null,$workflow,$type,$name,($employee?('Matricule : '.$employee['employee_number'].' · Service : '.($employee['service_name']??'').' · Fonction : '.($employee['function_name']??'').' · '.($employee['email']??'').' · '.($employee['phone']??'')):('Code : '.$supplier['supplier_code'].' · NCC : '.($supplier['tax_identifier']??'').' · '.($supplier['email']??'').' · '.($supplier['phone']??''))),$purpose,$date,$scope,$scope==='bureau'?$service:null,$scope==='bureau'?$allocation:null,$supplierId?:null,$workflow]);
   if(!$q->rowCount())throw new DomainException('Workflow invalide.');$id=(int)$pdo->lastInsertId();
   $ins=$pdo->prepare('INSERT INTO feb_request_items(request_id,budget_line_id,office_allocation_id,description,quantity,unit,unit_amount,total_amount) VALUES(?,?,?,?,?,?,?,?)');
   foreach($lines as $line){
    $ins->execute([$id,$scope==='chantier'?$line['budget_line_id']:null,$scope==='bureau'?$allocation:null,$line['description'],$line['quantity'],$line['unit'],$line['unit_amount'],$line['total_amount']]);
   }
   $attachments=$files['attachments']??null;
   if(is_array($attachments)&&isset($attachments['name'])&&is_array($attachments['name'])){
    $count=count($attachments['name']);
    if($count>10)throw new DomainException('Maximum 10 justificatifs par FEB.');
    for($i=0;$i<$count;$i++){
     $file=[];foreach(['name','type','tmp_name','error','size'] as $key)$file[$key]=$attachments[$key][$i]??null;
     $upload=$uploader->store($file);if($upload)$uploads[]=$upload;
    }
   }else{
    $upload=$uploader->store($files['attachment']??null);if($upload)$uploads[]=$upload;
   }
   if(!$uploads)throw new DomainException('Ajoutez au moins un justificatif.');
   $q=$pdo->prepare('INSERT INTO feb_documents(request_id,category,original_name,storage_name,mime_type,file_size,sha256,uploaded_by) VALUES(?,?,?,?,?,?,?,?)');
   foreach($uploads as $upload)$q->execute([$id,'justificatif',$upload['original_name'],$upload['storage_name'],$upload['mime_type'],$upload['file_size'],$upload['sha256'],$actor]);
   $this->history($id,$actor,'creation',null,'brouillon',['scope'=>$scope]);
   $pdo->commit();return $id;
  }catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();foreach($uploads as $uploaded)$uploader->discard($uploaded);throw $e;}
 }
 public function submit(int $id,int $actor):void{$pdo=$this->repo->pdo();$pdo->beginTransaction();try{$r=$this->repo->find($id,true);if(!$r||!in_array($r['status'],['brouillon','complement'],true)||(int)$r['requester_id']!==$actor||!$this->repo->canActOnRequest($r,$actor,'demander'))throw new DomainException('FEB non soumettable.');if(!$r['documents'])throw new DomainException('Un justificatif est obligatoire.');$this->assertBudget($r);$step=$r['current_step_id'];if(!$step){$q=$pdo->prepare('SELECT id FROM feb_workflow_steps WHERE workflow_id=? AND active=1 ORDER BY step_order LIMIT 1');$q->execute([$r['workflow_id']]);$step=(int)$q->fetchColumn();}if(!$step)throw new DomainException('Workflow sans étape active.');$ref=$r['reference']?:sprintf('FEB-%s-%06d',date('Y'),$id);$q=$pdo->prepare("UPDATE feb_requests SET reference=?,status='soumis',current_step_id=?,submitted_at=COALESCE(submitted_at,NOW()),version=version+1 WHERE id=? AND version=?");$q->execute([$ref,$step,$id,$r['version']]);if(!$q->rowCount())throw new DomainException('La FEB a été modifiée simultanément.');$this->history($id,$actor,$r['status']==='complement'?'complement_soumis':'soumission',$r['status'],'soumis');$pdo->commit();}catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}}
 public function addDocument(int $id,array $file,int $actor):void{
  $check=$this->repo->find($id);if(!$check||(int)$check['requester_id']!==$actor||!in_array($check['status'],['brouillon','complement'],true)||!$this->repo->canActOnRequest($check,$actor,'demander'))throw new DomainException('Complément non autorisé.');
  $uploader=new FebUploadService();$upload=$uploader->store($file);if(!$upload)throw new DomainException('Sélectionnez une pièce.');$pdo=$this->repo->pdo();
  try{$pdo->beginTransaction();$r=$this->repo->find($id,true);if(!$r||(int)$r['requester_id']!==$actor||!in_array($r['status'],['brouillon','complement'],true)||!$this->repo->hasChantierAccess($actor,(int)$r['chantier_id'],'demander',true))throw new DomainException('Complément non autorisé.');$q=$pdo->prepare('INSERT INTO feb_documents(request_id,category,original_name,storage_name,mime_type,file_size,sha256,uploaded_by) VALUES(?,?,?,?,?,?,?,?)');$q->execute([$id,'justificatif',$upload['original_name'],$upload['storage_name'],$upload['mime_type'],$upload['file_size'],$upload['sha256'],$actor]);$this->history($id,$actor,'document_ajoute',$r['status'],$r['status'],['name'=>$upload['original_name']]);$pdo->commit();}
  catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$uploader->discard($upload);throw $e;}
 }
 public function cancel(int $id,int $actor):void{$pdo=$this->repo->pdo();$pdo->beginTransaction();try{$r=$this->repo->find($id,true);if(!$r||(int)$r['requester_id']!==$actor||!in_array($r['status'],['brouillon','soumis','complement'],true)||!$this->repo->hasChantierAccess($actor,(int)$r['chantier_id'],'demander',true))throw new DomainException('Annulation non autorisée.');$q=$pdo->prepare("UPDATE feb_requests SET status='annule',current_step_id=NULL,cancelled_at=NOW(),version=version+1 WHERE id=? AND version=?");$q->execute([$id,$r['version']]);if(!$q->rowCount())throw new DomainException('Modification concurrente.');$this->history($id,$actor,'annulation',$r['status'],'annule');$pdo->commit();}catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}}
 public function decide(int $id,string $decision,string $comment,int $actor):void{if(!in_array($decision,['approuve','refuse','complement'],true))throw new DomainException('Décision invalide.');if($decision!=='approuve'&&trim($comment)==='')throw new DomainException('Un commentaire est obligatoire.');$pdo=$this->repo->pdo();$pdo->beginTransaction();try{$r=$this->repo->find($id,true);if(!$r||!$r['current_step_id']||!$this->repo->canActOnRequest($r,$actor,'valider'))throw new DomainException('FEB déjà traitée ou chantier non autorisé.');FebSecurityRules::assertDecidableStatus((string)$r['status']);$q=$pdo->prepare('SELECT * FROM feb_workflow_steps WHERE id=? AND active=1 FOR UPDATE');$q->execute([$r['current_step_id']]);$step=$q->fetch();if(!$step||($step['assigned_user_id']&&(int)$step['assigned_user_id']!==$actor))throw new DomainException('Cette décision ne vous est pas affectée.');if($decision==='approuve')$this->assertBudget($r);$q=$pdo->prepare('INSERT INTO feb_decisions(request_id,step_id,actor_id,decision,comment,request_version) VALUES(?,?,?,?,?,?)');$q->execute([$id,$step['id'],$actor,$decision,trim($comment),$r['version']]);$to=$decision==='refuse'?'refuse':($decision==='complement'?'complement':'soumis');$next=null;if($decision==='approuve'){$q=$pdo->prepare('SELECT id FROM feb_workflow_steps WHERE workflow_id=? AND step_order>? AND active=1 ORDER BY step_order LIMIT 1');$q->execute([$r['workflow_id'],$step['step_order']]);$next=$q->fetchColumn()?:null;if(!$next)$to='approuve';}$q=$pdo->prepare('UPDATE feb_requests SET status=?,current_step_id=?,approved_at=IF(?="approuve",NOW(),approved_at),version=version+1 WHERE id=? AND version=?');$q->execute([$to,$next,$to,$id,$r['version']]);if(!$q->rowCount())throw new DomainException('Décision concurrente détectée.');$this->history($id,$actor,'decision_'.$decision,$r['status'],$to,['comment'=>$comment]);$pdo->commit();}catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}}
 public function pay(int $id,array $data,int $actor):void{$amount=round((float)($data['amount']??0),2);$ref=trim((string)($data['payment_reference']??''));$method=trim((string)($data['method']??''));if($amount<=0||$ref===''||$method==='')throw new DomainException('Paiement incomplet.');$pdo=$this->repo->pdo();$pdo->beginTransaction();try{$r=$this->repo->find($id,true);if(!$r||!in_array($r['status'],['approuve','partiellement_paye'],true)||!$this->repo->canActOnRequest($r,$actor,'payer'))throw new DomainException('FEB non payable sur ce chantier.');$this->assertBudget($r);$total=array_sum(array_map(fn($x)=>(float)$x['total_amount'],$r['items']));$paid=array_sum(array_map(fn($x)=>(float)$x['amount'],$r['payments']));$q=$pdo->prepare('SELECT id FROM feb_payments WHERE payment_reference=? FOR UPDATE');$q->execute([$ref]);FebSecurityRules::assertPayment($total,$paid,$amount,(bool)$q->fetchColumn());$q=$pdo->prepare('INSERT INTO feb_payments(request_id,payment_reference,amount,method,paid_at,notes,recorded_by) VALUES(?,?,?,?,?,?,?)');$q->execute([$id,$ref,$amount,$method,(string)($data['paid_at']??date('Y-m-d H:i:s')),trim((string)($data['notes']??'')),$actor]);$to=abs(($paid+$amount)-$total)<0.01?'paye':'partiellement_paye';$q=$pdo->prepare('UPDATE feb_requests SET status=?,version=version+1 WHERE id=? AND version=?');$q->execute([$to,$id,$r['version']]);FebSecurityRules::assertUpdated($q->rowCount(),'Paiement concurrent détecté.');$this->history($id,$actor,'paiement',$r['status'],$to,['amount'=>$amount,'reference'=>$ref]);$pdo->commit();}catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();if($e instanceof \PDOException&&$e->getCode()==='23000')throw new DomainException('Cette référence de paiement existe déjà.',0,$e);throw $e;}}
 private function assertBudget(array $request):void{
  $pdo=$this->repo->pdo();
  if(($request['scope']??'chantier')==='bureau'){
   $allocationId=(int)($request['office_allocation_id']??0);$service=(int)($request['service_id']??0);
   $allocation=$this->repo->officeAllocation($allocationId,$service,true);
   if(!$allocation)throw new DomainException('Le budget bureau n’est plus actif.');
   $requested=array_sum(array_map(static fn(array $line):float=>(float)$line['total_amount'],$request['items']));
   foreach($request['items'] as $item)if((int)($item['office_allocation_id']??0)!==$allocationId || $item['budget_line_id']!==null)throw new DomainException('Poste bureau incohérent.');
   $q=$pdo->prepare("SELECT COALESCE(SUM(i.total_amount),0) FROM feb_request_items i JOIN feb_requests r ON r.id=i.request_id WHERE i.office_allocation_id=? AND r.id<>? AND r.scope='bureau' AND r.status NOT IN ('brouillon','refuse','annule')");
   $q->execute([$allocationId,(int)$request['id']]);
   FebSecurityRules::assertBudget([$allocationId=>(float)$allocation['budget_amount']],[$allocationId=>$requested],[$allocationId=>(float)$q->fetchColumn()],'bureau');
   return;
  }
  $allowed=array_column($this->repo->budgetLines((int)$request['chantier_id'],true),null,'id');
  $requested=[];
  foreach($request['items'] as $item){$line=(int)$item['budget_line_id'];$requested[$line]=($requested[$line]??0)+(float)$item['total_amount'];}
  $limits=[];$committed=[];
  foreach($requested as $line=>$amount){
   if(isset($allowed[$line]))$limits[$line]=(float)$allowed[$line]['total_cost'];
   $q=$pdo->prepare("SELECT COALESCE(SUM(i.total_amount),0) FROM feb_request_items i JOIN feb_requests r ON r.id=i.request_id WHERE i.budget_line_id=? AND r.id<>? AND r.status NOT IN ('brouillon','refuse','annule')");
   $q->execute([$line,(int)$request['id']]);$committed[$line]=(float)$q->fetchColumn();
  }
  FebSecurityRules::assertBudget($limits,$requested,$committed);
 }
 private function parseLines(array $raw,string $scope='chantier'):array{
  $out=[];
  foreach($raw as $x){
   if(!is_array($x))continue;
   $budget=(int)($x['budget_line_id']??0);
   $description=trim((string)($x['description']??''));
   $rawAmount=trim((string)($x['unit_amount']??''));
   if($budget===0&&$description===''&&$rawAmount==='')continue;
   $qty=round((float)($x['quantity']??0),3);
   $unit=trim((string)($x['unit']??'u'));
   $amount=round((float)$rawAmount,2);
   if(($scope==='chantier'&&$budget===0)||$description===''||$qty<=0||$amount<0||$rawAmount===''||!is_numeric($rawAmount)||!is_finite($amount)||!is_finite($qty))
    throw new DomainException('Ligne de besoin invalide.');
   $out[]=['budget_line_id'=>$budget,'quote_line_id'=>(int)($x['quote_line_id']??0),'debourse_id'=>(int)($x['debourse_id']??0),'description'=>$description,'quantity'=>$qty,'unit'=>mb_substr($unit,0,30),'unit_amount'=>$amount,'total_amount'=>round($qty*$amount,2)];
  }
  return $out;
 }
 private function history(int $id,?int $actor,string $event,?string $from,?string $to,array $meta=[]):void{$q=$this->repo->pdo()->prepare('INSERT INTO feb_history(request_id,actor_id,event_type,from_status,to_status,metadata_json) VALUES(?,?,?,?,?,?)');$q->execute([$id,$actor,$event,$from,$to,$meta?json_encode($meta,JSON_UNESCAPED_UNICODE):null]);}
}
