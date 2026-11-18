<?php
declare(strict_types=1);
namespace App\Controllers\Btp;
use App\Controllers\BaseController;
use App\Middleware\AuthMiddleware;
use App\Helpers\Auth;
use App\Security\PermissionEntityRegistry;
use App\Models\Database;
use App\Repositories\Btp\ProjetRepository;
use App\Services\Btp\ProjetService;
use App\Services\Shared\ModuleDashboardService;
use InvalidArgumentException;
final class ProjetController extends BaseController
{
    private function service(): ProjetService
    {
        return new ProjetService(new ProjetRepository(Database::getConnection()));
    }
    private function context(string $title,string $active): array
    {
        $module=(new ModuleDashboardService())->dashboard('btp');
        return ['pageTitle'=>$title,'moduleName'=>$module['label'],'moduleCode'=>$module['code'],
            'moduleTheme'=>$module,'activeModule'=>$active,'moduleNavigation'=>$module['navigation'],
            'additionalStyles'=>['https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css','css/finea-ui.css','css/components/btp-projects.css','css/components/btp-workspace.css']];
    }
    public function chantierIndex(): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS)){http_response_code(403);exit('Accès interdit');}
        $s=new \App\Services\Btp\ChantierStartService(Database::getConnection());
        $this->view('btp/chantiers_liste',$this->context('Chantiers','chantiers')+['chantiersBtp'=>$s->list(),'projetsBtp'=>$s->projects()]);
    }
    public function chantierCreate(): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        $s=new \App\Services\Btp\ChantierStartService(Database::getConnection());
        $this->view('btp/chantier_nouveau',$this->context('Préparer un chantier','chantier-nouveau')+['projetsBtp'=>$s->projects()]);
    }
    public function chantierWizard(string $id): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        $s=new \App\Services\Btp\ChantierStartService(Database::getConnection());
        $d=$s->dossier((int)$id);if(!$d){http_response_code(404);exit('Projet introuvable');}
        $error=$_SESSION['btp_chantier_error']??null;unset($_SESSION['btp_chantier_error']);
        $this->view('btp/chantier_wizard',$this->context('Préparation du chantier','chantier-nouveau')+['dossierBtp'=>$d,'csrfBtp'=>$this->csrf(),'errorBtp'=>$error]);
    }
    public function chantierStart(string $id): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        if(!hash_equals($this->csrf(),(string)($_POST['_csrf']??''))){http_response_code(403);exit('Jeton invalide');}
        try{(new \App\Services\Btp\ChantierStartService(Database::getConnection()))->start((int)$id,$_POST,isset($_SESSION['auth_user_id'])?(int)$_SESSION['auth_user_id']:null);$this->redirect('/btp/chantiers');}
        catch(\Throwable $e){error_log('[BTP chantier] '.$e->getMessage());$_SESSION['btp_chantier_error']=$e instanceof \DomainException?$e->getMessage():'Création impossible.';$this->redirect('/btp/chantiers/projet/'.(int)$id);}
    }
    public function planningIndex(): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS)){http_response_code(403);exit('Accès interdit');}
        $service=new \App\Services\Btp\PlanningService(Database::getConnection());
        $this->view('btp/plannings_liste',$this->context('Plannings prévisionnels','plannings')+['planningsBtp'=>$service->all(),'projetsBtp'=>$this->service()->list(500)]);
    }
    public function planningEditor(string $id): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        $service=new \App\Services\Btp\OfferDraftService(Database::getConnection());
        $project=$service->project((int)$id);
        if(!$project){http_response_code(404);exit('Projet introuvable');}
        $error=$_SESSION['btp_planning_error']??null;unset($_SESSION['btp_planning_error']);
        $this->view('btp/planning_editeur',$this->context('Nouveau planning prévisionnel','plannings')+['projectBtp'=>$project,'projectPlanningsBtp'=>(new \App\Services\Btp\PlanningService(Database::getConnection()))->forProject((int)$id),'csrfBtp'=>$this->csrf(),'errorBtp'=>$error]);
    }
    public function planningSave(string $id): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        if(!hash_equals($this->csrf(),(string)($_POST['_csrf']??''))){http_response_code(403);exit('Jeton invalide');}
        try{
            (new \App\Services\Btp\PlanningService(Database::getConnection()))->save((int)$id,$_POST,isset($_SESSION['auth_user_id'])?(int)$_SESSION['auth_user_id']:null);
            $this->redirect('/btp/plannings');
        }catch(\Throwable $e){
            error_log('[BTP planning] '.$e->getMessage());
            $_SESSION['btp_planning_error']=$e instanceof InvalidArgumentException?$e->getMessage():'Enregistrement du planning impossible.';
            $this->redirect('/btp/plannings/projet/'.(int)$id);
        }
    }
    public function quoteIndex(): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS)){http_response_code(403);exit('Accès interdit');}
        $pdo=Database::getConnection();
        $q=$pdo->query("SELECT q.id,q.revision,q.title AS label,q.total_ht AS amount_ht,q.status,q.created_at,p.id AS projet_id,p.reference,p.intitule,p.client_nom FROM btp_offer_drafts q JOIN btp_projets p ON p.id=q.projet_id ORDER BY q.created_at DESC,q.id DESC LIMIT 500");
        $this->view('btp/devis_liste',$this->context('Liste des devis','devis-liste')+['quotesBtp'=>$q->fetchAll(\PDO::FETCH_ASSOC)]);
    }
    public function quoteCreate(): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        $this->view('btp/devis_nouveau',$this->context('Nouveau devis','devis-nouveau')+['projetsBtp'=>$this->service()->list(100)]);
    }
    public function quoteEditor(string $id): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        $service=new \App\Services\Btp\OfferDraftService(Database::getConnection());
        $project=$service->project((int)$id);
        if(!$project){http_response_code(404);exit('Projet introuvable');}
        $error=$_SESSION['btp_offer_error']??null;unset($_SESSION['btp_offer_error']);
        $this->view('btp/devis_editeur',$this->context('Éditeur de devis','devis-nouveau')+['projectBtp'=>$project,'unitsBtp'=>$service->units(),'csrfBtp'=>$this->csrf(),'errorBtp'=>$error]);
    }
    public function quoteSave(string $id): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        if(!hash_equals($this->csrf(),(string)($_POST['_csrf']??''))){http_response_code(403);exit('Jeton invalide');}
        try{
            (new \App\Services\Btp\OfferDraftService(Database::getConnection()))->save((int)$id,$_POST,isset($_SESSION['auth_user_id'])?(int)$_SESSION['auth_user_id']:null);
            $this->redirect('/btp/devis');
        }catch(\Throwable $e){
            error_log('[BTP offer] '.$e->getMessage());
            $_SESSION['btp_offer_error']=$e instanceof InvalidArgumentException?$e->getMessage():'Enregistrement impossible.';
            $this->redirect('/btp/devis/projet/'.(int)$id);
        }
    }
    public function dashboard(): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS)){http_response_code(403);exit('Accès interdit');}
        $this->view('btp/dashboard',$this->context('Pilotage des projets','dashboard')+[
            'summaryBtp'=>$this->service()->summary(),'projets'=>$this->service()->list(10)]);
    }
    public function create(): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        $this->view('btp/projet_create',$this->context('Nouveau projet CRM','nouveau-projet')+[
            'opportunitiesBtp'=>$this->service()->opportunities(),'csrfBtp'=>$this->csrf(),
            'errorBtp'=>$_SESSION['btp_projet_error']??null]);
        unset($_SESSION['btp_projet_error']);
    }
    public function index(): void
    {
        AuthMiddleware::check();
        if (!Auth::can(PermissionEntityRegistry::BTP_PROJECTS)) { http_response_code(403); exit('Accès interdit'); }
        $error=$_SESSION['btp_projet_error'] ?? null;
        unset($_SESSION['btp_projet_error']);
        $this->view('btp/projets',$this->context('Projets BTP','projets')+['projets'=>$this->service()->list(),'errorBtp'=>$error]);
    }
    public function show(string $id): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS)){http_response_code(403);exit('Accès interdit');}
        $service=new \App\Services\Btp\DossierService(Database::getConnection());
        $project=$service->project((int)$id);
        if(!$project){http_response_code(404);exit('Projet introuvable');}
        $this->view('btp/dossier',$this->context('Dossier projet','projets')+[
            'projectBtp'=>$project,'eventsBtp'=>$service->events((int)$id),
            'quotesBtp'=>$service->quotes((int)$id),'documentsBtp'=>$service->documents((int)$id),'csrfBtp'=>$this->csrf(),
            'errorBtp'=>$_SESSION['btp_projet_error']??null]);
        unset($_SESSION['btp_projet_error']);
    }
    public function uploadDocument(string $id): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        if(!hash_equals($this->csrf(),(string)($_POST['_csrf']??''))){http_response_code(403);exit('Jeton invalide');}
        try {
            (new \App\Services\Btp\DossierService(Database::getConnection()))->uploadDocuments((int)$id,$_FILES['documents']??[],isset($_SESSION['auth_user_id'])?(int)$_SESSION['auth_user_id']:null);
        } catch (\Throwable $e) {
            error_log('[BTP upload] '.$e->getMessage());
            $_SESSION['btp_projet_error']=$e instanceof InvalidArgumentException?$e->getMessage():'Impossible de conserver le document.';
        }
        $this->redirect('/btp/projets/'.(int)$id);
    }
    public function downloadDocument(string $id,string $documentId): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS)){http_response_code(403);exit('Accès interdit');}
        $doc=(new \App\Services\Btp\DossierService(Database::getConnection()))->document((int)$id,(int)$documentId);
        if(!$doc){http_response_code(404);exit('Document introuvable');}
        $path=BASE_PATH.'/storage/private/btp/'.$doc['storage_name'];
        if(!is_file($path)){http_response_code(404);exit('Fichier indisponible');}
        header('Content-Type: application/octet-stream');
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: attachment; filename="document-'.(int)$documentId.'"');
        header('Content-Length: '.filesize($path));
        readfile($path);
        exit;
    }
    public function addEvent(string $id): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        if(!hash_equals($this->csrf(),(string)($_POST['_csrf']??''))){http_response_code(403);exit('Jeton invalide');}
        try {
            (new \App\Services\Btp\DossierService(Database::getConnection()))->addEvent((int)$id,$_POST,isset($_SESSION['auth_user_id'])?(int)$_SESSION['auth_user_id']:null);
        } catch (\Throwable $e) {$_SESSION['btp_projet_error']=$e instanceof \PDOException?'Impossible d’enregistrer cet événement.':$e->getMessage();}
        $this->redirect('/btp/projets/'.(int)$id);
    }
    public function addQuote(string $id): void
    {
        AuthMiddleware::check();
        if(!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')){http_response_code(403);exit('Accès interdit');}
        if(!hash_equals($this->csrf(),(string)($_POST['_csrf']??''))){http_response_code(403);exit('Jeton invalide');}
        try {
            (new \App\Services\Btp\DossierService(Database::getConnection()))->addQuote((int)$id,$_POST,isset($_SESSION['auth_user_id'])?(int)$_SESSION['auth_user_id']:null);
        } catch (\Throwable $e) {$_SESSION['btp_projet_error']=$e instanceof \PDOException?'Impossible d’enregistrer le devis.':$e->getMessage();}
        $this->redirect('/btp/projets/'.(int)$id);
    }
    public function store(): void
    {
        AuthMiddleware::check();
        if (!Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')) { http_response_code(403); exit('Accès interdit'); }
        if (!hash_equals($this->csrf(),(string)($_POST['_csrf'] ?? ''))) {
            http_response_code(403); exit('Jeton invalide');
        }
        try {
            $this->service()->create($_POST,isset($_SESSION['auth_user_id'])?(int)$_SESSION['auth_user_id']:null);
        } catch (InvalidArgumentException|\PDOException $e) {
            $_SESSION['btp_projet_error']=$e instanceof \PDOException?'Création impossible : référence existante ou base indisponible.':$e->getMessage();
        }
        $this->redirect('/btp/projets');
    }
    private function csrf(): string
    {
        return $_SESSION['btp_projet_csrf'] ??= bin2hex(random_bytes(32));
    }
}
