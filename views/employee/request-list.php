<?php
declare(strict_types=1);
use App\View\Components\EmployeeRequestList;
use App\View\Components\Ui;
/** @var array $requests */
ob_start();
?>
<div class="finea-shell employee-shell">
 <div class="finea-container">
  <?=Ui::pageHeader('Mes demandes','Retrouvez toutes vos démarches, consultez leur progression et créez une nouvelle demande.',['eyebrow'=>'Espace employé · Suivi des demandes','class'=>'employee-hero','actions'=>[Ui::button('Nouvelle demande',['href'=>'espace-employe/demandes/nouvelle','variant'=>'primary','icon'=>'plus'])]])?>
  <?=Ui::section('Historique des demandes',EmployeeRequestList::render($requests),'Toutes vos demandes RH enregistrées, de la plus récente à la plus ancienne.')?>
 </div>
</div>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';
