<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Helpers\Auth;use App\Helpers\Csrf;use App\Helpers\Session;use App\Middleware\AdminMiddleware;use App\Models\Database;use App\Repositories\Admin\FebAdminRepository;use App\Services\Admin\FebAdminService;use App\View\Pages\Admin\FebAdminPage;
final class AdminFebController extends AdminBaseController {
 private function service():FebAdminService{return new FebAdminService(new FebAdminRepository(Database::getConnection()));}
 public function index():void{$this->render();}
 public function workflowCreate():void{$this->render(0);}
 public function workflowEdit(string $id):void{$this->render((int)$id);}
 public function workflowSave(?string $id=null):void{$this->post();try{$saved=$this->service()->saveWorkflow($id?(int)$id:null,$_POST,(int)Auth::id());Session::flash('success','Workflow FEB enregistré.');$this->redirect('/admin/feb/workflows/'.$saved.'/modifier');}catch(\Throwable $e){Session::flash('error',$this->message($e));$this->back();}}
 public function workflowToggle(string $id):void{$this->post();try{$this->service()->toggleWorkflow((int)$id,(string)($_POST['active']??'0')==='1',(int)Auth::id());Session::flash('success','État du workflow mis à jour.');}catch(\Throwable $e){Session::flash('error',$this->message($e));}$this->redirect('/admin/feb');}
 public function accessSave():void{$this->post();try{$roles=is_array($_POST['roles']??null)?array_keys($_POST['roles']):[];$this->service()->assignAccess((int)($_POST['chantier_id']??0),(int)($_POST['user_id']??0),$roles,(int)Auth::id());Session::flash('success','Affectations FEB remplacées.');}catch(\Throwable $e){Session::flash('error',$this->message($e));}$this->redirect('/admin/feb');}
 public function accessBulkSave():void{$this->post();try{$users=is_array($_POST['user_ids']??null)?$_POST['user_ids']:[];$roles=is_array($_POST['roles']??null)?array_keys($_POST['roles']):[];$count=$this->service()->assignAccessBulk((int)($_POST['chantier_id']??0),$users,$roles,(int)Auth::id());Session::flash('success',$count.' utilisateur(s) affecté(s) au chantier.');}catch(\Throwable $e){Session::flash('error',$this->message($e));}$this->redirect('/admin/feb');}
 public function budgetSave():void{$this->post();try{$this->service()->assignBudget((int)($_POST['chantier_id']??0),(int)($_POST['budget_id']??0),(int)Auth::id());Session::flash('success','Budget d’exécution rattaché.');}catch(\Throwable $e){Session::flash('error',$this->message($e));}$this->redirect('/admin/feb');}
 public function officeBudgetSave():void{
  $this->post();
  try{
   $this->service()->createOfficeAllocation($_POST,(int)Auth::id());
   Session::flash('success','Affectation budgétaire bureau créée.');
  }catch(\Throwable $e){Session::flash('error',$this->message($e));}
  $this->redirect('/admin/feb');
 }
 private function render(?int $workflowId=null):void{AdminMiddleware::check();$service=$this->service();$data=$service->dashboard();$selected=(int)($_GET['chantier']??0);$this->adminView('admin/feb/index','Administration FEB','feb',['page'=>new FebAdminPage($data['workflows'],$data['users'],$data['chantiers'],$data['accesses'],$data['budgets'],$data['history'],$workflowId!==null,$workflowId?$service->workflow($workflowId):null,$service->eligibleBudgets($selected),$selected,$data['officeServices'],$data['officeAllocations'])]);}
 private function post():void{AdminMiddleware::check();if(!Csrf::verify($_POST['_csrf_token']??null)){Session::flash('error','Jeton CSRF invalide.');$this->back();}}
 private function message(\Throwable $e):string{return $e instanceof \DomainException?$e->getMessage():'Opération FEB impossible.';}
}
