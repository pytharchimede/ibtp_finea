<?php
declare(strict_types=1);
namespace App\View\Components;
use App\Helpers\View;

final class CrmDashboard
{
    public static function render(array $data):string
    {
        $e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $fmt=static fn(mixed $n):string=>number_format((float)($n??0),0,',',' ');
        $counts=$data['counts']??[];$pipeline=$data['pipeline']??[];$actions=$data['actions']??[];$ao=$data['ao']??[];
        $stageLabels=['qualification'=>'Qualification','proposition'=>'Proposition','negociation'=>'Négociation','gagnee'=>'Gagnée','perdue'=>'Perdue'];
        $totalStages=max(1,array_sum(array_map(static fn($s)=>(int)$s['total'],$data['stages']??[])));
        $links=[
          ['label'=>'+ Nouveau client','path'=>'crm/clients/nouveau','primary'=>true],
          ['label'=>'Clients et prospects','path'=>'crm/clients'],
          ['label'=>'+ Nouvelle opportunité','path'=>'crm/opportunites/nouvelle','primary'=>true],
          ['label'=>'Pipeline commercial','path'=>'crm/opportunites'],
          ['label'=>'Veille appels d’offres','path'=>'crm/veille-ao'],
          ['label'=>'Critères AO','path'=>'crm/veille-ao/parametres'],
          ['label'=>'Interactions','path'=>'crm/interactions'],
        ];
        ob_start();?>
<div class="crm-dashboard">
<header class="crm-dashboard-hero"><div class="crm-dashboard-hero-top"><div><span class="crm-dashboard-eyebrow">FINEA · PILOTAGE COMMERCIAL</span><h1>Tableau de bord CRM</h1><p>Vue consolidée du portefeuille clients, des affaires et des appels d’offres.</p></div><div class="crm-dashboard-date"><?= $e(date('d/m/Y')) ?></div></div>
<nav class="crm-dashboard-hero-links" aria-label="Actions et accès CRM"><?php foreach($links as $link):?><a class="<?= !empty($link['primary'])?'is-primary':'' ?>" href="<?= $e(View::url($link['path'])) ?>"><?= $e($link['label']) ?></a><?php endforeach;?></nav></header>
<section class="crm-dashboard-kpis" aria-label="Indicateurs commerciaux">
<div class="crm-dashboard-kpi"><span>Clients actifs dans le répertoire</span><strong><?= $fmt($counts['clients']??0) ?></strong><small>Fiches qualifiées comme clients</small></div>
<div class="crm-dashboard-kpi"><span>Prospects</span><strong><?= $fmt($counts['prospects']??0) ?></strong><small>Relations à développer</small></div>
<div class="crm-dashboard-kpi"><span>Pipeline estimé (XOF)</span><strong><?= $fmt($pipeline['amount']??0) ?></strong><small><?= $fmt($pipeline['total']??0) ?> opportunités · montants prévisionnels</small></div>
<div class="crm-dashboard-kpi is-alert"><span>Relances à traiter</span><strong><?= $fmt($actions['total']??0) ?></strong><small>En retard ou prévues aujourd’hui</small></div>
<div class="crm-dashboard-kpi"><span>AO à échéance ouverte</span><strong><?= $fmt($ao['active']??0) ?></strong><small>Sur <?= $fmt($ao['total']??0) ?> AO recensés</small></div>
<div class="crm-dashboard-kpi"><span>AO pertinents</span><strong><?= $fmt($ao['relevant']??0) ?></strong><small>Score ≥ <?= (int)($ao['threshold']??60) ?> % et échéance ouverte</small></div>
<div class="crm-dashboard-kpi is-warning"><span>AO à échéance ≤ 14 jours</span><strong><?= $fmt($ao['soon']??0) ?></strong><small>Échéances commerciales proches</small></div>
<div class="crm-dashboard-kpi"><span>AO convertis en opportunités</span><strong><?= $fmt($ao['converted']??0) ?></strong><small>Affaires créées depuis la veille</small></div>
</section>
<div class="crm-dashboard-grid">
<section class="crm-dashboard-panel"><div class="crm-dashboard-panel-head"><h2>Répartition du pipeline</h2><span><?= $fmt($pipeline['total']??0) ?> opportunités</span></div>
<?php if(empty($data['stages'])):?><p class="crm-dashboard-empty">Aucune opportunité enregistrée.</p><?php else:?>
<div class="crm-dashboard-stages"><?php foreach($data['stages'] as $stage):$n=(int)$stage['total'];$pct=round($n/$totalStages*100);?><div class="crm-dashboard-stage"><div><strong><?= $e($stageLabels[$stage['stage']]??ucfirst((string)$stage['stage'])) ?></strong><span><?= $fmt($n) ?> · <?= $fmt($stage['amount']) ?> FCFA</span></div><div class="crm-dashboard-track"><span style="width:<?= $pct ?>%"></span></div></div><?php endforeach;?></div><?php endif;?>
<small class="crm-dashboard-note">Montants XOF uniquement ; il s’agit d’estimations et non de ventes réalisées.<?= (int)($pipeline['other_currencies']??0)>0?' Les opportunités en autres devises sont exclues des totaux monétaires.':'' ?></small>
</section>
<section class="crm-dashboard-panel"><div class="crm-dashboard-panel-head"><h2>Appels d’offres à surveiller</h2><span>21 prochains jours</span></div>
<?php if(empty($data['aoUpcoming'])):?><p class="crm-dashboard-empty">Aucune échéance AO dans les 21 prochains jours.</p><?php else:?><div class="crm-dashboard-list"><?php foreach($data['aoUpcoming'] as $row):?><a href="<?= $e(View::url('crm/veille-ao/detail')) ?>?id=<?= (int)$row['id'] ?>" class="crm-dashboard-list-row"><span><strong><?= $e($row['titre']) ?></strong><small><?= $e(($row['autorite_contractante']??'')?:'Autorité non renseignée') ?></small></span><span class="crm-dashboard-right"><b><?= (int)$row['score_pertinence'] ?> %</b><small><?= $e(date('d/m/Y',strtotime($row['date_limite']))) ?></small></span></a><?php endforeach;?></div><?php endif;?></section>
<section class="crm-dashboard-panel"><div class="crm-dashboard-panel-head"><h2>Relances et prochaines actions</h2><span>À suivre</span></div>
<?php if(empty($data['upcoming'])):?><p class="crm-dashboard-empty">Aucune relance en retard ou programmée dans les 14 prochains jours.</p><?php else:?><div class="crm-dashboard-list"><?php foreach($data['upcoming'] as $row):?><div class="crm-dashboard-list-row"><span><strong><?= $e($row['subject']) ?></strong><small><?= $e($row['client_name']) ?></small></span><span class="crm-dashboard-right"><small class="<?= $row['next_action_date']<date('Y-m-d')?'is-overdue':'' ?>"><?= $e(date('d/m/Y',strtotime($row['next_action_date']))) ?></small></span></div><?php endforeach;?></div><?php endif;?></section>
<section class="crm-dashboard-panel"><div class="crm-dashboard-panel-head"><h2>Dernières opportunités</h2><span>Activité commerciale</span></div>
<?php if(empty($data['opportunities'])):?><p class="crm-dashboard-empty">Aucune opportunité enregistrée.</p><?php else:?><div class="crm-dashboard-list"><?php foreach($data['opportunities'] as $row):?><div class="crm-dashboard-list-row"><span><strong><?= $e($row['title']) ?></strong><small><?= $e($row['client_name']) ?></small></span><span class="crm-dashboard-right"><b><?= $e($stageLabels[$row['stage']]??$row['stage']) ?></b><small><?= $row['currency']==='XOF'?$fmt($row['estimated_amount']).' FCFA':$e($row['currency']) ?></small></span></div><?php endforeach;?></div><?php endif;?></section>
<section class="crm-dashboard-panel crm-dashboard-panel-wide"><div class="crm-dashboard-panel-head"><h2>Derniers clients et prospects</h2><span>Répertoire commercial</span></div>
<?php if(empty($data['recent'])):?><p class="crm-dashboard-empty">Aucune fiche enregistrée.</p><?php else:?><div class="crm-dashboard-clients"><?php foreach($data['recent'] as $row):?><a href="<?= $e(View::url('crm/clients/modifier')) ?>?id=<?= (int)$row['id'] ?>"><span class="crm-dashboard-avatar"><?= $e(mb_strtoupper(mb_substr((string)$row['name'],0,1))) ?></span><span><strong><?= $e($row['name']) ?></strong><small><?= $e($row['type']) ?> · <?= $e($row['status']) ?></small></span></a><?php endforeach;?></div><?php endif;?></section>
</div></div>
<?php return (string)ob_get_clean();
    }
}
