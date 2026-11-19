<?php
declare(strict_types=1);
use App\Router;use App\Controllers\Procurement\SupplierController;
/** @var Router $router */
$router->group('/fournisseurs',static function(Router $router):void{
 $router->get('/',[SupplierController::class,'index']);
 $router->post('/',[SupplierController::class,'create']);
 $router->post('/circuits',[SupplierController::class,'workflow']);
 $router->post('/circuits/{id}/activer',[SupplierController::class,'activate']);
 $router->post('/{id}/soumettre',[SupplierController::class,'submit']);
 $router->post('/{id}/decision',[SupplierController::class,'decide']);
});
