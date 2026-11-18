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
use InvalidArgumentException;
final class ProjetController extends BaseController
{
    private function service(): ProjetService
    {
        return new ProjetService(new ProjetRepository(Database::getConnection()));
    }
    public function index(): void
    {
        AuthMiddleware::check();
        if (!Auth::can(PermissionEntityRegistry::BTP_PROJECTS)) { http_response_code(403); exit('Accès interdit'); }
        $error=$_SESSION['btp_projet_error'] ?? null;
        unset($_SESSION['btp_projet_error']);
        $this->view('btp/projets',['pageTitle'=>'Projets BTP','projets'=>$this->service()->list(),'clientsBtp'=>$this->service()->clients(),'csrfBtp'=>$this->csrf(),'errorBtp'=>$error]);
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
