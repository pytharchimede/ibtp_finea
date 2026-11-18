<?php
declare(strict_types=1);
use App\Router;
use App\Controllers\Btp\ProjetController;
/** @var Router $router */
$router->group('/btp', static function(Router $router): void {
    $router->get('/dashboard',[ProjetController::class,'dashboard']);
    $router->get('/projets/nouveau',[ProjetController::class,'create']);
    $router->get('/projets',[ProjetController::class,'index']);
    $router->post('/projets',[ProjetController::class,'store']);
});
