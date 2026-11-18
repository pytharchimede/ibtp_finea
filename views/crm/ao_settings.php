<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\KeywordTags;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<div class="ao-wrap"><header class="ao-hero"><a class="ao-back" href="<?= $e(View::url('crm/veille-ao')) ?>">← Retour aux appels d’offres</a><h1>Critères de pertinence AO</h1><p>Personnalisez la sélection des marchés pour les métiers d’IBTP, selon le fonctionnement FINEA.</p></header>
<?php if($aoFlash):?><p class="ao-alert" role="status"><?= $e($aoFlash) ?></p><?php endif;?>
<section class="ao-panel"><h2>Paramètres de sélection</h2>
<p>Le score combine les mots-clés BTP du moteur FINEA et les mots-clés spécifiques à IBTP. Les termes trouvés dans le titre ont davantage de poids. Un AO contenant un mot-clé exclu obtient un score de 0 %.</p>
<form class="ao-settings-form" method="post" action="<?= $e(View::url('crm/veille-ao/parametres')) ?>">
<input type="hidden" name="_csrf" value="<?= $e($aoCsrf) ?>">
<?= KeywordTags::render('keywords','Mots-clés prioritaires IBTP',(string)($aoSettings['keywords']??''),['id'=>'ao-keywords','tone'=>'positive','hint'=>'Cliquez sur un badge pour le modifier, ou sur la croix rouge pour le supprimer. Entrée pour ajouter un mot-clé.']) ?>
<?= KeywordTags::render('excluded_keywords','Mots-clés d’exclusion',(string)($aoSettings['excluded_keywords']??''),['id'=>'ao-excluded','tone'=>'negative','hint'=>'Tout AO contenant un terme exclu reçoit un score de 0 %. Cliquez sur un badge pour le modifier.']) ?>
<label for="ao-min"><strong>Score minimum recommandé (%)</strong><small>Ce seuil sert à sélectionner les annonces pertinentes, sans supprimer les autres annonces collectées.</small></label>
<input type="number" id="ao-min" name="min_score" min="0" max="100" required value="<?= (int)($aoSettings['min_score']??60) ?>">
<button class="ao-primary" type="submit">Enregistrer et recalculer les scores</button>
</form></section></div>
<?php $content=ob_get_clean();$additionalStyles=array_merge($additionalStyles??[],['css/components/crm-ao.css','css/components/keyword-tags.css']);$additionalScripts=array_merge($additionalScripts??[],['js/components/keyword-tags.js']);require BASE_PATH.'/views/layouts/module.php';?>
