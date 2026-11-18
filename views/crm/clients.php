<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\Form;
$e=static fn($x):string=>htmlspecialchars((string)$x,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<style>
.crm-wrap{max-width:1120px;margin:26px auto;padding:0 20px;color:#1e2b43}
.crm-wrap *{box-sizing:border-box}
.crm-hero{background:linear-gradient(112deg,#1c2e58,#355a9c);color:#fff;padding:26px 30px;border-radius:18px;margin-bottom:22px}
.crm-hero h1{color:#fff;margin:0 0 7px;font-size:1.55rem}
.crm-hero p{margin:0 0 10px;color:#e1e9fa}
.crm-hero a{color:#ffe18c;font-weight:650;text-decoration:none}
.crm-card{background:#fff;border:1px solid #e3eaf4;border-radius:18px;padding:32px;box-shadow:0 12px 38px rgba(23,43,83,.055)}
.crm-card h2{font-size:1.24rem;margin:0 0 27px;color:#15284d}
.crm-tabbar{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:0;position:relative;margin:0 0 34px;padding:12px 8px 18px;border-bottom:1px solid #e7edf5}
.crm-tabbar:before{content:'';position:absolute;top:34px;left:16.67%;right:16.67%;height:3px;background:#e0e8f3;border-radius:6px}
.crm-tab{position:relative;display:flex;align-items:center;flex-direction:column;gap:6px;text-align:center;min-width:0;color:#72809a;padding:0 5px;background:transparent!important;border:0!important;border-radius:0!important;cursor:default!important}
.crm-tab:after{display:none!important}
.crm-step-dot{width:46px;height:46px;display:grid;place-items:center;border-radius:50%;background:#f3f6fb;border:2px solid #d9e3f1;color:#7c8ba4;position:relative;z-index:1;font-size:18px;box-shadow:0 0 0 5px #fff;transition:background .2s,border-color .2s,color .2s}
.crm-tab[aria-current="step"] .crm-step-dot{background:#254b95;border-color:#254b95;color:#fff;box-shadow:0 0 0 5px #e7efff}
.crm-tab.is-complete .crm-step-dot{background:#0d9677;border-color:#0d9677;color:#fff;box-shadow:0 0 0 5px #e8f7f2}
.crm-tab-title{display:block;font-size:.9rem;font-weight:800;color:#63748f}
.crm-tab[aria-current="step"] .crm-tab-title{color:#234b94}
.crm-tab.is-complete .crm-tab-title{color:#0b8c6f}
.crm-step-hint{display:block;font-size:.73rem;color:#8a97ad;line-height:1.3}
.crm-form-layout{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 24px;align-items:start}
.crm-form-layout .crm-full,.crm-form-layout>h3,.crm-form-layout>p{grid-column:1/-1}
.crm-panel[hidden]{display:none!important}
.crm-panel{animation:crm-enter .22s ease-out}
@keyframes crm-enter{from{opacity:.4;transform:translateY(6px)}to{opacity:1;transform:none}}
.crm-panel h3{margin:0;color:#1c3155;font-size:1.04rem;display:flex;gap:10px;align-items:center}
.crm-panel h3 i{color:#315aa6}
.crm-panel>p{font-size:.82rem;color:#697c98;margin:-6px 0 2px;line-height:1.55}
.crm-field,.crm-form-layout .finea-field{display:block;min-width:0;margin:0;font-size:.83rem;font-weight:700;color:#304361}
.crm-field input:not([type=checkbox]),.crm-field select,.crm-field textarea{display:block;width:100%;min-height:45px;margin-top:8px;padding:11px 13px;border:1px solid #d1dceb;border-radius:10px;background:#fff;color:#1a2b46;font:inherit;outline:none;transition:border-color .15s,box-shadow .15s}
.crm-field input:focus,.crm-field select:focus,.crm-field textarea:focus{border-color:#4169b3;box-shadow:0 0 0 3px #e7efff}
.crm-form-layout .finea-select-search{width:100%}
.crm-form-layout .finea-select-control{min-height:45px;border-radius:10px}
.crm-form-layout .finea-field{min-width:0}
.crm-submit{display:inline-flex;align-items:center;justify-content:center;gap:9px;background:#254b95;color:#fff;border:0;border-radius:11px;padding:12px 21px;font-weight:750;cursor:pointer;min-height:45px}
.crm-submit:hover{background:#1a3979}
.crm-submit:disabled{opacity:.6;cursor:wait}
.crm-secondary{display:inline-flex;align-items:center;gap:9px;background:#fff;color:#31548f;border:1px solid #cbd8e9;border-radius:11px;padding:11px 18px;font-weight:750;cursor:pointer;min-height:45px}
.crm-wizard-actions{grid-column:1/-1;display:flex;align-items:center;justify-content:flex-end;gap:14px;margin-top:12px;padding-top:22px;border-top:1px solid #e8eef6}
.crm-wizard-actions [hidden]{display:none!important}
.crm-wizard-actions [data-wizard-prev]{margin-right:auto}
.crm-wizard-actions [data-wizard-counter]{font-size:.82rem;color:#7889a4;margin-right:auto}
.crm-wizard-actions [data-wizard-prev]:not([hidden])+[data-wizard-counter]{margin-right:0}
.crm-wizard-error{grid-column:1/-1;color:#a62c44;background:#fff0f3;border-radius:10px;padding:12px 14px;display:none}
.crm-wizard-error:not(:empty){display:block}
.crm-table{width:100%;border-collapse:collapse;font-size:.87rem}
.crm-table td,.crm-table th{padding:12px;border-bottom:1px solid #e2e8f0;text-align:left}
.crm-table th{background:#f1f5f9}
.crm-scroll{overflow-x:auto}
.crm-alert{padding:12px;margin-bottom:15px;background:#fff1f2;color:#9f1239;border-radius:9px}
.crm-success{background:#dcfce7;color:#166534}
.crm-search{display:flex;gap:8px;margin-bottom:14px}
.crm-search input{flex:1;min-width:0;padding:10px;border:1px solid #cbd5e1;border-radius:9px}
@media(max-width:700px){.crm-wrap{padding:0 12px;margin:14px auto}.crm-card{padding:20px 15px}.crm-form-layout{grid-template-columns:1fr;gap:17px}.crm-tabbar{margin-bottom:26px;padding-left:0;padding-right:0}.crm-step-dot{width:39px;height:39px;font-size:15px}.crm-tabbar:before{top:31px}.crm-tab-title{font-size:.78rem}.crm-step-hint{display:none}.crm-wizard-actions{flex-wrap:wrap}.crm-wizard-actions [data-wizard-counter]{font-size:.74rem}}
.crm-step-check{font-size:23px;font-weight:900;line-height:1}.crm-step-check[hidden]{display:none!important}.crm-step-dot svg{display:block;flex-shrink:0}</style>
<div class="crm-wrap"><div class="crm-hero"><h1>Clients &amp; Prospects</h1><p>Référentiel CRM partagé avec les projets BTP.</p><a href="<?= $e(View::url('crm/dashboard')) ?>">Tableau de bord CRM</a> · <a href="<?= $e(View::url('crm/clients/nouveau')) ?>">+ Nouveau client</a></div>
<?php if($errorCrm):?><div class="crm-alert" role="alert"><?= $e($errorCrm) ?></div><?php endif;?>
<?php if($successCrm):?><div class="crm-alert crm-success"><?= $e($successCrm) ?></div><?php endif;?>
<div class="crm-grid">
<?php if($crmFormMode):?><section class="crm-card"><h2><?= $clientEdit?'Modifier la fiche':($crmNewMode?'Créer un client / prospect':'Ajouter un client / prospect') ?></h2>
<nav class="crm-tabbar" aria-label="Progression du formulaire">
<div class="crm-tab" data-crm-tab="identity" aria-current="step"><span class="crm-step-dot"><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="4.5" width="19" height="15" rx="2"/><circle cx="8" cy="10" r="2"/><path d="M4.5 16c.6-2.2 6.4-2.2 7 0M14 9h5M14 13h5M14 16h3"/></svg></span><span class="crm-tab-title">1. Identité</span><small class="crm-step-hint">Identification officielle</small></div>
<div class="crm-tab" data-crm-tab="contact"><span class="crm-step-dot"><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="16" height="18" rx="2"/><path d="M2 7h5M2 12h5M2 17h5"/><circle cx="13" cy="9" r="2"/><path d="M9.5 17c0-3.2 7-3.2 7 0"/></svg></span><span class="crm-tab-title">2. Coordonnées</span><small class="crm-step-hint">Contact et localisation</small></div>
<div class="crm-tab" data-crm-tab="business"><span class="crm-step-dot"><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13c5 3 13 3 18 0M11 14h2"/></svg></span><span class="crm-tab-title">3. Classification</span><small class="crm-step-hint">Profil commercial</small></div>
</nav>
<form class="crm-form-layout" data-finea-wizard="1" novalidate method="post" action="<?= $e(View::url('crm/clients')) ?>">
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
<div class="crm-full" style="background:#fff8e8;border:1px solid #f0dca4;border-radius:12px;padding:16px">
<strong><i class="fa-solid fa-triangle-exclamation"></i> Enregistrement avec dérogation</strong>
<p style="font-size:.84rem;color:#715b32;margin:8px 0">En cas de NCC erroné, de cessation d'activité ou d'indisponibilité DGI, le client peut être enregistré sans être déclaré vérifié. Cette décision est tracée.</p>
<label class="crm-field"><input type="checkbox" name="verification_override" value="1" id="crm-verification-override" <?= !empty($clientEdit['verification_override_reason'])?'checked':'' ?>> Autoriser l'enregistrement malgré les contrôles</label>
<label class="crm-field" id="crm-override-reason-wrap" hidden>Motif de la dérogation<textarea name="verification_override_reason" maxlength="500" rows="2" id="crm-override-reason"><?= $e($clientEdit['verification_override_reason']??'') ?></textarea></label>
</div>
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
 const override=document.getElementById('crm-verification-override');
 const reason=document.getElementById('crm-override-reason');
 const reasonWrap=document.getElementById('crm-override-reason-wrap');
 if(override&&reason&&reasonWrap){const sync=()=>{reasonWrap.hidden=!override.checked;reason.required=override.checked;};override.addEventListener('change',sync);sync();}
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
