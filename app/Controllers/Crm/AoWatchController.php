<?php
declare(strict_types=1);
namespace App\Controllers\Crm;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Middleware\AuthMiddleware;
use App\Models\Database;
use App\Repositories\Crm\AoWatchRepository;
use App\Repositories\Crm\AoScoringSettingsRepository;
use App\Services\Crm\Ao\AoScoringService;
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
    public function settings():void
    {
        $this->guard();
        $this->view('crm/ao_settings',[
            'aoSettings'=>(new AoScoringSettingsRepository(Database::getConnection()))->get(),
            'aoCsrf'=>$this->csrf(),'aoFlash'=>$this->flash()
        ]+CrmPageContext::forPage('Paramétrage de la pertinence AO','veille-ao-parametres'));
    }
    public function saveSettings():void
    {
        $this->guard();$this->checkCsrf();
        $keywords=trim((string)($_POST['keywords']??''));
        $excluded=trim((string)($_POST['excluded_keywords']??''));
        $min=filter_var($_POST['min_score']??null,FILTER_VALIDATE_INT);
        $scorer=new AoScoringService();
        if(mb_strlen($keywords)>10000||mb_strlen($excluded)>10000||count($scorer->splitKeywords($keywords))>250||count($scorer->splitKeywords($excluded))>250||$min===false||$min<0||$min>100){
            $_SESSION['crm_ao_flash']='Paramètres invalides : score entre 0 et 100, 250 mots-clés maximum par liste.';
            $this->redirect('/crm/veille-ao/parametres');
        }
        $settings=['keywords'=>$keywords,'excluded_keywords'=>$excluded,'min_score'=>$min];
        $pdo=Database::getConnection();
        try{
            $pdo->beginTransaction();
            (new AoScoringSettingsRepository($pdo))->save($settings,(int)Auth::id());
            $count=$this->repo()->rescore($scorer,$settings);
            $pdo->commit();
            $_SESSION['crm_ao_flash']="Paramètres enregistrés : ".$count." appels d'offres recalculés.";
        }catch(\Throwable $e){
            if($pdo->inTransaction())$pdo->rollBack();
            error_log('[CRM AO scoring] '.$e->getMessage());
            $_SESSION['crm_ao_flash']='Échec de la mise à jour des paramètres.';
        }
        $this->redirect('/crm/veille-ao/parametres');
    }
    public function index():void
    {
        $this->guard();$repo=$this->repo();$filters=$this->filters();
        $this->view('crm/ao_list',[
            'aoItems'=>$repo->search($filters),'aoStats'=>$repo->stats(),
            'aoFilters'=>$filters,'aoFlash'=>$this->flash(),
            'aoSettings'=>(new AoScoringSettingsRepository(Database::getConnection()))->get()
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
