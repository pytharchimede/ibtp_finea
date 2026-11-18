<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$counts=$crmAnalytics['counts'];$pipeline=$crmAnalytics['pipeline'];$actions=$crmAnalytics['actions'];
$fmt=static fn(mixed $n):string=>number_format((float)($n??0),0,',',' ');
ob_start();?>
<style>
.crm-dash{max-width:1250px;margin:0 auto;color:#15233d}.crm-dash *{box-sizing:border-box}
.crm-dash-hero{background:linear-gradient(115deg,#172b56,#405f9e);border-radius:22px;padding:32px;color:white;margin-bottom:23px}
.crm-dash-hero h1{font-size:clamp(1.6rem,3vw,2.3rem);margin:6px 0 8px;color:white}.crm-dash-hero p{margin:0;color:#e0eaff}
.crm-dash-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px}
.crm-dash-actions a{display:inline-block;background:#fff;color:#1e3a8a;padding:11px 15px;border-radius:11px;font-weight:800;text-decoration:none}
.crm-dash-actions a.secondary{background:#ffffff20;border:1px solid #ffffff60;color:#fff}
.crm-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-bottom:22px}
.crm-kpi{background:white;border:1px solid #e2e8f0;border-radius:17px;padding:22px}
.crm-kpi span{display:block;color:#64748b;font-size:.85rem}.crm-kpi strong{display:block;font-size:1.7rem;margin:9px 0;color:#15233d}
.crm-kpi small{color:#64748b}
.crm-dash-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.crm-dash-card{background:#fff;border:1px solid #e2e8f0;border-radius:17px;padding:23px;min-width:0}
.crm-dash-card h2{font-size:1.12rem;margin:0 0 16px}
.crm-dash-card table{width:100%;border-collapse:collapse;font-size:.88rem}
.crm-dash-card th,.crm-dash-card td{padding:11px 8px;text-align:left;border-bottom:1px solid #e2e8f0}
.crm-dash-card th{color:#64748b;font-size:.78rem}
.crm-dash-card a{color:#1d4ed8;font-weight:700}.crm-dash-empty{color:#64748b;line-height:1.5}
.crm-dash-scroll{overflow-x:auto}
@media(max-width:1000px){.crm-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:650px){.crm-kpis,.crm-dash-grid{grid-template-columns:1fr}.crm-dash-hero{padding:23px}}
</style>
<div class="crm-dash">
<header class="crm-dash-hero">
<div style="font-size:.76rem;letter-spacing:.14em;text-transform:uppercase;color:#fde68a;font-weight:800">FINEA · Gestion commerciale</div>
<h1>Tableau de bord CRM</h1><p>Suivez vos clients, votre portefeuille d'opportunités et les relances à effectuer.</p>
<div class="crm-dash-actions">
<a href="<?= $e(View::url('crm/clients/nouveau')) ?>">+ Nouveau client</a>
<a class="secondary" href="<?= $e(View::url('crm/clients')) ?>">Répertoire clients</a>
<a class="secondary" href="<?= $e(View::url('crm/opportunites')) ?>">Pipeline</a>
<a class="secondary" href="<?= $e(View::url('crm/interactions')) ?>">Interactions</a>
</div></header>
<div class="crm-kpis">
<div class="crm-kpi"><span>Clients</span><strong><?= $fmt($counts['clients']) ?></strong><small>Fiches de type client</small></div>
<div class="crm-kpi"><span>Prospects</span><strong><?= $fmt($counts['prospects']) ?></strong><small>Prospects enregistrés</small></div>
<div class="crm-kpi"><span>Pipeline estimé</span><strong><?= $fmt($pipeline['amount']) ?> <span style="display:inline;font-size:.75rem">FCFA*</span></strong><small><?= $fmt($pipeline['total']) ?> opportunité(s)</small></div>
<div class="crm-kpi"><span>Relances à traiter</span><strong><?= $fmt($actions['total']) ?></strong><small>Échéances aujourd'hui ou dépassées</small></div>
</div>
<div class="crm-dash-grid">
<section class="crm-dash-card"><h2>Opportunités par étape</h2>
<?php if(!$crmAnalytics['stages']):?><p class="crm-dash-empty">Aucune opportunité enregistrée.</p><?php else:?>
<div class="crm-dash-scroll"><table><thead><tr><th>Étape</th><th>Nombre</th><th>Montant estimé*</th></tr></thead><tbody>
<?php foreach($crmAnalytics['stages'] as $row):?><tr><td><?= $e($row['stage']) ?></td><td><?= $fmt($row['total']) ?></td><td><?= $fmt($row['amount']) ?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?>
<p class="crm-dash-empty" style="font-size:.76rem">* Montants indicatifs : ne pas additionner des opportunités de devises différentes. Les montants affichés ne sont pas des ventes réalisées.</p>
</section>
<section class="crm-dash-card"><h2>Relances et prochaines actions</h2>
<?php if(!$crmAnalytics['upcoming']):?><p class="crm-dash-empty">Aucune relance prévue dans les 14 prochains jours ni en retard.</p><?php else:?>
<div class="crm-dash-scroll"><table><thead><tr><th>Client</th><th>Action</th><th>Date</th></tr></thead><tbody>
<?php foreach($crmAnalytics['upcoming'] as $row):?><tr><td><?= $e($row['client_name']) ?></td><td><?= $e($row['subject']) ?></td><td><?= $e($row['next_action_date']) ?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?>
</section>
<section class="crm-dash-card"><h2>Derniers clients et prospects</h2>
<?php if(!$crmAnalytics['recent']):?><p class="crm-dash-empty">Commencez par créer votre premier client.</p><?php else:?>
<div class="crm-dash-scroll"><table><thead><tr><th>Nom</th><th>Catégorie</th><th>Statut</th></tr></thead><tbody>
<?php foreach($crmAnalytics['recent'] as $row):?><tr><td><a href="<?= $e(View::url('crm/clients')) ?>?edit=<?= (int)$row['id'] ?>"><?= $e($row['name']) ?></a></td><td><?= $e($row['type']) ?></td><td><?= $e($row['status']) ?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?>
</section>
<section class="crm-dash-card"><h2>Accès rapide</h2><p>Créez d'abord une fiche client, puis rattachez-la à un projet BTP.</p>
<p><a href="<?= $e(View::url('crm/clients/nouveau')) ?>">Créer un client →</a></p>
<p><a href="<?= $e(View::url('btp/projets')) ?>">Accéder aux projets &amp; chantiers →</a></p>
</section>
</div></div>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';?>
