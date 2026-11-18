<?php

declare(strict_types=1);

use App\Router;
use App\Controllers\Crm\CrmDashboardController;
use App\Controllers\Crm\ClientController;
use App\Controllers\Crm\CrmRecordsController;
use App\Controllers\Crm\AoWatchController;

/** @var Router $router */

$router->group('/crm', function (Router $router): void {
    $router->get('/', [CrmDashboardController::class, 'index']);
    $router->get('/dashboard', [CrmDashboardController::class, 'index']);
    $router->get('/clients', [ClientController::class, 'index']);
    $router->get('/clients/nouveau', [ClientController::class, 'create']);
    $router->get('/clients/modifier', [ClientController::class, 'edit']);
    $router->post('/clients/qualifier', [ClientController::class, 'qualify']);
    $router->get('/clients/export', [ClientController::class, 'export']);
    $router->post('/clients', [ClientController::class, 'save']);
    $router->get('/opportunites/nouvelle', [CrmRecordsController::class, 'newOpportunity']);
    $router->post('/opportunites', [CrmRecordsController::class, 'saveOpportunity']);
    $router->get('/opportunites/piece-jointe', [CrmRecordsController::class, 'downloadAttachment']);
    $router->get('/opportunites/export', [CrmRecordsController::class, 'exportOpportunities']);
    $router->get('/opportunites', [CrmRecordsController::class, 'opportunities']);
    $router->get('/veille-ao', [AoWatchController::class, 'index']);
    $router->get('/veille-ao/detail', [AoWatchController::class, 'detail']);
    $router->post('/veille-ao/convertir', [AoWatchController::class, 'convert']);
    $router->post('/veille-ao/statut', [AoWatchController::class, 'updateStatus']);
    $router->get('/veille-ao/export', [AoWatchController::class, 'export']);
    $router->get('/veille-ao/synchronisations', [AoWatchController::class, 'logs']);
    $router->get('/interactions', [CrmRecordsController::class, 'interactions']);
});
