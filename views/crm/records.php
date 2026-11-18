<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn($v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$isOpp=$crmKind==='opportunities';
ob_start();?>
<style>.crm-records{max-width:1150px;margin:35px auto;padding:0 20px;color:#15233d}.crm-records-header{padding:28px;background:linear-gradient(110deg,#1d2b57,#385b9b);color:#fff;border-radius:18px;margin-bottom:22px}.crm-records-header h1{color:#fff;margin:0 0 12px}.crm-records-header a{color:#facc15;margin-right:18px}.crm-records-card{background:#fff;border-radius:16px;padding:22px;overflow:auto}.crm-records table{width:100%;border-collapse:collapse;min-width:650px}.crm-records td,.crm-records th{text-align:left;padding:13px;border-bottom:1px solid #e2e8f0}.crm-records th{background:#f1f5f9}.crm-records-empty{padding:25px;text-align:center;color:#64748b}</style>
<div class="crm-records"><div class="crm-records-header"><h1><?= $isOpp?'Pipeline commercial':'Historique des interactions' ?></h1>
<a href="<?= $e(View::url('crm/dashboard')) ?>">Tableau de bord</a><a href="<?= $e(View::url('crm/clients')) ?>">Clients &amp; Prospects</a><a href="<?= $e(View::url('crm/clients/nouveau')) ?>">+ Nouveau client</a></div>
<div class="crm-records-card"><h2><?= $isOpp?'Opportunités enregistrées':'Interactions enregistrées' ?></h2>
<table><thead><tr><?php foreach($isOpp?['Client','Opportunité','Étape','Montant estimé','Échéance']:['Client','Canal','Objet','Date','Prochaine action'] as $col):?><th><?= $e($col) ?></th><?php endforeach;?></tr></thead><tbody>
<?php foreach($crmRows as $row):?><tr><td><?= $e($row['client_name']) ?></td>
<?php if($isOpp):?><td><?= $e($row['title']) ?></td><td><?= $e($row['stage']) ?></td><td><?= $e($row['estimated_amount']) ?> <?= $e($row['currency']) ?></td><td><?= $e($row['expected_close_date']) ?></td>
<?php else:?><td><?= $e($row['channel']) ?></td><td><?= $e($row['subject']) ?></td><td><?= $e($row['interaction_at']) ?></td><td><?= $e($row['next_action_date']) ?></td><?php endif;?></tr><?php endforeach;?>
<?php if(!$crmRows):?><tr><td colspan="5" class="crm-records-empty">Aucun enregistrement pour le moment.</td></tr><?php endif;?></tbody></table>
<p>Consultation disponible. La création et la modification des opportunités et interactions seront intégrées dans le prochain lot CRM.</p>
</div></div>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';?>
