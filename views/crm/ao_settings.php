<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<div class="ao-wrap"><header class="ao-hero"><a class="ao-back" href="<?= $e(View::url('crm/veille-ao')) ?>">← Retour aux appels d’offres</a><h1>Critères de pertinence AO</h1><p>Personnalisez la sélection des marchés pour les métiers d’IBTP, selon le fonctionnement FINEA.</p></header>
<?php if($aoFlash):?><p class="ao-alert" role="status"><?= $e($aoFlash) ?></p><?php endif;?>
<section class="ao-panel"><h2>Paramètres de sélection</h2>
<p>Le score combine les mots-clés BTP du moteur FINEA et les mots-clés spécifiques à IBTP. Les termes trouvés dans le titre ont davantage de poids. Un AO contenant un mot-clé exclu obtient un score de 0 %.</p>
<form class="ao-settings-form" method="post" action="<?= $e(View::url('crm/veille-ao/parametres')) ?>">
<input type="hidden" name="_csrf" value="<?= $e($aoCsrf) ?>">
<label for="ao-keywords"><strong>Mots-clés prioritaires IBTP</strong><small>Séparez les termes par une virgule, un point-virgule ou un retour à la ligne. Chaque correspondance ajoute 12 points, en complément des poids métier prédéfinis.</small></label>
<textarea id="ao-keywords" name="keywords" rows="7" maxlength="10000"><?= $e($aoSettings['keywords']??'') ?></textarea>
<label for="ao-excluded"><strong>Mots-clés d’exclusion</strong><small>Une correspondance exclut l’AO du scoring prioritaire en lui attribuant 0 %.</small></label>
<textarea id="ao-excluded" name="excluded_keywords" rows="6" maxlength="10000"><?= $e($aoSettings['excluded_keywords']??'') ?></textarea>
<label for="ao-min"><strong>Score minimum recommandé (%)</strong><small>Ce seuil sert à sélectionner les annonces pertinentes, sans supprimer les autres annonces collectées.</small></label>
<input type="number" id="ao-min" name="min_score" min="0" max="100" required value="<?= (int)($aoSettings['min_score']??60) ?>">
<button class="ao-primary" type="submit">Enregistrer et recalculer les scores</button>
</form></section></div>
<?php $content=ob_get_clean();$additionalStyles=array_merge($additionalStyles??[],['css/components/crm-ao.css']);require BASE_PATH.'/views/layouts/module.php';?>
