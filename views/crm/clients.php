<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\Form;
$e=static fn($x):string=>htmlspecialchars((string)$x,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<style>
.crm-wrap{max-width:1200px;margin:32px auto;padding:0 18px;color:#17233c}.crm-hero{background:linear-gradient(115deg,#192d59,#385b9b);color:white;padding:28px;border-radius:20px;margin-bottom:20px}.crm-hero h1{color:white;margin:0 0 8px}.crm-hero a{color:#ffdb62}.crm-grid{display:grid;grid-template-columns:1fr;gap:20px}.crm-tabbar{display:flex;gap:9px;flex-wrap:wrap;margin:0 0 22px}.crm-tab{border:1px solid #d8e3f4;background:#f8fbff;color:#314c78;padding:12px 16px;border-radius:12px;font-weight:750;cursor:pointer}.crm-tab[aria-selected=true]{background:#1d3c84;color:white;border-color:#1d3c84}.crm-panel[hidden]{display:none!important}.crm-form-layout{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 20px;align-items:start}.crm-form-layout .crm-full,.crm-form-layout>h3,.crm-form-layout>p{grid-column:1/-1}.crm-form-layout .crm-field,.crm-form-layout .finea-field{min-width:0;margin-bottom:0}.crm-form-layout .finea-select-search{width:100%}.crm-form-layout .finea-select-search-control{min-height:44px}.crm-form-layout .crm-submit{justify-self:start}.crm-wrap,.crm-wrap *{box-sizing:border-box}@media(max-width:700px){.crm-form-layout{grid-template-columns:1fr}}.crm-card{background:#fff;border:1px solid #e2e8f0;border-radius:17px;padding:24px;box-shadow:0 8px 26px #15233d0d}.crm-card h2{margin:0 0 14px;font-size:1.2rem}.crm-field{display:block;margin-bottom:13px;font-size:.85rem;font-weight:700;color:#334155}.crm-field input,.crm-field select,.crm-field textarea{display:block;width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:9px;background:white;color:#0f172a;font:inherit;margin-top:5px}.crm-submit{background:#1d3c84;color:white;border:0;border-radius:10px;padding:12px 20px;font-weight:800;cursor:pointer}.crm-table{width:100%;border-collapse:collapse;font-size:.87rem}.crm-table td,.crm-table th{padding:11px;border-bottom:1px solid #e2e8f0;text-align:left}.crm-table th{background:#f1f5f9}.crm-scroll{overflow-x:auto}.crm-alert{padding:12px;margin-bottom:15px;background:#fff1f2;color:#9f1239;border-radius:9px}.crm-success{background:#dcfce7;color:#166534}.crm-search{display:flex;gap:8px;margin-bottom:14px}.crm-search input{flex:1;min-width:0;padding:10px;border:1px solid #cbd5e1;border-radius:9px}@media(max-width:850px){.crm-grid{grid-template-columns:1fr}}
</style>
<div class="crm-wrap"><div class="crm-hero"><h1>Clients &amp; Prospects</h1><p>Référentiel CRM partagé avec les projets BTP.</p><a href="<?= $e(View::url('crm/dashboard')) ?>">Tableau de bord CRM</a> · <a href="<?= $e(View::url('crm/clients/nouveau')) ?>">+ Nouveau client</a></div>
<?php if($errorCrm):?><div class="crm-alert" role="alert"><?= $e($errorCrm) ?></div><?php endif;?>
<?php if($successCrm):?><div class="crm-alert crm-success"><?= $e($successCrm) ?></div><?php endif;?>
<div class="crm-grid">
<?php if($crmFormMode):?><section class="crm-card"><h2><?= $clientEdit?'Modifier la fiche':($crmNewMode?'Créer un client / prospect':'Ajouter un client / prospect') ?></h2>
<nav class="crm-tabbar" aria-label="Sections de la fiche">
<button class="crm-tab" type="button" data-crm-tab="identity" aria-selected="true"><i class="fa-solid fa-id-card"></i> Identité</button>
<button class="crm-tab" type="button" data-crm-tab="contact" aria-selected="false"><i class="fa-solid fa-address-book"></i> Coordonnées</button>
<button class="crm-tab" type="button" data-crm-tab="business" aria-selected="false"><i class="fa-solid fa-briefcase"></i> Classification</button>
</nav>
<form class="crm-form-layout" method="post" action="<?= $e(View::url('crm/clients')) ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrfCrm) ?>">
<?php if($clientEdit):?><input type="hidden" name="id" value="<?= (int)$clientEdit['id'] ?>"><?php endif;?>
<div class="crm-panel crm-full crm-form-layout" data-crm-panel="contact" hidden>
<?php foreach(['name'=>'Raison sociale / nom','contact_name'=>'Personne à contacter','email'=>'Adresse email','phone'=>'Téléphone','country'=>'Pays','city'=>'Ville','sector'=>"Secteur d'activité"] as $key=>$label):?>
<label class="crm-field"><?= $e($label) ?><input name="<?= $e($key) ?>" value="<?= $e($clientEdit[$key]??'') ?>" <?= $key==='name'?'required':'' ?> maxlength="<?= $key==='name'?180:($key==='email'?150:160) ?>"></label>
<?php endforeach;?>
</div>
<div class="crm-panel crm-full crm-form-layout" data-crm-panel="identity">
<h3 class="crm-full"><i class="fa-solid fa-shield-halved"></i> Identification légale</h3>
<p style="color:#64748b;font-size:.83rem">Pour les sociétés ivoiriennes avec NCC, la vérification DGI est obligatoire avant enregistrement. Pour les autres identifiants, la vérification reste en attente.</p>
<label class="crm-field">Type de client (nature juridique)<select id="crm-legal-form" name="legal_form" required>
<option value="personne_morale" <?= ($clientEdit['legal_form']??'personne_morale')==='personne_morale'?'selected':'' ?>>Personne morale (entreprise)</option>
<option value="personne_physique" <?= ($clientEdit['legal_form']??'')==='personne_physique'?'selected':'' ?>>Personne physique</option></select></label>
<?= Form::selectSearch('identity_country', 'Pays d'identification', array_map(static fn($row)=>['value'=>$row['option_code'],'label'=>$row['label']],$crmReferences['country']), $clientEdit['identity_country']??'CI', ['required'=>true,'placeholder'=>'Rechercher et sélectionner...']) ?>
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
<button class="crm-submit crm-full" type="submit"><i class="fa-solid fa-floppy-disk"></i> <?= $clientEdit?'Enregistrer les modifications':'Créer la fiche' ?></button>
<?php if($clientEdit):?><a href="<?= $e(View::url('crm/clients')) ?>">Annuler</a><?php endif;?>
</form></section><?php else:?><section class="crm-card"><h2>Répertoire CRM (<?= count($clients) ?>)</h2>
<form class="crm-search" method="get"><input name="q" placeholder="Rechercher un client" value="<?= $e($_GET['q']??'') ?>"><button class="crm-submit" type="submit">Rechercher</button></form>
<div class="crm-scroll"><table class="crm-table"><thead><tr><th>Client</th><th>Type</th><th>Contact</th><th>Téléphone</th><th>Ville</th><th></th></tr></thead><tbody>
<?php foreach($clients as $client):?><tr><td><strong><?= $e($client['name']) ?></strong></td><td><?= $e($client['type']) ?></td><td><?= $e($client['contact_name']) ?></td><td><?= $e($client['phone']) ?></td><td><?= $e($client['city']) ?></td><td><a href="<?= $e(View::url('crm/clients/modifier')) ?>?id=<?= (int)$client['id'] ?>">Modifier</a></td></tr><?php endforeach;?>
<?php if(!$clients):?><tr><td colspan="6">Aucun client enregistré. Créez votre première fiche.</td></tr><?php endif;?></tbody></table></div></section><?php endif;?></div></div>
<script>
(function(){
const tabs=[...document.querySelectorAll('[data-crm-tab]')],panels=[...document.querySelectorAll('[data-crm-panel]')];
function show(name){tabs.forEach(t=>t.setAttribute('aria-selected',String(t.dataset.crmTab===name)));panels.forEach(p=>p.hidden=p.dataset.crmPanel!==name);}
tabs.forEach(t=>t.addEventListener('click',()=>show(t.dataset.crmTab)));
const form=document.querySelector('.crm-form-layout[method=post]');if(form)form.addEventListener('invalid',e=>{const p=e.target.closest('[data-crm-panel]');if(p)show(p.dataset.crmPanel);},true);
const legal=document.getElementById('crm-legal-form'),country=document.querySelector('[name="identity_country"]'),kind=document.querySelector('[name="identifier_kind"]'),cni=document.getElementById('crm-cni-present');
if(!legal||!country||!kind)return;
function update(){
const physical=legal.value==='personne_physique',ci=country.value.trim().toUpperCase()==='CI';
kind.placeholder=physical?(ci?'CNI':'Identifiant personnel'):ci?'NCC ou RCCM':country.value.trim().toUpperCase()==='FR'?'SIREN ou SIRET':'Identifiant du registre officiel';
if(cni)cni.parentElement.style.display=physical&&ci?'block':'none';
if(physical&&ci)kind.value='CNI';
else if(!physical&&ci&&kind.value==='CNI')kind.value='NCC';
else if(!physical&&country.value.trim().toUpperCase()==='FR'&&kind.value==='CNI')kind.value='SIREN';
}
legal.addEventListener('change',update);country.addEventListener('change',update);update();
})();
</script>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';?>
