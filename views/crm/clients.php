<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\Form;
$e=static fn($x):string=>htmlspecialchars((string)$x,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$crmWizardSteps=[['id'=>'identity','label'=>'Identité','hint'=>'Identification officielle'],['id'=>'contact','label'=>'Coordonnées','hint'=>'Contact et localisation'],['id'=>'business','label'=>'Classification','hint'=>'Profil commercial']];
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
.crm-step-check{font-size:23px;font-weight:900;line-height:1}.crm-step-check[hidden]{display:none!important}.crm-step-dot svg{display:block;flex-shrink:0}.crm-client-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.crm-client-card{min-width:0;display:flex;flex-direction:column;border:1px solid #dfe7f2;border-radius:16px;background:#fff;box-shadow:0 5px 18px rgba(28,48,84,.045);overflow:hidden;transition:transform .18s,box-shadow .18s}
.crm-client-card:hover{transform:translateY(-2px);box-shadow:0 12px 26px rgba(28,48,84,.1)}
.crm-client-head{display:flex;align-items:flex-start;gap:13px;padding:19px 19px 13px}
.crm-client-avatar{flex:0 0 43px;width:43px;height:43px;border-radius:12px;display:grid;place-items:center;background:#eaf0ff;color:#254b95;font-size:1.18rem;font-weight:850}
.crm-client-title{min-width:0;flex:1}.crm-client-title h3{margin:0 0 8px;color:#1c3155;font-size:1.02rem;line-height:1.35;overflow-wrap:anywhere}
.crm-client-type{display:inline-flex;padding:4px 10px;border-radius:999px;background:#eff5ff;color:#315b9b;font-size:.74rem;font-weight:750}
.crm-client-details{margin:0;padding:4px 19px 15px;display:grid;gap:12px}
.crm-client-details>div{display:grid;grid-template-columns:95px minmax(0,1fr);gap:9px;align-items:start}
.crm-client-details dt{color:#74839a;font-size:.8rem}.crm-client-details dd{margin:0;min-width:0;color:#263954;font-size:.86rem;font-weight:650;overflow-wrap:anywhere}
.crm-client-details a{color:#24529b;text-decoration:none}
.crm-client-footer{margin-top:auto;padding:13px 19px;border-top:1px solid #e9eef6;background:#fbfcff;display:flex;justify-content:flex-end}
.crm-client-edit{display:inline-flex;align-items:center;gap:9px;color:#244e94;font-weight:800;font-size:.84rem;text-decoration:none;min-height:32px}
.crm-client-edit:hover{text-decoration:underline}
.crm-client-empty{grid-column:1/-1;padding:36px 20px;text-align:center;border:1px dashed #cbd8e9;border-radius:14px;color:#334766}
.crm-client-empty p{color:#73839b}.crm-client-empty a{color:#254b95;font-weight:750}
@media(max-width:760px){.crm-client-grid{grid-template-columns:1fr}.crm-search{flex-wrap:wrap}.crm-search input{flex-basis:100%}.crm-search button{width:100%}}
@media(max-width:420px){.crm-client-head{padding:16px 15px 11px}.crm-client-details{padding:4px 15px 14px}.crm-client-details>div{grid-template-columns:85px minmax(0,1fr)}.crm-client-footer{padding:11px 15px}}
.crm-search-advanced{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.crm-search-advanced input,.crm-search-advanced select{width:100%;min-width:0;min-height:44px;border:1px solid #cbd5e1;border-radius:9px;padding:10px;background:white}.crm-search-advanced a{justify-content:center;text-decoration:none}.crm-client-badges{display:flex;gap:6px;flex-wrap:wrap}.crm-client-badge{display:inline-block;padding:4px 8px;border-radius:8px;font-size:.72rem;font-weight:750}.crm-client-badge.is-good{background:#dcfce7;color:#166534}.crm-client-badge.is-warning{background:#fff4db;color:#925c13}.crm-client-quick{position:relative}.crm-client-quick summary{cursor:pointer;color:#244e94;font-weight:700;padding:8px}.crm-client-quick-menu{position:absolute;z-index:3;right:0;bottom:100%;min-width:210px;padding:12px;border:1px solid #dbe5f2;border-radius:12px;background:#fff;box-shadow:0 12px 28px #1e2b4322;display:grid;gap:10px}.crm-client-quick-menu form{display:grid;gap:8px}.crm-client-quick-menu select{width:100%;padding:8px}.crm-client-quick-menu button{border:0;background:#254b95;color:#fff;border-radius:8px;padding:9px;cursor:pointer}@media(max-width:760px){.crm-search-advanced{grid-template-columns:1fr}.crm-search-advanced input{flex-basis:auto}.crm-client-footer{overflow:visible}}
.crm-client-card{overflow:hidden}.crm-client-head{padding:20px 20px 15px}.crm-client-title h3{font-size:1.08rem}.crm-client-details{padding:8px 20px 20px}.crm-client-footer{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;padding:14px 16px;background:#f8faff}.crm-client-action{display:flex;align-items:center;justify-content:center;text-align:center;min-height:42px;padding:8px 5px;border:1px solid #ccd9ee;border-radius:9px;text-decoration:none;color:#264b8d;font-weight:750;font-size:.78rem}.crm-client-action.primary{background:#254b95;border-color:#254b95;color:#fff}.crm-client-action:hover{filter:brightness(.94)}.crm-client-qualification{padding:12px 17px 17px;display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:8px;align-items:center}.crm-client-qualification label{font-size:.75rem;color:#65758f;font-weight:750}.crm-client-qualification select{min-width:0;min-height:40px;padding:7px;border:1px solid #ccd9e9;border-radius:8px;background:#fff}.crm-client-qualification button{min-height:40px;border:0;border-radius:8px;padding:8px 11px;background:#e7efff;color:#244c91;font-weight:750;cursor:pointer}@media(max-width:420px){.crm-client-footer{grid-template-columns:1fr 1fr}.crm-client-action.primary{grid-column:1/-1}.crm-client-qualification{grid-template-columns:1fr 1fr}.crm-client-qualification label{grid-column:1/-1}.crm-client-qualification button{padding:7px}}
</style>
<div class="crm-wrap"><div class="crm-hero"><h1>Clients &amp; Prospects</h1><p>Référentiel CRM partagé avec les projets BTP.</p><a href="<?= $e(View::url('crm/dashboard')) ?>">Tableau de bord CRM</a> · <a href="<?= $e(View::url('crm/clients/nouveau')) ?>">+ Nouveau client</a></div>
<?php if($errorCrm):?><div class="crm-alert" role="alert"><?= $e($errorCrm) ?></div><?php endif;?>
<?php if($successCrm):?><div class="crm-alert crm-success"><?= $e($successCrm) ?></div><?php endif;?>
<div class="crm-grid">
<?php if($crmFormMode):?><section class="crm-card"><h2><?= $clientEdit?'Modifier la fiche':($crmNewMode?'Créer un client / prospect':'Ajouter un client / prospect') ?></h2>
<?= \App\View\Components\FormWizard::progress($crmWizardSteps) ?>

<?= \App\View\Components\FormWizard::formOpen(View::url('crm/clients'), $crmWizardSteps) ?>
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
<?= \App\View\Components\FormWizard::controls($clientEdit ? 'Enregistrer les modifications' : 'Créer la fiche') ?>
<?php if($clientEdit):?><a href="<?= $e(View::url('crm/clients')) ?>">Annuler</a><?php endif;?>
</form></section><?php else:?><section class="crm-card"><h2>Répertoire CRM (<?= count($clients) ?>)</h2>
<form class="crm-search crm-search-advanced" method="get">
<input name="q" placeholder="Nom, contact, email, téléphone, NCC..." value="<?= $e($crmFilters['q']??'') ?>">
<input name="city" placeholder="Ville" value="<?= $e($crmFilters['city']??'') ?>">
<select name="type"><option value="">Toutes catégories</option><?php foreach(['prospect'=>'Prospect','client'=>'Client','partner'=>'Partenaire'] as $key=>$label):?><option value="<?= $e($key) ?>" <?= ($crmFilters['type']??'')===$key?'selected':'' ?>><?= $e($label) ?></option><?php endforeach;?></select>
<select name="status"><option value="">Tous statuts</option><?php foreach(['new'=>'Nouveau','active'=>'Actif','dormant'=>'Dormant','lost'=>'Perdu'] as $key=>$label):?><option value="<?= $e($key) ?>" <?= ($crmFilters['status']??'')===$key?'selected':'' ?>><?= $e($label) ?></option><?php endforeach;?></select>
<select name="verification_status"><option value="">Toutes vérifications</option><?php foreach(['verified'=>'Vérifié','pending'=>'En attente','rejected'=>'Non vérifié'] as $key=>$label):?><option value="<?= $e($key) ?>" <?= ($crmFilters['verification_status']??'')===$key?'selected':'' ?>><?= $e($label) ?></option><?php endforeach;?></select>
<button class="crm-submit" type="submit">Rechercher</button><a class="crm-secondary" href="<?= $e(View::url('crm/clients')) ?>">Effacer</a>
<a class="crm-secondary" href="<?= $e(View::url('crm/clients/export')) ?>?<?= $e(http_build_query($crmFilters)) ?>">Exporter les résultats CSV</a>
</form>
<div class="crm-client-grid">
<?php foreach($clients as $client):?>
<?= \App\View\Components\CrmClientCard::render($client, $csrfCrm) ?>
<?php endforeach;?>
<?php if(!$clients):?><div class="crm-client-empty"><strong>Aucun client trouvé</strong><p>Aucun résultat pour cette recherche. Vous pouvez créer une nouvelle fiche client.</p><a href="<?= $e(View::url('crm/clients/nouveau')) ?>">Créer un client</a></div><?php endif;?>
</div></section><?php endif;?></div></div>
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
