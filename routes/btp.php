<?php
declare(strict_types=1);
use App\Router;
use App\Controllers\Btp\AffaireController;
/** @var Router $router */
$router->group('/btp', static function (Router $router): void {
    $router->get('/affaires', [AffaireController::class, 'index']);
    $router->post('/affaires', [AffaireController::class, 'store']);
});
