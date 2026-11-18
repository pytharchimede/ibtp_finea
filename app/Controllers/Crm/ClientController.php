<?php
declare(strict_types=1);
namespace App\Controllers\Crm;
use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Middleware\AuthMiddleware;
use App\Models\Database;
use App\Repositories\Crm\ClientRepository;
use App\Services\Crm\ClientService;
final class ClientController extends BaseController {
 private function service():ClientService{return new ClientService(new ClientRepository(Database::getConnection()));}
 private function guard(bool $write=false):void{
  AuthMiddleware::check();
  if(!Auth::user()?->isAdmin){http_response_code(403);exit('Accès CRM réservé aux administrateurs pour cette première version.');}
 }
 private function csrf():string{return $_SESSION['crm_client_csrf']??=bin2hex(random_bytes(32));}
 public function index():void{ $this->renderIndex(false); }
 public function create():void{ $this->renderIndex(true); }
 public function edit():void{ $this->renderIndex(false,true); }
 private function renderIndex(bool $new,bool $editPage=false):void{
  $this->guard();$id=$editPage?(filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT)?:0):0;
  $error=$_SESSION['crm_client_error']??null;$success=$_SESSION['crm_client_success']??null;
  unset($_SESSION['crm_client_error'],$_SESSION['crm_client_success']);
  $filters=array_intersect_key($_GET,array_flip(['q','type','status','city','verification_status','identity_country']));
  $this->view('crm/clients',['pageTitle'=>'Clients et prospects','clients'=>$this->service()->filter($filters),'crmFilters'=>$filters,'clientEdit'=>$id?$this->service()->find($id):null,'csrfCrm'=>$this->csrf(),'errorCrm'=>$error,'successCrm'=>$success,'crmNewMode'=>$new,'crmFormMode'=>$new||$editPage,'crmReferences'=>(new \App\Repositories\Crm\CrmReferenceRepository(Database::getConnection()))->all()] + \App\Services\Crm\CrmPageContext::forPage($new?'Créer un client':($editPage?'Modifier un client':'Clients & Prospects'),$new?'nouveau-client':'clients'));
 }
 public function qualify():void{
  $this->guard(true);
  if(!hash_equals($this->csrf(),(string)($_POST['_csrf']??''))){http_response_code(403);exit('Jeton CSRF invalide.');}
  try{$this->service()->qualify((int)($_POST['id']??0),(string)($_POST['type']??''),(int)Auth::id());$_SESSION['crm_client_success']='Qualification commerciale mise à jour.';}
  catch(\DomainException $e){$_SESSION['crm_client_error']=$e->getMessage();}
  $this->redirect('/crm/clients');
 }
 public function export():void{
  $this->guard();
  $filters=array_intersect_key($_GET,array_flip(['q','type','status','city','verification_status','identity_country']));
  if(isset($_GET['id'])){$record=$this->service()->find((int)$_GET['id']);$rows=$record?[$record]:[];}
  else{$rows=$this->service()->filter($filters);}
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="finea-clients-'.date('Ymd-His').'.csv"');
  header('Cache-Control: no-store');
  $out=fopen('php://output','wb');fwrite($out,"\\xEF\\xBB\\xBF");
  $fields=['id','name','type','status','contact_name','email','phone','city','country','sector','legal_form','identity_country','identifier_kind','identifier_value','verification_status','verification_note'];
  fputcsv($out,$fields,';');
  foreach($rows as $row){fputcsv($out,array_map(static function($key)use($row){$v=(string)($row[$key]??'');return preg_match('/^[\\s]*[=+@\\-]/u',$v)?"'".$v:$v;},$fields),';');}
  fclose($out);exit;
 }
 public function save():void{
  $this->guard(true);
  if(!hash_equals($this->csrf(),(string)($_POST['_csrf']??''))){http_response_code(403);exit('Jeton CSRF invalide.');}
  $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:null;
  try{$this->service()->save($_POST,(int)Auth::id(),$id);$_SESSION['crm_client_success']='Fiche client enregistrée.';}
  catch(\DomainException|\PDOException $e){$_SESSION['crm_client_error']=$e instanceof \PDOException?'Enregistrement impossible.':$e->getMessage();}
  $this->redirect('/crm/clients');
 }
}
