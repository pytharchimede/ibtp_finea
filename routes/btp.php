<?php
declare(strict_types=1);
use App\Router;
use App\Controllers\Btp\ProjetController;
/** @var Router $router */
$router->group('/btp', static function(Router $router): void {
    $router->get('/debourses',[ProjetController::class,'budgetIndex']);
    $router->get('/debourses/nouveau',[ProjetController::class,'budgetCreate']);
    $router->post('/debourses',[ProjetController::class,'budgetStore']);
    $router->get('/bons-commande',[ProjetController::class,'orderIndex']);
    $router->get('/bons-commande/nouveau',[ProjetController::class,'orderCreate']);
    $router->post('/bons-commande',[ProjetController::class,'orderStore']);
    $router->get('/chantiers',[ProjetController::class,'chantierIndex']);
    $router->get('/chantiers/nouveau',[ProjetController::class,'chantierCreate']);
    $router->get('/chantiers/nouveau/projet/{id}',[ProjetController::class,'chantierWizard']);
    $router->get('/chantiers/projet/{id}',[ProjetController::class,'chantierWizard']);
    $router->post('/chantiers/projet/{id}',[ProjetController::class,'chantierStart']);
    $router->get('/plannings',[ProjetController::class,'planningIndex']);
    $router->get('/plannings/nouveau',[ProjetController::class,'planningCreate']);
    $router->get('/plannings/projet/{id}',[ProjetController::class,'planningEditor']);
    $router->post('/plannings/projet/{id}',[ProjetController::class,'planningSave']);
    $router->get('/devis',[ProjetController::class,'quoteIndex']);
    $router->get('/devis/projet/{id}',[ProjetController::class,'quoteEditor']);
    $router->post('/devis/projet/{id}',[ProjetController::class,'quoteSave']);
    $router->get('/devis/nouveau',[ProjetController::class,'quoteCreate']);
    $router->get('/dashboard',[ProjetController::class,'dashboard']);
    $router->get('/projets/nouveau',[ProjetController::class,'create']);
    $router->get('/projets',[ProjetController::class,'index']);
    $router->post('/projets',[ProjetController::class,'store']);
    $router->get('/projets/{id}',[ProjetController::class,'show']);
    $router->post('/projets/{id}/evenements',[ProjetController::class,'addEvent']);
    $router->post('/projets/{id}/documents',[ProjetController::class,'uploadDocument']);
    $router->get('/projets/{id}/documents/{documentId}',[ProjetController::class,'downloadDocument']);
    $router->post('/projets/{id}/devis',[ProjetController::class,'addQuote']);
});
