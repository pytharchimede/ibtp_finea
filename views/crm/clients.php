<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\Form;
$e=static fn($x):string=>htmlspecialchars((string)$x,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<style>
.crm-wrap{max-width:1200px;margin:32px auto;padding:0 18px;color:#17233c}.crm-hero{background:linear-gradient(115deg,#192d59,#385b9b);color:white;padding:28px;border-radius:20px;margin-bottom:20px}.crm-hero h1{color:white;margin:0 0 8px}.crm-hero a{color:#ffdb62}.crm-grid{display:grid;grid-template-columns:1fr;gap:20px}.crm-tabbar{display:flex;gap:0;margin:0 0 26px;align-items:stretch;counter-reset:step}.crm-tab{flex:1;position:relative;min-width:0}.crm-tab:not(:last-child):after{content:'';position:absolute;left:calc(50% + 26px);right:calc(-50% + 26px);top:21px;height:3px;background:#dce6f4;z-index:0}.crm-tab.is-complete:not(:last-child):after{background:#16a085}.crm-step-dot{display:grid;place-items:center;margin:0 auto 8px;width:43px;height:43px;border:2px solid #dce6f4;border-radius:50%;background:#f8fbff;color:#60769c;position:relative;z-index:1;font-size:16px}.crm-tab[aria-current=step] .crm-step-dot{background:#244c98;border-color:#244c98;color:white;box-shadow:0 0 0 5px #e6edff}.crm-tab.is-complete .crm-step-dot{background:#0c9678;border-color:#0c9678;color:white}.crm-tab-title{display:block;font-size:.88rem}.crm-tab.is-complete .crm-tab-title{color:#0c9678}.crm-step-hint{display:block;color:#71819c;font-size:.73rem;margin-top:4px}.crm-wizard-actions{display:flex;justify-content:space-between;align-items:center;gap:12px;grid-column:1/-1;border-top:1px solid #e6ebf3;padding-top:20px;margin-top:6px}.crm-wizard-actions button{min-height:44px}.crm-secondary{background:#fff;color:#244c98;border:1px solid #c7d4e8;border-radius:10px;padding:11px 18px;font-weight:700;cursor:pointer}.crm-wizard-error{grid-column:1/-1;color:#ad2440;background:#fff1f2;border-radius:10px;padding:12px 14px;display:none}.crm-wizard-error:not(:empty){display:block}@media(max-width:600px){.crm-step-hint{display:none}.crm-tab-title{font-size:.74rem}.crm-tab:not(:last-child):after{left:calc(50% + 23px);right:calc(-50% + 23px)}}.crm-tab{border:1px solid #d8e3f4;background:#f8fbff;color:#314c78;padding:12px 16px;border-radius:12px;font-weight:750;cursor:pointer}.crm-tab[aria-selected=true]{background:#1d3c84;color:white;border-color:#1d3c84}.crm-panel[hidden]{display:none!important}.crm-form-layout{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 20px;align-items:start}.crm-form-layout .crm-full,.crm-form-layout>h3,.crm-form-layout>p{grid-column:1/-1}.crm-form-layout .crm-field,.crm-form-layout .finea-field{min-width:0;margin-bottom:0}.crm-form-layout .finea-select-search{width:100%}.crm-form-layout .finea-select-search-control{min-height:44px}.crm-form-layout .crm-submit{justify-self:start}.crm-wrap,.crm-wrap *{box-sizing:border-box}@media(max-width:700px){.crm-form-layout{grid-template-columns:1fr}}.crm-card{background:#fff;border:1px solid #e2e8f0;border-radius:17px;padding:24px;box-shadow:0 8px 26px #15233d0d}.crm-card h2{margin:0 0 14px;font-size:1.2rem}.crm-field{display:block;margin-bottom:13px;font-size:.85rem;font-weight:700;color:#334155}.crm-field input,.crm-field select,.crm-field textarea{display:block;width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:9px;background:white;color:#0f172a;font:inherit;margin-top:5px}.crm-submit{background:#1d3c84;color:white;border:0;border-radius:10px;padding:12px 20px;font-weight:800;cursor:pointer}.crm-table{width:100%;border-collapse:collapse;font-size:.87rem}.crm-table td,.crm-table th{padding:11px;border-bottom:1px solid #e2e8f0;text-align:left}.crm-table th{background:#f1f5f9}.crm-scroll{overflow-x:auto}.crm-alert{padding:12px;margin-bottom:15px;background:#fff1f2;color:#9f1239;border-radius:9px}.crm-success{background:#dcfce7;color:#166534}.crm-search{display:flex;gap:8px;margin-bottom:14px}.crm-search input{flex:1;min-width:0;padding:10px;border:1px solid #cbd5e1;border-radius:9px}@media(max-width:850px){.crm-grid{grid-template-columns:1fr}}
</style>
<div class="crm-wrap"><div class="crm-hero"><h1>Clients &amp; Prospects</h1><p>Référentiel CRM partagé avec les projets BTP.</p><a href="<?= $e(View::url('crm/dashboard')) ?>">Tableau de bord CRM</a> · <a href="<?= $e(View::url('crm/clients/nouveau')) ?>">+ Nouveau client</a></div>
<?php if($errorCrm):?><div class="crm-alert" role="alert"><?= $e($errorCrm) ?></div><?php endif;?>
<?php if($successCrm):?><div class="crm-alert crm-success"><?= $e($successCrm) ?></div><?php endif;?>
<div class="crm-grid">
<?php if($crmFormMode):?><section class="crm-card"><h2><?= $clientEdit?'Modifier la fiche':($crmNewMode?'Créer un client / prospect':'Ajouter un client / prospect') ?></h2>
<nav class="crm-tabbar" aria-label="Progression du formulaire">
<div class="crm-tab" data-crm-tab="identity" aria-current="step"><span class="crm-step-dot"><i class="fa-solid fa-id-card"></i></span><span class="crm-tab-title">1. Identité</span><small class="crm-step-hint">Identification officielle</small></div>
<div class="crm-tab" data-crm-tab="contact"><span class="crm-step-dot"><i class="fa-solid fa-address-book"></i></span><span class="crm-tab-title">2. Coordonnées</span><small class="crm-step-hint">Contact et localisation</small></div>
<div class="crm-tab" data-crm-tab="business"><span class="crm-step-dot"><i class="fa-solid fa-briefcase"></i></span><span class="crm-tab-title">3. Classification</span><small class="crm-step-hint">Profil commercial</small></div>
</nav>
<form class="crm-form-layout" data-finea-wizard="1" method="post" action="<?= $e(View::url('crm/clients')) ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrfCrm) ?>">
<?php if($clientEdit):?><input type="hidden" name="id" value="<?= (int)$clientEdit['id'] ?>"><?php endif;?>
<div class="crm-panel crm-full crm-form-layout" data-crm-panel="contact" hidden>
<?php foreach(['contact_name'=>'Personne à contacter','email'=>'Adresse email','phone'=>'Téléphone','country'=>'Pays','city'=>'Ville','sector'=>"Secteur d'activité"] as $key=>$label):?>
<label class="crm-field"><?= $e($label) ?><input name="<?= $e($key) ?>" value="<?= $e($clientEdit[$key]??'') ?>" <?= $key==='name'?'required':'' ?> maxlength="<?= $key==='name'?180:($key==='email'?150:160) ?>"></label>
<?php endforeach;?>
</div>
<div class="crm-panel crm-full crm-form-layout" data-crm-panel="identity">
<h3 class="crm-full"><i class="fa-solid fa-shield-halved"></i> Identification légale</h3>
<label class="crm-field crm-full" data-crm-name-field><?= $e(($clientEdit['legal_form']??'personne_morale')==='personne_physique'?'Nom et prénoms':'Raison sociale') ?><input name="name" required maxlength="180" value="<?= $e($clientEdit['name']??'') ?>"></label>
<p style="color:#64748b;font-size:.83rem">Pour les sociétés ivoiriennes avec NCC, la vérification DGI est obligatoire avant enregistrement. Pour les autres identifiants, la vérification reste en attente.</p>
<label class="crm-field">Type de client (nature juridique)<select id="crm-legal-form" name="legal_form" required>
<option value="personne_morale" <?= ($clientEdit['legal_form']??'personne_morale')==='personne_morale'?'selected':'' ?>>Personne morale (entreprise)</option>
<option value="personne_physique" <?= ($clientEdit['legal_form']??'')==='personne_physique'?'selected':'' ?>>Personne physique</option></select></label>
<?= Form::selectSearch('identity_country', "Pays d'identification", array_map(static fn($row)=>['value'=>$row['option_code'],'label'=>$row['label']],$crmReferences['country']), $clientEdit['identity_country']??'CI', ['required'=>true,'placeholder'=>'Rechercher et sélectionner...']) ?>
<?= Form::selectSearch('identifier_kind', 'Type d’identifiant', array_map(static fn($row)=>['value'=>$row['option_code'],'label'=>$row['label']],$crmReferences['identifier']), $clientEdit['identifier_kind']??'', ['required'=>true,'placeholder'=>'Rechercher et sélectionner...']) ?>
<label class="crm-field">Numéro de l'identifiant officiel<input name="identifier_value" required maxlength="100" value="<?= $e($clientEdit['identifier_value']??'') ?>"></label>
<label class="crm-field"><input type="checkbox" id="crm-cni-present" name="cni_present" value="1" style="width:auto;display:inline" <?= !empty($clientEdit['cni_present'])?'checked':'' ?>> CNI présentée (personne physique ivoirienne)</label>
<?php if($clientEdit):?><p><strong>Vérification :</strong> <?= $e($clientEdit['verification_status']??'pending') ?> — aucune validation externe automatique dans cette version.</p><?php endif;?>
</div>
<div class="crm-panel crm-full crm-form-layout" data-crm-panel="business">
<h3 class="crm-full"><i class="fa-solid fa-chart-line"></i> Profil commercial</h3>
<?= Form::selectSearch('type', 'Catégorie commerciale', array_map(static fn($row)=>['value'=>$row['option_code'],'label'=>$row['label']],$crmReferences['category']), $clientEdit['type']??'prospect', ['required'=>true,'placeholder'=>'Rechercher et sélectionner...']) ?>
<?= Form::selectSearch('status', 'Statut commercial', array_map(static fn($row)=>['value'=>$row['option_code'],'label'=>$row['label']],$crmReferences['status']), $clientEdit['status']??'new', ['required'=>true,'placeholder'=>'Rechercher et sélectionner...']) ?>
<label class="crm-field crm-full">Notes<textarea name="notes" rows="3"><?= $e($clientEdit['notes']??'') ?></textarea></label>
</div>
<div class="crm-wizard-error" role="alert" aria-live="polite" data-wizard-error></div>
<div class="crm-wizard-actions">
<button class="crm-secondary" type="button" data-wizard-prev hidden><i class="fa-solid fa-arrow-left"></i> Précédent</button>
<span data-wizard-counter aria-live="polite">Étape 1 sur 3</span>
<button class="crm-submit" type="button" data-wizard-next>Suivant <i class="fa-solid fa-arrow-right"></i></button>
<button class="crm-submit" type="submit" data-wizard-submit hidden><i class="fa-solid fa-floppy-disk"></i> <?= $clientEdit?'Enregistrer les modifications':'Créer la fiche' ?></button>
</div>
<?php if($clientEdit):?><a href="<?= $e(View::url('crm/clients')) ?>">Annuler</a><?php endif;?>
</form></section><?php else:?><section class="crm-card"><h2>Répertoire CRM (<?= count($clients) ?>)</h2>
<form class="crm-search" method="get"><input name="q" placeholder="Rechercher un client" value="<?= $e($_GET['q']??'') ?>"><button class="crm-submit" type="submit">Rechercher</button></form>
<div class="crm-scroll"><table class="crm-table"><thead><tr><th>Client</th><th>Type</th><th>Contact</th><th>Téléphone</th><th>Ville</th><th></th></tr></thead><tbody>
<?php foreach($clients as $client):?><tr><td><strong><?= $e($client['name']) ?></strong></td><td><?= $e($client['type']) ?></td><td><?= $e($client['contact_name']) ?></td><td><?= $e($client['phone']) ?></td><td><?= $e($client['city']) ?></td><td><a href="<?= $e(View::url('crm/clients/modifier')) ?>?id=<?= (int)$client['id'] ?>">Modifier</a></td></tr><?php endforeach;?>
<?php if(!$clients):?><tr><td colspan="6">Aucun client enregistré. Créez votre première fiche.</td></tr><?php endif;?></tbody></table></div></section><?php endif;?></div></div>
<script src="<?= $e(View::asset('js/components/form-wizard.js')) ?>"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const legal=document.getElementById('crm-legal-form');
 const country=document.querySelector('[name="identity_country"]');
 const kind=document.querySelector('[name="identifier_kind"]');
 const cni=document.getElementById('crm-cni-present');
 const nameField=document.querySelector('[data-crm-name-field]');
 if(!legal||!country||!kind)return;
 const update=function(){
  const physical=legal.value==='personne_physique',ci=country.value==='CI';
  if(cni)cni.closest('label').style.display=physical&&ci?'block':'none';
  if(nameField)nameField.firstChild.textContent=physical?'Nom et prénoms':'Raison sociale';
  if(physical&&ci&&kind.value!=='CNI'){kind.value='CNI';kind.dispatchEvent(new Event('change',{bubbles:true}));}
  if(!physical&&ci&&kind.value==='CNI'){kind.value='NCC';kind.dispatchEvent(new Event('change',{bubbles:true}));}
 };
 legal.addEventListener('change',update);country.addEventListener('change',update);update();
});
</script>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';?>
