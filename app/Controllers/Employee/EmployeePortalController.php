<?php

declare(strict_types=1);

namespace App\Controllers\Employee;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Database;
use App\Repositories\Employee\EmployeeDashboardRepository;
use App\Repositories\Employee\EmployeePortalRepository;
use App\Services\Employee\EmployeeDashboardService;
use App\Services\Employee\EmployeePortalService;
use App\View\Pages\Employee\DashboardPage;
use App\View\Pages\Employee\RequestFormPage;
use App\View\Pages\Employee\RequestShowPage;
use RuntimeException;

final class EmployeePortalController extends EmployeeBaseController
{
    private EmployeePortalService $service;
    private EmployeeDashboardService $dashboardService;

    public function __construct()
    {
        $portalRepository = new EmployeePortalRepository(Database::getConnection());
        $this->service = new EmployeePortalService($portalRepository);
        $this->dashboardService = new EmployeeDashboardService(
            new EmployeeDashboardRepository($portalRepository)
        );
    }

    public function index(): void
    {
        AuthMiddleware::check();
        try {
            $this->employeeView('employee/dashboard', 'Mon espace employé', 'dashboard', [
                'page' => DashboardPage::fromArray(
                    $this->dashboardService->dashboard(Auth::user()),
                    Csrf::token()
                ),
            ]);
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/selection_portail');
        }
    }

    public function listRequests(): void
    {
        AuthMiddleware::check();
        try {
            $dashboard = $this->dashboardService->dashboard(Auth::user());
            $febRepository = new \App\Repositories\Feb\FebRepository(Database::getConnection());
            $this->employeeView('employee/request-list', 'Mes demandes', 'requests', [
                'requests' => $dashboard['requests'] ?? [],
                'febRequests' => $febRepository->listOwn((int) Auth::id()),
            ]);
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/espace-employe');
        }
    }

    public function createRequest(): void
    {
        AuthMiddleware::check();
        $febData = ['scope' => 'chantier', 'chantiers' => [], 'workflows' => [], 'selectedChantier' => 0, 'lines' => [], 'officeServices' => [], 'officeAllocations' => [], 'userId' => 0];
        if (($_GET['type'] ?? '') === 'feb') {
            $repository = new \App\Repositories\Feb\FebRepository(Database::getConnection());
            $userId = (int) Auth::id();
            $chantier = (int) ($_GET['chantier'] ?? 0);
            $scope = ($_GET['scope'] ?? 'chantier') === 'bureau' ? 'bureau' : 'chantier';
            $allowed = $scope === 'chantier' && $chantier > 0 && $repository->hasChantierAccess($userId, $chantier, 'demander');
            $chantiers = $scope === 'chantier' ? $repository->chantiersFor($userId, 'demander') : [];
            if ($allowed && !in_array($chantier, array_map(static fn(array $row): int => (int) $row['id'], $chantiers), true)) $allowed = false;
            $febData = [
                'scope' => $scope,
                'chantiers' => $scope === 'chantier' ? $chantiers : [],
                'workflows' => $repository->workflows(),
                'selectedChantier' => $scope === 'chantier' && $allowed ? $chantier : 0,
                'lines' => $scope === 'chantier' && $allowed ? $repository->budgetLines($chantier) : [],
                'officeServices' => [],
                'officeAllocations' => [],
                'userId' => $userId,
                'setupError' => '',
            ];
            if ($scope === 'bureau') {
                try {
                    $febData['officeServices'] = $repository->officeServices();
                    $febData['officeAllocations'] = $repository->officeAllocations();
                } catch (\PDOException $e) {
                    error_log('[FEB bureau] Configuration SQL indisponible : ' . $e->getMessage());
                    $febData['setupError'] = 'Le paramétrage budgétaire FEB Bureau est indisponible. Vérifiez la migration 20261119_002_feb_office_budget.sql et les services RH.';
                }
            }
        }
        $this->employeeView('employee/request-form', 'Nouvelle demande RH', 'new_request', [
            'febData' => $febData,
            'additionalStyles' => ['css/finea-ui.css', 'css/employee.css', 'css/components/btp-workspace.css', 'css/components/feb-workspace.css'],
            'additionalScripts' => ['js/employee.js', 'js/components/feb-wizard.js'],
            'page' => new RequestFormPage(Csrf::token(), (string) ($_GET['type'] ?? '')),
        ]);
    }

    public function storeRequest(): void
    {
        $this->guard();
        try {
            $id = $this->service->createRequest(Auth::user(), $_POST, $_FILES);
            Session::flash('success', 'Votre demande a été soumise et transmise au manager.');
            $this->redirect('/espace-employe/demandes/' . $id);
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            $this->back();
        }
    }

    public function showRequest(string $id): void
    {
        AuthMiddleware::check();
        try {
            $this->employeeView('employee/request-show', 'Suivi de ma demande', 'requests', [
                'page' => new RequestShowPage(
                    $this->service->request(Auth::user(), (int) $id),
                    Csrf::token(),
                ),
            ]);
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/espace-employe');
        }
    }

    public function cancelRequest(string $id): void
    {
        $this->guard();
        try {
            $this->service->cancelRequest(Auth::user(), (int) $id);
            Session::flash('success', 'La demande a été annulée.');
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/espace-employe/demandes/' . (int) $id);
    }

    public function respondExplanation(string $id): void
    {
        $this->guard();
        try {
            $this->service->respondExplanation(Auth::user(), (int) $id, $_POST);
            Session::flash('success', 'Votre réponse a été transmise aux RH.');
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/espace-employe');
    }

    private function guard(): void
    {
        AuthMiddleware::check();
        if (!Csrf::verify($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Jeton CSRF invalide.');
            $this->back();
        }
    }
}
