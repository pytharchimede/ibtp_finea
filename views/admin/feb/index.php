<?php
declare(strict_types=1);
use App\Helpers\Csrf;
use App\Helpers\View;
use App\View\Components\Ui;
use App\View\Components\FormControls as Fc;
use App\View\Pages\Admin\FebAdminPage;
/** @var FebAdminPage $page */
$e=static fn(mixed $v):string=>View::e((string)$v);
ob_start();
$chantierOptions=[];foreach($page->chantiers as $c)$chantierOptions[(string)$c['id']]=$c['code'].' · '.$c['nom'];
$serviceOptions=[];foreach($page->officeServices as $srv)$serviceOptions[(string)$srv['id']]=$srv['name'];
$userOptions=[];foreach($page->users as $u)$userOptions[(string)$u['id']]=$u['full_name'].' · '.$u['email'];
?>
<?=Fc::assets()?>
<div class="finea-shell feb-admin-shell"><div class="finea-container">
<?=Ui::pageHeader('Administration FEB','Configurer les circuits, les accès chantier et les budgets d’exécution sans élargissement implicite des droits.',['eyebrow'=>'Configuration sécurisée','class'=>'admin-hero','actions'=>[Ui::button('Nouveau workflow',['href'=>'admin/feb/workflows/nouveau','variant'=>'accent','icon'=>'plus'])]])?>

<?php if($page->showWorkflowForm): $workflow=$page->workflow; $steps=(array)($workflow['steps']??[]); ?>
<form method="post" action="<?=View::url($workflow?'admin/feb/workflows/'.(int)$workflow['id']:'admin/feb/workflows')?>" class="admin-user-form">
<?=Csrf::input()?>
<?php ob_start(); ?><div class="admin-form-grid"><label>Code<input name="code" required pattern="[a-z0-9_-]{2,50}" value="<?=$e($workflow['code']??'')?>"></label><label>Libellé<input name="label" required maxlength="120" value="<?=$e($workflow['label']??'')?>"></label></div><?php echo Ui::section('Circuit',(string)ob_get_clean(),$workflow?'Utilisé par '.(int)$workflow['usage_count'].' FEB':'Nouveau circuit'); ?>
<?php ob_start(); ?><div class="finea-table-wrap"><table class="finea-table"><thead><tr><th>#</th><th>Code</th><th>Libellé</th><th>Permission</th><th>Validateur dédié</th></tr></thead><tbody>
<?php for($i=0;$i<6;$i++): $s=$steps[$i]??[]; ?><tr><td><?=$i+1?></td><td><input name="steps[<?=$i?>][code]" value="<?=$e($s['code']??'')?>"></td><td><input name="steps[<?=$i?>][label]" value="<?=$e($s['label']??'')?>"></td><td><select class="feb-enhanced-select" name="steps[<?=$i?>][permission_action]"><?php foreach(['update'=>'Modifier','view'=>'Lire','create'=>'Créer','delete'=>'Supprimer'] as $value=>$label): ?><option value="<?=$value?>" <?=($s['permission_action']??'update')===$value?'selected':''?>><?=$label?></option><?php endforeach; ?></select></td><td><select class="feb-enhanced-select" name="steps[<?=$i?>][assigned_user_id]"><option value="">Tous les validateurs autorisés</option><?php foreach($page->users as $u): ?><option value="<?=$u['id']?>" <?=(int)($s['assigned_user_id']??0)===(int)$u['id']?'selected':''?>><?=$e($u['full_name'])?></option><?php endforeach; ?></select></td></tr><?php endfor; ?>
</tbody></table></div><?php echo Ui::section('Étapes ordonnées',(string)ob_get_clean(),'Immuables dès la première FEB utilisant ce circuit.'); ?>
<div class="admin-form-actions"><?=Ui::button('Annuler',['href'=>'admin/feb','variant'=>'secondary','icon'=>'arrow-left'])?><?=Ui::button('Enregistrer',['type'=>'submit','variant'=>'primary','icon'=>'save'])?></div></form>
<?php endif; ?>

