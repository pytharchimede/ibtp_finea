<?php
declare(strict_types=1);
namespace App\Controllers\Crm;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Middleware\AuthMiddleware;
use App\Models\Database;
use App\Repositories\Crm\AoWatchRepository;
use App\Services\Crm\CrmPageContext;

final class AoWatchController extends BaseController
{
    private function guard():void
    {
        AuthMiddleware::check();
        if(!Auth::user()?->isAdmin){http_response_code(403);exit('Accès veille AO réservé aux administrateurs.');}
    }
    private function repo():AoWatchRepository{return new AoWatchRepository(Database::getConnection());}
    private function csrf():string{return $_SESSION['crm_ao_csrf']??=bin2hex(random_bytes(32));}
    private function checkCsrf():void
    {
        if(!hash_equals($this->csrf(),(string)($_POST['_csrf']??''))){
            http_response_code(403);exit('Jeton CSRF invalide.');
        }
    }
    private function filters():array
    {
        $keys=['q','deadline_scope','status','source','min_score','deadline_soon'];
        $filters=[];
        foreach($keys as $key){
            $v=$_GET[$key]??'';
            $filters[$key]=is_scalar($v)?mb_substr(trim((string)$v),0,150):'';
        }
        $filters['deadline_scope']=in_array($filters['deadline_scope'],['active','expired','undated','all'],true)?$filters['deadline_scope']:'active';
        return $filters;
    }
    private function flash():?string
    {
        $message=$_SESSION['crm_ao_flash']??null;
        unset($_SESSION['crm_ao_flash']);
        return $message;
    }
    public function index():void
    {
        $this->guard();$repo=$this->repo();$filters=$this->filters();
        $this->view('crm/ao_list',[
            'aoItems'=>$repo->search($filters),'aoStats'=>$repo->stats(),
            'aoFilters'=>$filters,'aoFlash'=>$this->flash()
        ]+CrmPageContext::forPage("Veille appels d'offres",'veille-ao'));
    }
    public function detail():void
    {
        $this->guard();$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT)?:0;
        $item=$this->repo()->find($id);
        if(!$item){http_response_code(404);exit("Appel d'offres introuvable.");}
        $clients=Database::getConnection()->query('SELECT id,name FROM crm_clients ORDER BY name LIMIT 1000')->fetchAll(\PDO::FETCH_ASSOC);
        $this->view('crm/ao_detail',[
            'aoItem'=>$item,'aoClients'=>$clients,'aoCsrf'=>$this->csrf(),
            'aoFlash'=>$this->flash()
        ]+CrmPageContext::forPage('Détail appel d’offres','veille-ao'));
    }
    public function logs():void
    {
        $this->guard();
        $this->view('crm/ao_logs',['aoLogs'=>$this->repo()->logs()]
            +CrmPageContext::forPage('Historique des collectes AO','veille-ao-suivi'));
    }
    public function updateStatus():void
    {
        $this->guard();$this->checkCsrf();
        $id=(int)($_POST['id']??0);
        try{
            $this->repo()->setStatus($id,(string)($_POST['status']??''));
            $_SESSION['crm_ao_flash']='Statut actualisé.';
        }catch(\DomainException $e){$_SESSION['crm_ao_flash']=$e->getMessage();}
        $this->redirect('/crm/veille-ao/detail?id='.$id);
    }
    public function convert():void
    {
        $this->guard();$this->checkCsrf();
        $id=(int)($_POST['id']??0);$client=(int)($_POST['client_id']??0);
        try{
            $opp=$this->repo()->convert($id,$client);
            $_SESSION['crm_opp_success']="L'appel d'offres a été rattaché à l'opportunité #".$opp.'.';
            $this->redirect('/crm/opportunites');
        }catch(\DomainException $e){
            $_SESSION['crm_ao_flash']=$e->getMessage();
            $this->redirect('/crm/veille-ao/detail?id='.$id);
        }
    }
    public function export():void
    {
        $this->guard();
        $rows=$this->repo()->search($this->filters(),1000);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="veille-ao-finea-'.date('Ymd-His').'.csv"');
        header('Cache-Control: no-store');
        $out=fopen('php://output','wb');fwrite($out,"\xEF\xBB\xBF");
        $fields=['source','numero_ao','reference','titre','autorite_contractante','type_marche','secteur','date_publication','date_limite','lieu_execution','score_pertinence','statut_interne','resume','url_source','crm_opportunity_id'];
        fputcsv($out,$fields,';');
        foreach($rows as $row){
            $values=[];
            foreach($fields as $field){
                $v=(string)($row[$field]??'');
                $values[]=preg_match('/^\s*[=+@-]/u',$v)?"'".$v:$v;
            }
            fputcsv($out,$values,';');
        }
        fclose($out);exit;
    }
}
