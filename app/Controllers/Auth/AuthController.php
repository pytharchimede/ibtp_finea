<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Middleware\GuestMiddleware;
use App\Models\Database;
use App\Repositories\Admin\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\Shared\BrandingService;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService(
            new UserRepository(Database::getConnection())
        );
    }

    public function showLogin(): void
    {
        GuestMiddleware::check();
        $this->view('auth/login', [
            'pageTitle' => 'Connexion',
            'branding' => new BrandingService(),
        ]);
    }

    public function login(): void
    {
        if (!Csrf::verify($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Jeton CSRF invalide.');
            $this->back();
        }

        $result = $this->authService->login($_POST);
        if (!$result['success']) {
            Session::flash('error', $result['message']);
            $this->back();
        }

        Session::set('auth_user_id', $result['user']->id);
        Session::flash('success', 'Connexion réussie.');
        $this->redirect('/selection_portail');
    }

    public function logout(): void
    {
        Session::forget('auth_user_id');
        Session::flash('success', 'Déconnexion effectuée.');
        $this->redirect('/');
    }
}