<?php ob_start(); ?><div class="finea-table-wrap"><table class="finea-table"><thead><tr><th>Circuit</th><th>Étapes</th><th>Utilisation</th><th>État</th><th>Actions</th></tr></thead><tbody>
<?php foreach($page->workflows as $w): ?><tr><td><strong><?=$e($w['label'])?></strong><small class="admin-table-subtitle"><?=$e($w['code'])?></small></td><td><?=(int)$w['step_count']?></td><td><?=(int)$w['usage_count']?> FEB</td><td><?=$w['active']?'Actif':'Inactif'?></td><td><div class="admin-row-actions"><?=Ui::button('Modifier',['href'=>'admin/feb/workflows/'.(int)$w['id'].'/modifier','variant'=>'secondary','icon'=>'pencil'])?><form method="post" action="<?=View::url('admin/feb/workflows/'.(int)$w['id'].'/etat')?>" onsubmit="return confirm('Confirmer ce changement d’état ?')"><?=Csrf::input()?><input type="hidden" name="active" value="<?=$w['active']?'0':'1'?>"><?=Ui::button($w['active']?'Désactiver':'Activer',['type'=>'submit','variant'=>$w['active']?'danger':'primary','class'=>'fc-workflow-toggle','icon'=>$w['active']?'ban':'power'])?></form></div></td></tr><?php endforeach; ?>
<?php if(!$page->workflows): ?><tr><td colspan="5"><?=Ui::emptyState('Aucun workflow FEB','Créez le premier circuit de validation.')?></td></tr><?php endif; ?></tbody></table></div><?php echo Ui::section('Workflows FEB',(string)ob_get_clean()); ?>

<?php ob_start(); ?>
<form method="post" action="<?=View::url('admin/feb/affectations-groupe')?>" class="admin-user-form feb-access-form">
<?=Csrf::input()?>
<div class="admin-form-grid"><div><label>Chantier</label><?=Fc::searchable("chantier_id",$chantierOptions,["placeholder"=>"Rechercher un chantier…"])?></div></div>
<fieldset class="feb-user-picker"><legend>Collaborateurs — sélection multiple</legend><?=Fc::searchable("user_ids",$userOptions,["multiple"=>true,"placeholder"=>"Saisir un nom ou un e-mail…"])?><p>Les collaborateurs sélectionnés apparaissent sous forme de badges.</p></fieldset>
<fieldset class="feb-role-picker"><legend>Rôles à attribuer</legend><?=Fc::checkbox("roles[demander]","Demandeur")?><?=Fc::checkbox("roles[valider]","Validateur")?><?=Fc::checkbox("roles[payer]","Payeur")?></fieldset>
<p>Les rôles choisis remplaceront les rôles FEB existants de chaque personne sélectionnée sur ce chantier. Les autres collaborateurs ne seront pas modifiés. Les permissions globales restent obligatoires.</p>
<div class="admin-form-actions"><?=Ui::button('Affecter les collaborateurs sélectionnés',['type'=>'submit','variant'=>'primary','icon'=>'users'])?></div></form>


<div class="finea-table-wrap"><table class="finea-table"><thead><tr><th>Chantier</th><th>Utilisateur</th><th>Rôle</th><th>Attribué par</th></tr></thead><tbody><?php foreach($page->accesses as $a): ?><tr><td><?=$e($a['code'].' · '.$a['nom'])?></td><td><?=$e($a['full_name'])?></td><td><?=$e($a['access_role'])?></td><td><?=$e($a['granted_by_name'])?></td></tr><?php endforeach; ?><?php if(!$page->accesses): ?><tr><td colspan="4"><?=Ui::emptyState('Aucune affectation','Aucun accès chantier FEB configuré.')?></td></tr><?php endif; ?></tbody></table></div><?php echo Ui::section('Affectations par chantier',(string)ob_get_clean(),'Les permissions globales FEB restent obligatoires.'); ?>

