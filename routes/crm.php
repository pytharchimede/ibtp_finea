<?php

declare(strict_types=1);

use App\Router;
use App\Controllers\Crm\CrmDashboardController;
use App\Controllers\Crm\ClientController;
use App\Controllers\Crm\CrmRecordsController;

/** @var Router $router */

$router->group('/crm', function (Router $router): void {
    $router->get('/', [CrmDashboardController::class, 'index']);
    $router->get('/dashboard', [CrmDashboardController::class, 'index']);
    $router->get('/clients', [ClientController::class, 'index']);
    $router->post('/clients', [ClientController::class, 'save']);
    $router->get('/opportunites', [CrmRecordsController::class, 'opportunities']);
    $router->get('/interactions', [CrmRecordsController::class, 'interactions']);
});
