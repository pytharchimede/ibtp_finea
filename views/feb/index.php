<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\Ui;
$e=static fn($v):string=>View::e((string)$v);
$states=['brouillon'=>['Brouillon','neutral'],'soumis'=>['Soumise','info'],'complement'=>['Complément demandé','warning'],'valide'=>['Validée','success'],'refuse'=>['Refusée','danger'],'annule'=>['Annulée','neutral'],'partiellement_paye'=>['Paiement partiel','warning'],'paye'=>['Payée','success']];
ob_start();
?>
<div class="finea-shell feb-employee-list"><div class="finea-container">
<?=Ui::pageHeader('Mes fiches d’expression de besoin','Consultez vos demandes, leurs décisions et leur avancement jusqu’au paiement.',['eyebrow'=>'FINEA · Suivi des FEB','actions'=>$canCreate?[Ui::button('Nouvelle FEB',['href'=>'feb/nouvelle','variant'=>'primary','icon'=>'plus'])]:[]])?>
<?php ob_start(); ?>
<div class="finea-table-wrap"><table class="finea-table"><thead><tr><th>Référence</th><th>Chantier</th><th>Bénéficiaire</th><th>Montant</th><th>État</th><th>Consulter</th></tr></thead><tbody>
<?php foreach($requests as $r):$state=$states[(string)$r['status']]??[(string)$r['status'],'neutral']; ?>
<tr><td><strong><?=$e($r['reference']?:'#'.$r['id'])?></strong></td><td><?=$e($r['chantier_code'].' · '.$r['chantier_name'])?></td><td><?=$e($r['beneficiary_name'])?></td><td><?=number_format((float)$r['total_amount'],0,',',' ')?> FCFA</td><td><?=Ui::badge($state[0],$state[1])?></td><td><?=Ui::button('Détails',['href'=>'feb/'.(int)$r['id'],'variant'=>'secondary','icon'=>'search'])?></td></tr>
<?php endforeach; ?>
<?php if(!$requests):?><tr><td colspan="6"><?=Ui::emptyState('Aucune FEB à afficher','Créez votre première demande depuis l’Espace employé.')?></td></tr><?php endif;?>
</tbody></table></div>
<?php echo Ui::section('Demandes et suivi',(string)ob_get_clean(),'Les FEB visibles dépendent de vos habilitations chantier.'); ?>
</div></div>
<style>.feb-employee-list .finea-section-card{border:1px solid #dbe4f0;border-radius:17px;padding:18px;margin:18px 0}.feb-employee-list .finea-table-wrap{overflow-x:auto}.feb-employee-list .finea-action-btn{display:inline-flex;align-items:center;justify-content:center}.feb-employee-list .finea-btn-content{display:inline-flex;align-items:center;justify-content:center;gap:8px}.feb-employee-list .finea-btn-icon{flex-shrink:0}</style>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';
