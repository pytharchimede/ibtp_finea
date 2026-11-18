<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$fmt=static fn(mixed $n):string=>number_format((int)($n??0),0,',',' ');
ob_start();?>
<div class="btp-shell"><header class="btp-banner"><div><small>FINEA · PILOTAGE BTP</small><h1>Projets & Chantiers</h1><p>Suivez les études, soumissions et projets en exécution depuis un espace métier unique.</p></div><nav><a href="<?= $e(View::url('btp/projets/nouveau')) ?>">+ Nouveau projet</a><a href="<?= $e(View::url('btp/projets')) ?>">Tous les projets</a></nav></header>
<section class="btp-stats"><?php foreach(['total'=>'Projets enregistrés','etudes'=>'À l’étude','soumissions'=>'En soumission','execution'=>'En exécution'] as $key=>$label):?><article><span><?= $e($label) ?></span><strong><?= $fmt($summaryBtp[$key]??0) ?></strong></article><?php endforeach;?></section>
<section class="btp-panel"><h2>Derniers projets</h2><?php if(!$projets):?><p>Aucun projet. Commencez par sélectionner une opportunité disponible.</p><?php else:?><div class="btp-table-wrap"><table><thead><tr><th>Référence</th><th>Projet</th><th>Client</th><th>Statut</th></tr></thead><tbody><?php foreach($projets as $p):?><tr><td><?= $e($p['reference']) ?></td><td><?= $e($p['intitule']) ?></td><td><?= $e($p['client_nom']) ?></td><td><?= $e($p['statut']) ?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></section></div>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';