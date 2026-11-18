<?php
declare(strict_types=1);
use App\View\Components\CrmAoCard;
use App\View\Components\CrmAoFilters;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<div class="ao-wrap"><header class="ao-hero"><span class="ao-kicker">FINEA / CRM</span><h1>Veille appels d’offres</h1><p>Marchés publics ivoiriens, scoring BTP et préparation des opportunités.</p></header>
<?php if($aoFlash):?><p class="ao-alert" role="status"><?= $e($aoFlash) ?></p><?php endif;?>
<div class="ao-stats"><div><strong><?= (int)($aoStats['total']??0) ?></strong><span>AO recensés</span></div><div><strong><?= (int)($aoStats['relevant']??0) ?></strong><span>Pertinence ≥ 60 %</span></div><div><strong><?= (int)($aoStats['soon']??0) ?></strong><span>Échéances à 14 jours</span></div><div><strong><?= (int)($aoStats['converted']??0) ?></strong><span>Opportunités CRM</span></div></div>
<?= CrmAoFilters::render($aoFilters) ?>
<p class="ao-count"><?= count($aoItems) ?> résultat(s) affiché(s). La collecte s’effectue côté serveur ; les sources qui nécessitent un compte doivent être configurées séparément.</p>
<?php if($aoItems):?><section class="ao-grid" aria-label="Appels d'offres"><?php foreach($aoItems as $item):?><?= CrmAoCard::render($item) ?><?php endforeach;?></section><?php else:?><div class="ao-empty">Aucun appel d’offres ne correspond aux critères sélectionnés. Consultez l’historique des collectes pour vérifier les sources.</div><?php endif;?>
</div>
<?php $content=ob_get_clean();$additionalStyles=array_merge($additionalStyles??[],['css/components/crm-ao.css']);require BASE_PATH.'/views/layouts/module.php';?>
