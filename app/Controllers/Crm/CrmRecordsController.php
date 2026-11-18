<?php
declare(strict_types=1);
namespace App\Controllers\Crm;
use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Middleware\AuthMiddleware;
use App\Models\Database;
use PDO;
final class CrmRecordsController extends BaseController {
 private function guard():void{AuthMiddleware::check();if(!Auth::user()?->isAdmin){http_response_code(403);exit('Accès CRM réservé aux administrateurs.');}}
 private function show(string $kind):void{
  $this->guard();$pdo=Database::getConnection();
  if($kind==='opportunities'){
   $rows=$pdo->query('SELECT o.id,o.title,o.stage,o.estimated_amount,o.currency,o.expected_close_date,c.name AS client_name FROM crm_opportunities o JOIN crm_clients c ON c.id=o.client_id ORDER BY o.id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
  }else{
   $rows=$pdo->query('SELECT i.id,i.channel,i.subject,i.interaction_at,i.next_action_date,c.name AS client_name FROM crm_interactions i JOIN crm_clients c ON c.id=i.client_id ORDER BY i.id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
  }
  $oppSuccess=$_SESSION['crm_opp_success']??null;
  unset($_SESSION['crm_opp_success']);
  $this->view('crm/records',['pageTitle'=>$kind==='opportunities'?'Opportunités CRM':'Interactions CRM','crmKind'=>$kind,'crmRows'=>$rows,'crmOppSuccess'=>$oppSuccess] + \App\Services\Crm\CrmPageContext::forPage($kind==='opportunities'?'Opportunités CRM':'Interactions CRM',$kind==='opportunities'?'opportunites':'interactions'));
 }
 private function csrf():string{return $_SESSION['crm_opp_csrf']??=bin2hex(random_bytes(32));}
 public function newOpportunity():void{
  $this->guard();$pdo=Database::getConnection();
  $clients=$pdo->query('SELECT id,name FROM crm_clients ORDER BY name LIMIT 1000')->fetchAll(PDO::FETCH_ASSOC);
  $error=$_SESSION['crm_opp_error']??null;unset($_SESSION['crm_opp_error']);
  $this->view('crm/opportunity_form',['pageTitle'=>'Nouvelle opportunité','clients'=>$clients,'selectedClient'=>(int)($_GET['client_id']??0),'csrfOpp'=>$this->csrf(),'errorOpp'=>$error] + \App\Services\Crm\CrmPageContext::forPage('Nouvelle opportunité','opportunites'));
 }
 public function saveOpportunity():void{
  $this->guard();
  if(!hash_equals($this->csrf(),(string)($_POST['_csrf']??''))){http_response_code(403);exit('Jeton CSRF invalide.');}
  $client=(int)($_POST['client_id']??0);$title=trim((string)($_POST['title']??''));$description=trim((string)($_POST['description']??''));
  $stage=(string)($_POST['stage']??'qualification');$deadline=(string)($_POST['deadline']??'');$amount=(string)($_POST['estimated_amount']??'');
  $stages=['qualification','proposition','negociation','gagnee','perdue'];
  try{
   if($client<=0||mb_strlen($title)<3||mb_strlen($title)>180||mb_strlen($description)>10000||!in_array($stage,$stages,true))throw new \DomainException('Informations de l’opportunité invalides.');
   if($deadline!==''&&(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$deadline)||!checkdate((int)substr($deadline,5,2),(int)substr($deadline,8,2),(int)substr($deadline,0,4))))throw new \DomainException('Date limite invalide.');
   if($amount!==''&&(!is_numeric($amount)||(float)$amount<0||(float)$amount>999999999999))throw new \DomainException('Montant estimé invalide.');
   $pdo=Database::getConnection();
   $q=$pdo->prepare('SELECT 1 FROM crm_clients WHERE id=?');$q->execute([$client]);if(!$q->fetchColumn())throw new \DomainException('Client introuvable.');
   $files=$_FILES['attachments']??null;$prepared=[];
   if(is_array($files)&&isset($files['name'])&&is_array($files['name'])){
    if(count($files['name'])>5)throw new \DomainException('Maximum 5 pièces jointes.');
    $allowed=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx'];
    $finfo=new \finfo(FILEINFO_MIME_TYPE);
    foreach($files['name'] as $i=>$original){
     $err=(int)($files['error'][$i]??UPLOAD_ERR_NO_FILE);if($err===UPLOAD_ERR_NO_FILE)continue;
     if($err!==UPLOAD_ERR_OK)throw new \DomainException('Échec du transfert de fichier.');
     $tmp=(string)($files['tmp_name'][$i]??'');$size=(int)($files['size'][$i]??0);
     if(!is_uploaded_file($tmp)||$size<=0||$size>10485760)throw new \DomainException('Chaque fichier doit faire au maximum 10 Mo.');
     $mime=$finfo->file($tmp);if(!isset($allowed[$mime]))throw new \DomainException('Type de fichier non autorisé.');
     $prepared[]=['tmp'=>$tmp,'name'=>mb_substr(basename((string)$original),0,255),'mime'=>$mime,'size'=>$size,'ext'=>$allowed[$mime]];
    }
   }
   $pdo->beginTransaction();
   $q=$pdo->prepare('INSERT INTO crm_opportunities(client_id,title,stage,estimated_amount,currency,expected_close_date,probability,description,deadline) VALUES(?,?,?,?,?,?,?,?,?)');
   $q->execute([$client,$title,$stage,$amount!==''?$amount:null,'XOF',$deadline!==''?$deadline:null,10,$description!==''?$description:null,$deadline!==''?$deadline:null]);
   $id=(int)$pdo->lastInsertId();
   $dir=dirname(__DIR__,3).'/storage/crm/opportunities/'.$id;
   if($prepared&&!is_dir($dir)&&!mkdir($dir,0750,true)&&!is_dir($dir))throw new \RuntimeException('Dossier de stockage indisponible.');
   $saved=[];
   foreach($prepared as $file){
    $stored=bin2hex(random_bytes(20)).'.'.$file['ext'];$path=$dir.'/'.$stored;
    if(!move_uploaded_file($file['tmp'],$path))throw new \RuntimeException('Enregistrement de pièce jointe impossible.');
    $saved[]=$path;
    $q=$pdo->prepare('INSERT INTO crm_opportunity_attachments(opportunity_id,original_name,storage_name,mime_type,file_size) VALUES(?,?,?,?,?)');
    $q->execute([$id,$file['name'],$stored,$file['mime'],$file['size']]);
   }
   $pdo->commit();$_SESSION['crm_opp_success']='Opportunité enregistrée.';$this->redirect('/crm/opportunites');
  }catch(\Throwable $e){
   if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();
   foreach($saved??[] as $path)@unlink($path);
   $_SESSION['crm_opp_error']=$e instanceof \DomainException?$e->getMessage():'Enregistrement impossible. Vérifiez les migrations et le stockage.';
   $this->redirect('/crm/opportunites/nouvelle?client_id='.$client);
  }
 }
 public function downloadAttachment():void{
  $this->guard();$id=(int)($_GET['id']??0);
  $pdo=Database::getConnection();$q=$pdo->prepare('SELECT a.* FROM crm_opportunity_attachments a WHERE a.id=?');$q->execute([$id]);$file=$q->fetch(PDO::FETCH_ASSOC);
  if(!$file){http_response_code(404);exit('Pièce jointe introuvable.');}
  $path=dirname(__DIR__,3).'/storage/crm/opportunities/'.(int)$file['opportunity_id'].'/'.basename((string)$file['storage_name']);
  if(!is_file($path)){http_response_code(404);exit('Fichier introuvable.');}
  header('Content-Type: application/octet-stream');header('X-Content-Type-Options: nosniff');
  header('Content-Disposition: attachment; filename="piece-jointe-'.(int)$file['id'].'.bin"');
  header('Content-Length: '.filesize($path));readfile($path);exit;
 }
 public function opportunities():void{$this->show('opportunities');}
 public function interactions():void{$this->show('interactions');}
}
