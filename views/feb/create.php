<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\Ui;
use App\View\Components\FormControls as Fc;
$e=static fn($v):string=>View::e((string)$v);
$chantierOptions=[];foreach($chantiers as $c)$chantierOptions[(string)$c['id']]=$c['code'].' · '.$c['nom'];
$workflowOptions=[];foreach($workflows as $w)$workflowOptions[(string)$w['id']]=$w['label'];
$lineOptions=[];foreach($lines as $l)$lineOptions[(string)$l['id']]=$l['description'].' · '.number_format((float)$l['total_cost'],0,',',' ').' FCFA';
ob_start();
?>
<?=Fc::assets()?>
<div class="finea-shell feb-employee-create"><div class="finea-container">
<?=Ui::pageHeader('Nouvelle fiche d’expression de besoin','Décrivez votre besoin et joignez les pièces nécessaires. La demande sera transmise au circuit de validation après soumission.',['eyebrow'=>'Espace employé · Mes demandes','actions'=>[Ui::button('Mes FEB',['href'=>'feb','variant'=>'secondary','icon'=>'arrow-left'])]])?>
<?php ob_start(); ?>
<form method="get" action="<?=$e(View::url('feb/nouvelle'))?>" class="feb-create-form">
<label for="feb-chantier-picker">Chantier concerné</label>
<div id="feb-chantier-picker"><?=Fc::searchable('chantier',$chantierOptions,['selected'=>$selectedChantier?[(string)$selectedChantier]:[],'placeholder'=>'Rechercher un chantier autorisé…'])?></div>
<div class="feb-form-actions"><?=Ui::button('Charger les déboursés',['type'=>'submit','variant'=>'primary','icon'=>'search'])?></div>
</form>
<?php if(!$chantiers): ?><?=Ui::emptyState('Aucun chantier accessible','Demandez à l’administrateur FEB de vous attribuer le rôle Demandeur sur un chantier disposant d’un budget validé.')?><?php endif; ?>
<?=Ui::section('1. Sélection du chantier',(string)ob_get_clean(),'Seuls les chantiers auxquels vous êtes affecté sont proposés.')?>
<?php if($selectedChantier): ?>
<form method="post" enctype="multipart/form-data" action="<?=$e(View::url('feb'))?>" class="feb-create-form">
<input type="hidden" name="_csrf_token" value="<?=$e($csrf)?>">
<input type="hidden" name="chantier_id" value="<?=(int)$selectedChantier?>">
<?php ob_start(); ?>
<div class="feb-create-grid">
<div><label>Circuit de validation</label><?=Fc::searchable('workflow_id',$workflowOptions,['selected'=>count($workflowOptions)===1?[array_key_first($workflowOptions)]:[],'placeholder'=>'Choisir un circuit…'])?></div>
<label>Type de bénéficiaire<select class="feb-field" name="beneficiary_type"><option value="fournisseur">Fournisseur</option><option value="beneficiaire">Autre bénéficiaire</option></select></label>
<label>Nom du fournisseur ou bénéficiaire<input class="feb-field" name="beneficiary_name" required maxlength="180" placeholder="Nom du bénéficiaire"></label>
<label>Date souhaitée<input class="feb-field" type="date" name="needed_at" required min="<?=date('Y-m-d')?>"></label>
<label>Détails du bénéficiaire<input class="feb-field" name="beneficiary_details" maxlength="500" placeholder="Référence, contact, informations utiles"></label>
<label>Justificatif (PDF, JPG, PNG ou DOCX — 10 Mo)<input class="feb-field" type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.docx" required></label>
</div>
<label>Motif de la demande<textarea class="feb-field" name="purpose" required rows="4" placeholder="Expliquez pourquoi cette dépense est nécessaire au chantier…"></textarea></label>
<?php echo Ui::section('2. Informations de la demande',(string)ob_get_clean(),'Précisez le bénéficiaire, la date et le motif.'); ?>
<?php ob_start(); ?>
<?php if(!$lines): ?><?=Ui::emptyState('Aucune ligne budgétaire','Le chantier ne possède pas de déboursés validés disponibles pour une FEB.')?><?php endif; ?>
<div class="feb-lines" id="feb-lines">
<?php for($n=0;$n<3;$n++): ?>
<fieldset class="feb-line-card"><legend>Besoin <?=$n+1?><?=$n?' · facultatif':''?></legend>
<div class="feb-create-grid">
<div><label>Déboursé budgétaire</label><?=Fc::searchable("lines[$n][budget_line_id]",$lineOptions,['placeholder'=>'Rechercher un déboursé…'])?></div>
<label>Description<input class="feb-field" name="lines[<?=$n?>][description]" <?=$n?'':'required'?> placeholder="Matériel, prestation, main-d’œuvre…"></label>
<label>Quantité<input class="feb-field feb-quantity" type="number" min="0.001" step="0.001" name="lines[<?=$n?>][quantity]" value="<?=$n?'':'1'?>"></label>
<label>Unité<input class="feb-field" name="lines[<?=$n?>][unit]" value="u"></label>
<label>Prix unitaire (FCFA)<input class="feb-field feb-price" type="number" min="0" step="0.01" name="lines[<?=$n?>][unit_amount]"></label>
<div class="feb-line-total"><span>Sous-total estimé</span><strong data-feb-line-total>0 FCFA</strong></div>
</div>
</fieldset>
<?php endfor; ?>
</div>
<div class="feb-grand-total"><span>Montant total estimé</span><strong id="feb-grand-total">0 FCFA</strong></div>
<?php echo Ui::section('3. Lignes de besoin',(string)ob_get_clean(),'Rattachez chaque dépense à une ligne du budget validé.'); ?>
<div class="feb-form-actions"><?=Ui::button('Enregistrer le brouillon',['type'=>'submit','variant'=>'primary','icon'=>'save'])?></div>
<p class="feb-form-help">L’enregistrement ne soumet pas encore la FEB : vous pourrez vérifier votre brouillon avant de l’envoyer aux validateurs.</p>
</form>
<?php endif; ?>
</div></div>
<style>
.feb-employee-create .finea-section-card{border:1px solid #dbe4f0;border-radius:17px;padding:20px;margin:18px 0;box-shadow:0 6px 22px #0f172a0a}.feb-create-form{display:grid;gap:18px}.feb-create-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}.feb-create-form label{display:grid;gap:7px;font-weight:600;color:#334155}.feb-field{display:block;width:100%;min-height:45px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:11px;background:#fff;color:#0f172a;font:inherit}.feb-field:focus{outline:0;border-color:#2563eb;box-shadow:0 0 0 3px #2563eb22}.feb-line-card{border:1px solid #dbe4f0;border-radius:13px;padding:16px;margin:14px 0}.feb-line-card legend{padding:0 8px;font-weight:700;color:#1d4ed8}.feb-form-actions{display:flex;justify-content:flex-end;gap:10px}.feb-line-total{display:flex;flex-direction:column;justify-content:center;gap:4px;color:#64748b}.feb-line-total strong{font-size:19px;color:#0f172a}.feb-grand-total{display:flex;justify-content:space-between;align-items:center;padding:17px;border-radius:12px;background:#eff6ff;color:#1e40af;font-weight:700}.feb-grand-total strong{font-size:22px}.feb-form-help{font-size:13px;color:#64748b;text-align:right}.feb-employee-create .finea-action-btn{display:inline-flex;justify-content:center;align-items:center}.feb-employee-create .finea-btn-content{display:inline-flex;align-items:center;justify-content:center;gap:8px}.feb-employee-create .finea-btn-icon{flex-shrink:0}@media(max-width:700px){.feb-create-grid{grid-template-columns:1fr}.feb-grand-total{gap:12px}.feb-employee-create .finea-section-card{padding:14px}}
</style>
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const cards=[...document.querySelectorAll('.feb-line-card')],total=document.getElementById('feb-grand-total');
 if(!total)return;
 const fmt=n=>new Intl.NumberFormat('fr-FR',{maximumFractionDigits:0}).format(n)+' FCFA';
 function update(){let sum=0;for(const card of cards){let q=Number(card.querySelector('.feb-quantity')?.value||0),p=Number(card.querySelector('.feb-price')?.value||0),amount=Math.max(0,q*p);sum+=amount;card.querySelector('[data-feb-line-total]').textContent=fmt(amount);}total.textContent=fmt(sum);}
 document.querySelectorAll('.feb-quantity,.feb-price').forEach(el=>el.addEventListener('input',update));update();
});
</script>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';
