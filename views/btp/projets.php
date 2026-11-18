<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<div class="btp-shell"><header class="btp-banner"><div><small>FINEA · PROJETS & CHANTIERS</small><h1>Portefeuille de projets</h1><p>Chaque nouveau projet est obligatoirement issu d'une opportunité commerciale existante.</p></div><nav><a href="<?= $e(View::url('btp/dashboard')) ?>">Tableau de bord</a><a href="<?= $e(View::url('btp/projets/nouveau')) ?>">+ Nouveau projet</a></nav></header>
<?php if(!empty($errorBtp)):?><p class="btp-error"><?= $e($errorBtp) ?></p><?php endif;?>
<section class="btp-panel"><h2>Projets enregistrés</h2><?php if(!$projets):?><p>Aucun projet pour le moment.</p><?php else:?><div class="btp-table-wrap"><table><thead><tr><th>Référence</th><th>Projet</th><th>Client</th><th>Dossier d'origine</th><th>Statut</th></tr></thead><tbody><?php foreach($projets as $p):?><tr><td><?= $e($p['reference']) ?></td><td><?= $e($p['intitule']) ?></td><td><?= $e($p['client_nom']) ?></td><td><?= $p['crm_opportunity_id']!==null?'#'.(int)$p['crm_opportunity_id']:'Projet historique' ?></td><td><?= $e($p['statut']) ?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></section></div>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';