<?php ob_start(); ?><form method="get" action="<?=View::url('admin/feb')?>" class="admin-user-form"><label>Chantier à configurer</label><?=Fc::searchable("chantier",$chantierOptions,["selected"=>$page->selectedChantier?[(string)$page->selectedChantier]:[],"placeholder"=>"Rechercher un chantier…"])?><?=Ui::button('Afficher les budgets',['type'=>'submit','variant'=>'primary','icon'=>'search'])?></form>
<?php if($page->selectedChantier): ?><form method="post" action="<?=View::url('admin/feb/budgets')?>" class="admin-user-form"><?=Csrf::input()?><input type="hidden" name="chantier_id" value="<?=$page->selectedChantier?>"><label>Budget validé<select class="feb-enhanced-select" name="budget_id" required><option value="">Sélectionner</option><?php foreach($page->eligibleBudgets as $b): ?><option value="<?=$b['id']?>"><?=$e('Révision '.$b['revision'].' · '.$b['reference'].' · '.number_format((float)$b['total_cost'],0,',',' ').' FCFA')?></option><?php endforeach; ?></select></label><div class="admin-form-actions"><?=Ui::button('Rattacher le budget',['type'=>'submit','variant'=>'primary','icon'=>'check'])?></div></form><?php endif; ?>
<div class="finea-table-wrap"><table class="finea-table"><thead><tr><th>Chantier</th><th>Budget</th><th>Montant</th><th>Utilisation</th></tr></thead><tbody><?php foreach($page->budgets as $b): ?><tr><td><?=$e($b['code'].' · '.$b['nom'])?></td><td>#<?=(int)$b['budget_id']?> · révision <?=(int)$b['revision']?></td><td><?=number_format((float)$b['total_cost'],0,',',' ')?> FCFA</td><td><?=(int)$b['usage_count']?> FEB</td></tr><?php endforeach; ?><?php if(!$page->budgets): ?><tr><td colspan="4"><?=Ui::emptyState('Aucun budget rattaché','Chaque chantier FEB exige un budget validé explicite.')?></td></tr><?php endif; ?></tbody></table></div><?php echo Ui::section('Budgets d’exécution',(string)ob_get_clean()); ?>

<?php ob_start(); ?>
<form method="post" action="<?=View::url('admin/feb/budgets-bureau')?>" class="admin-user-form feb-office-form">
<?=Csrf::input()?>
<div class="admin-form-grid">
 <div class="feb-office-field"><label>Service bénéficiaire</label><?=Fc::searchable("service_id",$serviceOptions,["placeholder"=>"Rechercher un service RH actif…"])?></div>
 <label>Exercice budgétaire<input type="number" name="fiscal_year" min="<?=date('Y')?>" max="<?=(int)date('Y')+1?>" value="<?=date('Y')?>" required></label>
 <label>Code du poste<input type="text" name="code" required maxlength="50" pattern="[a-zA-Z0-9_-]{2,50}" placeholder="FOURNITURES"></label>
 <label>Poste budgétaire<input type="text" name="label" required maxlength="180" placeholder="Fournitures et consommables"></label>
 <label>Montant budgété (FCFA)<input type="number" name="budget_amount" min="1" step="0.01" required></label>
</div>
<div class="admin-form-actions"><?=Ui::button('Créer l’affectation',['type'=>'submit','variant'=>'primary','icon'=>'plus'])?></div>
</form>
<div class="finea-table-wrap"><table class="finea-table"><thead><tr><th>Exercice</th><th>Service</th><th>Affectation</th><th>Budget</th><th>État</th></tr></thead><tbody>
<?php foreach($page->officeAllocations as $allocation): ?><tr><td><?=(int)$allocation['fiscal_year']?></td><td><?=$e($allocation['service_name'])?></td><td><strong><?=$e($allocation['label'])?></strong><small class="admin-table-subtitle"><?=$e($allocation['code'])?></small></td><td><?=number_format((float)$allocation['budget_amount'],0,',',' ')?> FCFA</td><td><?=$allocation['active']?'Actif':'Inactif'?></td></tr><?php endforeach; ?>
<?php if(!$page->officeAllocations): ?><tr><td colspan="5"><?=Ui::emptyState('Aucun budget bureau','Créez une affectation financière pour débloquer les FEB Bureau.')?></td></tr><?php endif; ?>
</tbody></table></div>
<?php echo Ui::section('Budgets FEB Bureau',(string)ob_get_clean(),'Service RH → affectation annuelle → budget contrôlé à la soumission.'); ?>

