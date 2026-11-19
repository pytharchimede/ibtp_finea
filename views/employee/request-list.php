<?php
declare(strict_types=1);
use App\View\Components\EmployeeRequestList;
use App\Helpers\View;
use App\View\Components\Ui;
/** @var array $requests */
ob_start();
?>
<div class="finea-shell employee-shell">
 <div class="finea-container">
  <?=Ui::pageHeader('Mes demandes','Retrouvez toutes vos démarches, consultez leur progression et créez une nouvelle demande.',['eyebrow'=>'Espace employé · Suivi des demandes','class'=>'employee-hero','actions'=>[Ui::button('Nouvelle demande',['href'=>'espace-employe/demandes/nouvelle','variant'=>'primary','icon'=>'plus'])]])?>
  <?=Ui::section('Demandes RH',EmployeeRequestList::render($requests),'Congés, absences, avances et autres démarches administratives.')?>
  <?php ob_start(); ?>
  <div class="employee-request-list">
  <?php foreach(($febRequests ?? []) as $feb): ?>
   <a class="employee-request-item" href="<?=View::url('feb/'.(int)$feb['id'])?>">
    <span class="employee-request-item-icon tone-blue">FEB</span>
    <span class="employee-request-item-main">
     <small><?=View::e((string)($feb['reference'] ?: 'FEB #'.$feb['id']))?></small>
     <strong><?=($feb['scope']==='bureau'?'FEB Bureau':'FEB Chantier')?></strong>
     <em><?=View::e((string)($feb['location_name']??''))?> · <?=number_format((float)$feb['total_amount'],0,',',' ')?> FCFA</em>
    </span>
    <span class="employee-request-progress">
     <small>État</small>
     <strong><?=View::e((string)$feb['status'])?></strong>
     <small><?=View::e((string)$feb['needed_at'])?></small>
    </span>
    <span class="employee-request-arrow" aria-hidden="true">→</span>
   </a>
  <?php endforeach; ?>
  <?php if(!($febRequests ?? [])): ?><?=Ui::emptyState('Aucune FEB','Vos FEB Chantier et Bureau apparaîtront ici après leur enregistrement.')?><?php endif; ?>
  </div>
  <?php echo Ui::section('Fiches d’expression de besoin',(string)ob_get_clean(),'FEB chantier et bureau avec accès direct au circuit de validation et aux justificatifs.'); ?>
 </div>
</div>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';
