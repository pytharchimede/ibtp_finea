<?php
declare(strict_types=1);
namespace App\Controllers\Procurement;
use App\Controllers\BaseController;
use App\Helpers\Auth;use App\Helpers\Csrf;use App\Helpers\Session;use App\Middleware\AuthMiddleware;use App\Models\Database;use App\Services\Procurement\SupplierService;
final class SupplierController extends BaseController {
 private function service():SupplierService{return new SupplierService(Database::getConnection());}
 private function guard(bool $admin=false):void{AuthMiddleware::check();if($admin&&!Auth::user()?->isAdmin){http_response_code(403);exit('Administration réservée');}}
 private function csrf():void{if(!Csrf::verify($_POST['_csrf_token']??null)){http_response_code(403);exit('CSRF invalide');}}
 private function run(callable $action,string $success):void{try{$action();Session::flash('success',$success);}catch(\Throwable $e){Session::flash('error',$e instanceof \DomainException?$e->getMessage():'Opération fournisseur impossible.');}$this->redirect('/fournisseurs');}
 public function index():void{$this->guard();$s=$this->service();$this->view('procurement/suppliers',['suppliers'=>$s->list(),'workflows'=>$s->workflows(),'users'=>$s->users(),'service'=>$s,'csrf'=>Csrf::token(),'isAdmin'=>Auth::user()?->isAdmin??false]);}
 public function create():void{$this->guard();$this->csrf();$this->run(fn()=> $this->service()->create($_POST,(int)Auth::id()),'Fournisseur créé en brouillon.');}
 public function submit(string $id):void{$this->guard();$this->csrf();$this->run(fn()=> $this->service()->submit((int)$id,(int)Auth::id()),'Dossier soumis au circuit.');}
 public function decide(string $id):void{$this->guard();$this->csrf();$this->run(fn()=> $this->service()->decide((int)$id,(string)($_POST['decision']??''),(string)($_POST['comment']??''),(int)Auth::id()),'Décision enregistrée.');}
 public function workflow():void{$this->guard(true);$this->csrf();$this->run(fn()=> $this->service()->createWorkflow($_POST),'Circuit créé. Activez-le avant utilisation.');}
 public function activate(string $id):void{$this->guard(true);$this->csrf();$this->run(fn()=> $this->service()->activateWorkflow((int)$id),'Circuit fournisseur activé.');}
}
