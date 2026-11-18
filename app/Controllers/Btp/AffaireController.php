<?php
declare(strict_types=1);
namespace App\Controllers\Btp;
use App\Controllers\BaseController;
use App\Middleware\AuthMiddleware;
use App\Models\Database;
use App\Repositories\Btp\AffaireRepository;
use App\Services\Btp\AffaireService;
use InvalidArgumentException;
final class AffaireController extends BaseController
{
    private function service(): AffaireService
    {
        return new AffaireService(new AffaireRepository(Database::getConnection()));
    }
    public function index(): void
    {
        AuthMiddleware::check();
        $this->view('btp/affaires', [
            'pageTitle' => 'Affaires BTP',
            'affaires' => $this->service()->list(),
            'csrfBtp' => $this->csrf(),
            'errorBtp' => $_SESSION['btp_error'] ?? null,
        ]);
        unset($_SESSION['btp_error']);
    }
    public function store(): void
    {
        AuthMiddleware::check();
        if (!hash_equals($this->csrf(), (string) ($_POST['_csrf'] ?? ''))) {
            http_response_code(403);
            exit('Jeton de sécurité invalide.');
        }
        try {
            $authorId = isset($_SESSION['auth_user_id']) ? (int) $_SESSION['auth_user_id'] : null;
            $this->service()->create($_POST, $authorId);
        } catch (InvalidArgumentException|\PDOException $e) {
            $_SESSION['btp_error'] = $e instanceof \PDOException ? 'Référence déjà utilisée ou erreur de base.' : $e->getMessage();
        }
        $this->redirect('/btp/affaires');
    }
    private function csrf(): string
    {
        if (!isset($_SESSION['btp_csrf'])) {
            $_SESSION['btp_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['btp_csrf'];
    }
}
