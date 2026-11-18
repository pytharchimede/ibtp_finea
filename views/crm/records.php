<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn($v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$isOpp=$crmKind==='opportunities';
ob_start();?>
<style>.crm-records{max-width:1150px;margin:35px auto;padding:0 20px;color:#15233d}.crm-records-header{padding:28px;background:linear-gradient(110deg,#1d2b57,#385b9b);color:#fff;border-radius:18px;margin-bottom:22px}.crm-records-header h1{color:#fff;margin:0 0 12px}.crm-records-header a{color:#facc15;margin-right:18px}.crm-records-card{background:#fff;border-radius:16px;padding:22px;overflow:auto}.crm-records table{width:100%;border-collapse:collapse;min-width:650px}.crm-records td,.crm-records th{text-align:left;padding:13px;border-bottom:1px solid #e2e8f0}.crm-records th{background:#f1f5f9}.crm-records-empty{padding:25px;text-align:center;color:#64748b}.crm-opp-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:20px}.crm-opp-actions a{display:inline-flex;align-items:center;justify-content:center;min-height:43px;padding:10px 15px;border:1px solid #d2deef;border-radius:10px;color:#244b92;font-weight:750;text-decoration:none}.crm-opp-actions .crm-opp-primary{background:#254b95;border-color:#254b95;color:#fff}@media(max-width:650px){.crm-opp-actions a{flex:1 1 100%}}
</style>
<div class="crm-records"><div class="crm-records-header"><h1><?= $isOpp?'Pipeline commercial':'Historique des interactions' ?></h1>
<a href="<?= $e(View::url('crm/dashboard')) ?>">Tableau de bord</a><a href="<?= $e(View::url('crm/clients')) ?>">Clients &amp; Prospects</a><a href="<?= $e(View::url('crm/clients/nouveau')) ?>">+ Nouveau client</a></div>
<?php if(!empty($crmOppSuccess)):?><p style="padding:12px;background:#dcfce7;color:#166534;border-radius:10px"><?= $e($crmOppSuccess) ?></p><?php endif;?>
<div class="crm-records-card"><?php if($isOpp):?><div class="crm-opp-actions"><a class="crm-opp-primary" href="<?= $e(View::url('crm/opportunites/nouvelle')) ?>">+ Nouvelle opportunité</a><a href="<?= $e(View::url('crm/clients')) ?>">Clients &amp; Prospects</a></div><?php endif;?><h2><?= $isOpp?'Opportunités enregistrées':'Interactions enregistrées' ?></h2>
<table><thead><tr><?php foreach($isOpp?['Client','Opportunité','Étape','Montant estimé','Échéance']:['Client','Canal','Objet','Date','Prochaine action'] as $col):?><th><?= $e($col) ?></th><?php endforeach;?></tr></thead><tbody>
<?php foreach($crmRows as $row):?><tr><td><?= $e($row['client_name']) ?></td>
<?php if($isOpp):?><td><?= $e($row['title']) ?></td><td><?= $e($row['stage']) ?></td><td><?= $e($row['estimated_amount']) ?> <?= $e($row['currency']) ?></td><td><?= $e($row['expected_close_date']) ?></td>
<?php else:?><td><?= $e($row['channel']) ?></td><td><?= $e($row['subject']) ?></td><td><?= $e($row['interaction_at']) ?></td><td><?= $e($row['next_action_date']) ?></td><?php endif;?></tr><?php endforeach;?>
<?php if(!$crmRows):?><tr><td colspan="5" class="crm-records-empty">Aucun enregistrement pour le moment.</td></tr><?php endif;?></tbody></table>

</div></div>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';?>