<?php ob_start(); ?><div class="finea-table-wrap"><table class="finea-table"><thead><tr><th>Date</th><th>Acteur</th><th>Objet</th><th>Action</th></tr></thead><tbody><?php foreach($page->history as $h): ?><tr><td><?=$e($h['created_at'])?></td><td><?=$e($h['actor_name'])?></td><td><?=$e($h['entity_type'].($h['entity_id']?' #'.$h['entity_id']:''))?></td><td><?=$e($h['action'])?></td></tr><?php endforeach; ?><?php if(!$page->history): ?><tr><td colspan="4"><?=Ui::emptyState('Aucun historique','Les changements administratifs apparaîtront ici.')?></td></tr><?php endif; ?></tbody></table></div><?php echo Ui::section('Historique administratif',(string)ob_get_clean()); ?>
</div></div>
<style>
.feb-admin-shell .finea-section-card{border:1px solid #dbe4f0;border-radius:18px;box-shadow:0 8px 28px rgba(15,23,42,.045);margin-bottom:22px}
.feb-admin-shell .admin-user-form{padding:8px 0 16px}
.feb-admin-shell .admin-user-form label{font-weight:600;color:#334155}.feb-admin-shell .feb-office-form .admin-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 22px;align-items:end}.feb-admin-shell .feb-office-form .admin-form-grid>label,.feb-admin-shell .feb-office-field{display:grid;gap:8px;margin:0;min-width:0}.feb-admin-shell .feb-office-form input{display:block;width:100%;min-height:46px;padding:11px 14px;border:1px solid #cbd5e1;border-radius:11px;background:#fff;color:#0f172a;font:inherit;box-sizing:border-box}.feb-admin-shell .feb-office-form input:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.14)}.feb-admin-shell .feb-office-form .fc-searchable{min-width:0}
.feb-admin-shell .feb-enhanced-select{display:block;width:100%;min-height:46px;max-width:100%;padding:10px 42px 10px 14px;border:1px solid #cbd5e1;border-radius:11px;background-color:#fff;color:#0f172a;font:inherit;box-shadow:0 1px 3px rgba(15,23,42,.04);transition:border-color .15s,box-shadow .15s}
.feb-admin-shell .feb-enhanced-select:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.14)}
.feb-admin-shell .finea-table-wrap{border-radius:12px;overflow-x:auto}
.feb-admin-shell .admin-form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px}
@media(max-width:720px){.feb-admin-shell .feb-office-form .admin-form-grid,.feb-admin-shell .admin-form-grid{grid-template-columns:1fr}.feb-admin-shell .feb-role-picker{display:grid;gap:12px}}
.fc-workflow-toggle.finea-action-btn--danger{background:#fee2e2!important;color:#b91c1c!important;border:1px solid #fecaca!important;font-weight:700}.fc-workflow-toggle.finea-action-btn--danger:hover{background:#fecaca!important}.fc-config-submit{margin-top:10px;padding:10px 16px;border-radius:10px;border:0;background:#1d4ed8;color:#fff;font-weight:700;cursor:pointer}.feb-user-picker,.feb-role-picker{border:1px solid #dbe4f0;border-radius:12px;padding:16px;margin:14px 0}.feb-role-picker{display:flex;flex-wrap:wrap;gap:20px}
.finea-btn-centered{display:inline-flex!important;align-items:center!important;justify-content:center!important;text-align:center!important;gap:8px;vertical-align:middle;min-height:40px}.finea-btn-centered .finea-btn-content{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:100%;line-height:1.25}.finea-btn-centered .finea-btn-label{text-align:center}.finea-btn-centered .finea-btn-icon{flex-shrink:0}.feb-admin-shell .admin-row-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.feb-admin-shell .admin-row-actions form{margin:0}.feb-admin-shell .finea-action-btn--danger{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}.feb-admin-shell .finea-action-btn--danger:hover{background:#fecaca}
</style>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';
