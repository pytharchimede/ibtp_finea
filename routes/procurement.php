<?php
declare(strict_types=1);
use App\Router;use App\Controllers\Procurement\SupplierController;
/** @var Router $router */
$router->group('/fournisseurs',static function(Router $router):void{
 $router->get('/',[SupplierController::class,'index']);
 $router->get('/dashboard',[SupplierController::class,'dashboard']);
 $router->get('/nouveau',[SupplierController::class,'newSupplier']);
 $router->get('/circuits',[SupplierController::class,'circuits']);
 $router->post('/',[SupplierController::class,'create']);
 $router->post('/circuits',[SupplierController::class,'workflow']);
 $router->post('/exigences',[SupplierController::class,'requirement']);
 $router->get('/documents/{id}/telecharger',[SupplierController::class,'download']);
 $router->get('/{id}',[SupplierController::class,'dossier']);
 $router->post('/{id}/documents',[SupplierController::class,'upload']);
 $router->post('/circuits/{id}/activer',[SupplierController::class,'activate']);
 $router->post('/{id}/soumettre',[SupplierController::class,'submit']);
 $router->post('/{id}/decision',[SupplierController::class,'decide']);
});
