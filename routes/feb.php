<?php
declare(strict_types=1);
use App\Router;use App\Controllers\Feb\FebController;
/** @var Router $router */
$router->group('/feb',static function(Router $router):void{
 $router->get('/',[FebController::class,'index']);$router->get('/nouvelle',[FebController::class,'create']);$router->post('/',[FebController::class,'store']);
 $router->get('/{id}',[FebController::class,'show']);$router->get('/{id}/documents/{documentId}',[FebController::class,'download']);$router->post('/{id}/soumettre',[FebController::class,'submit']);$router->post('/{id}/documents',[FebController::class,'document']);$router->post('/{id}/annuler',[FebController::class,'cancel']);$router->post('/{id}/decision',[FebController::class,'decide']);$router->post('/{id}/paiements',[FebController::class,'pay']);
});
