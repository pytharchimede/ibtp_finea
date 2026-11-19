<?php
declare(strict_types=1);
namespace App\Controllers\Procurement;
use App\Controllers\BaseController;
use App\Helpers\Auth;use App\Helpers\Csrf;use App\Helpers\Session;use App\Middleware\AuthMiddleware;use App\Models\Database;use App\Services\Procurement\SupplierService;use App\Services\Shared\ModuleDashboardService;
final class SupplierController extends BaseController {
 private function service():SupplierService{return new SupplierService(Database::getConnection());}
 private function guard(bool $admin=false):void{AuthMiddleware::check();if($admin&&!(Auth::user()?->isAdmin??false)){http_response_code(403);exit('Administration réservée');}}
 private function csrf():void{if(!Csrf::verify($_POST['_csrf_token']??null)){http_response_code(403);exit('CSRF invalide');}}
 private function run(callable $action,string $success):void{try{$action();Session::flash('success',$success);}catch(\Throwable $e){Session::flash('error',$e instanceof \DomainException?$e->getMessage():'Opération fournisseur impossible.');}$this->redirect('/fournisseurs');}
 private function screen(string $section):void{
  $this->guard();$s=$this->service();$m=(new ModuleDashboardService())->dashboard('fournisseurs');
  $this->view('procurement/suppliers',[
   'pageTitle'=>match($section){'dashboard'=>'Tableau de bord fournisseurs','nouveau'=>'Nouveau fournisseur','circuits'=>'Circuits d’agrément',default=>'Répertoire fournisseurs'},
   'moduleName'=>$m['label'],'moduleCode'=>$m['code'],'moduleTheme'=>$m,'moduleNavigation'=>$m['navigation'],
   'activeModule'=>$section,'additionalStyles'=>['css/finea-ui.css','css/components/btp-workspace.css'],
   'section'=>$section,'suppliers'=>$s->list(),'workflows'=>$s->workflows(),'users'=>$s->users(),
   'service'=>$s,'csrf'=>Csrf::token(),'isAdmin'=>Auth::user()?->isAdmin??false
  ]);
 }
 public function index():void{$this->screen('repertoire');}
 public function dashboard():void{$this->screen('dashboard');}
 public function newSupplier():void{$this->screen('nouveau');}
 public function circuits():void{$this->guard(true);$this->screen('circuits');}
 public function create():void{$this->guard();$this->csrf();$this->run(fn()=> $this->service()->create($_POST,(int)Auth::id()),'Fournisseur créé en brouillon.');}
 public function submit(string $id):void{$this->guard();$this->csrf();$this->run(fn()=> $this->service()->submit((int)$id,(int)Auth::id()),'Dossier soumis au circuit.');}
 public function decide(string $id):void{$this->guard();$this->csrf();$this->run(fn()=> $this->service()->decide((int)$id,(string)($_POST['decision']??''),(string)($_POST['comment']??''),(int)Auth::id()),'Décision enregistrée.');}
 public function workflow():void{$this->guard(true);$this->csrf();$this->run(fn()=> $this->service()->createWorkflow($_POST),'Circuit créé. Activez-le avant utilisation.');}
 public function activate(string $id):void{$this->guard(true);$this->csrf();$this->run(fn()=> $this->service()->activateWorkflow((int)$id),'Circuit fournisseur activé.');}
}
