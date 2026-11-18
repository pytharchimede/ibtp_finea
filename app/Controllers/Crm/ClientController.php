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
  $this->view('crm/clients',['pageTitle'=>'Clients et prospects','clients'=>$this->service()->all((string)($_GET['q']??'')),'clientEdit'=>$id?$this->service()->find($id):null,'csrfCrm'=>$this->csrf(),'errorCrm'=>$error,'successCrm'=>$success,'crmNewMode'=>$new,'crmFormMode'=>$new||$editPage,'crmReferences'=>(new \App\Repositories\Crm\CrmReferenceRepository(Database::getConnection()))->all()] + \App\Services\Crm\CrmPageContext::forPage($new?'Créer un client':($editPage?'Modifier un client':'Clients & Prospects'),$new?'nouveau-client':'clients'));
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
