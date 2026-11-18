<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start(); ?>
<div class="btp-shell btp-workspace btp-offer-editor"><nav class="btp-breadcrumb"><a href="<?= $e(View::url('btp/plannings')) ?>">Plannings</a><span>/</span><strong><?= $e($projectBtp['reference']) ?></strong></nav><header class="btp-banner"><small>ÉTUDES TECHNIQUES</small><h1>Nouveau planning prévisionnel</h1><p><?= $e($projectBtp['intitule']) ?> · <?= $e($projectBtp['client_nom']) ?></p></header>
<?php if($errorBtp): ?><p class="btp-error" role="alert"><?= $e($errorBtp) ?></p><?php endif; ?>
<form method="post" id="btp-planning-form" action="<?= $e(View::url('btp/plannings/projet/'.(int)$projectBtp['id'])) ?>"><input type="hidden" name="_csrf" value="<?= $e($csrfBtp) ?>">
<section class="btp-panel btp-section"><div class="btp-section-heading"><h2>Identification</h2></div><label>Intitulé du planning<input name="title" required maxlength="180" value="<?= $e('Planning prévisionnel — '.$projectBtp['intitule']) ?>"></label></section>
<section class="btp-panel btp-section"><div class="btp-section-heading"><h2>Phases et tâches</h2></div><div class="btp-table-wrap"><table class="btp-editor-table"><thead><tr><th>Phase / tâche</th><th>Date de début</th><th>Date de fin</th><th>Durée</th><th></th></tr></thead><tbody id="btp-planning-tasks"></tbody></table></div><div class="btp-editor-add-actions"><button type="button" id="btp-add-planning-task">+ Ajouter une tâche</button></div></section>
<div class="btp-offer-actions"><a href="<?= $e(View::url('btp/plannings')) ?>">Annuler</a><button type="submit">Enregistrer le planning</button></div></form></div>
<?php $content=ob_get_clean();$additionalScripts=array_merge($additionalScripts??[],['js/components/btp-planning-editor.js']);require BASE_PATH.'/views/layouts/module.php';
