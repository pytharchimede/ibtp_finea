<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start(); ?>
<div class="btp-shell btp-workspace"><nav class="btp-breadcrumb"><a href="<?= $e(View::url('btp/dashboard')) ?>">BTP</a><span>/</span><a href="<?= $e(View::url('btp/plannings')) ?>">Plannings</a><span>/</span><strong>Nouveau planning</strong></nav>
<header class="btp-banner"><small>PRÉPARATION DES TRAVAUX</small><h1><i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Créer un planning</h1><p>Sélectionnez le projet concerné avant de saisir les phases et tâches.</p></header>
<section class="btp-panel btp-section"><div class="btp-section-heading"><h2><i class="fa-solid fa-diagram-project" aria-hidden="true"></i> Projet concerné</h2></div><label for="btp-planning-project">Choisir un projet</label><select id="btp-planning-project" data-select-search data-placeholder="Rechercher un projet"><option value="">Sélectionner un projet</option><?php foreach($projetsBtp as $p): ?><option value="<?= (int)$p['id'] ?>"><?= $e($p['reference'].' · '.$p['intitule']) ?></option><?php endforeach; ?></select><div class="btp-editor-add-actions"><a id="btp-planning-create" data-base="<?= $e(View::url('btp/plannings')) ?>" class="btp-primary-link" href="<?= $e(View::url('btp/plannings/nouveau')) ?>"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i> Continuer vers le formulaire</a></div></section></div>
<?php $content=ob_get_clean();$additionalScripts=array_merge($additionalScripts??[],['js/components/form-components.js','js/components/btp-planning-list.js']);require BASE_PATH.'/views/layouts/module.php';
