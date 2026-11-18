<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<div class="ao-wrap"><header class="ao-hero"><a class="ao-back" href="<?= $e(View::url('crm/veille-ao')) ?>">← Retour à la veille</a><h1>Historique des collectes AO</h1><p>Suivi technique des synchronisations automatisées.</p></header>
<div class="ao-panel ao-table-scroll"><table class="ao-log-table"><thead><tr><th>Date</th><th>Source</th><th>Statut</th><th>Détectés</th><th>Nouveaux</th><th>Mis à jour</th><th>Erreur</th></tr></thead><tbody><?php foreach($aoLogs as $log):?><tr><td><?= $e($log['created_at']) ?></td><td><?= $e($log['source']) ?></td><td><?= $e($log['status']) ?></td><td><?= (int)$log['found_count'] ?></td><td><?= (int)$log['new_count'] ?></td><td><?= (int)$log['updated_count'] ?></td><td><?= $e($log['error_message']??'') ?></td></tr><?php endforeach;?><?php if(!$aoLogs):?><tr><td colspan="7">Aucune synchronisation enregistrée. Lancez la commande CLI sur le serveur.</td></tr><?php endif;?></tbody></table></div>
</div>
<?php $content=ob_get_clean();$additionalStyles=array_merge($additionalStyles??[],['css/components/crm-ao.css']);require BASE_PATH.'/views/layouts/module.php';?>
