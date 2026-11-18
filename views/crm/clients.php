<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\Form;
$e=static fn($x):string=>htmlspecialchars((string)$x,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<style>
.crm-wrap{max-width:1200px;margin:32px auto;padding:0 18px;color:#17233c}.crm-hero{background:linear-gradient(115deg,#192d59,#385b9b);color:white;padding:28px;border-radius:20px;margin-bottom:20px}.crm-hero h1{color:white;margin:0 0 8px}.crm-hero a{color:#ffdb62}.crm-grid{display:grid;grid-template-columns:1fr;gap:20px}.crm-form-layout{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 20px;align-items:start}.crm-form-layout .crm-full,.crm-form-layout>h3,.crm-form-layout>p{grid-column:1/-1}.crm-form-layout .crm-field,.crm-form-layout .finea-field{min-width:0;margin-bottom:0}.crm-form-layout .finea-select-search{width:100%}.crm-form-layout .finea-select-search-control{min-height:44px}.crm-form-layout .crm-submit{justify-self:start}.crm-wrap,.crm-wrap *{box-sizing:border-box}@media(max-width:700px){.crm-form-layout{grid-template-columns:1fr}}.crm-card{background:#fff;border:1px solid #e2e8f0;border-radius:17px;padding:24px;box-shadow:0 8px 26px #15233d0d}.crm-card h2{margin:0 0 14px;font-size:1.2rem}.crm-field{display:block;margin-bottom:13px;font-size:.85rem;font-weight:700;color:#334155}.crm-field input,.crm-field select,.crm-field textarea{display:block;width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:9px;background:white;color:#0f172a;font:inherit;margin-top:5px}.crm-submit{background:#1d3c84;color:white;border:0;border-radius:10px;padding:12px 20px;font-weight:800;cursor:pointer}.crm-table{width:100%;border-collapse:collapse;font-size:.87rem}.crm-table td,.crm-table th{padding:11px;border-bottom:1px solid #e2e8f0;text-align:left}.crm-table th{background:#f1f5f9}.crm-scroll{overflow-x:auto}.crm-alert{padding:12px;margin-bottom:15px;background:#fff1f2;color:#9f1239;border-radius:9px}.crm-success{background:#dcfce7;color:#166534}.crm-search{display:flex;gap:8px;margin-bottom:14px}.crm-search input{flex:1;min-width:0;padding:10px;border:1px solid #cbd5e1;border-radius:9px}@media(max-width:850px){.crm-grid{grid-template-columns:1fr}}
</style>
<div class="crm-wrap"><div class="crm-hero"><h1>Clients &amp; Prospects</h1><p>Référentiel CRM partagé avec les projets BTP.</p><a href="<?= $e(View::url('crm/dashboard')) ?>">Tableau de bord CRM</a> · <a href="<?= $e(View::url('crm/clients/nouveau')) ?>">+ Nouveau client</a></div>
<?php if($errorCrm):?><div class="crm-alert" role="alert"><?= $e($errorCrm) ?></div><?php endif;?>
<?php if($successCrm):?><div class="crm-alert crm-success"><?= $e($successCrm) ?></div><?php endif;?>
<div class="crm-grid">
<?php if($crmFormMode):?><section class="crm-card"><h2><?= $clientEdit?'Modifier la fiche':($crmNewMode?'Créer un client / prospect':'Ajouter un client / prospect') ?></h2>
<form class="crm-form-layout" method="post" action="<?= $e(View::url('crm/clients')) ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrfCrm) ?>">
<?php if($clientEdit):?><input type="hidden" name="id" value="<?= (int)$clientEdit['id'] ?>"><?php endif;?>
<?php foreach(['name'=>'Raison sociale / nom','contact_name'=>'Personne à contacter','email'=>'Adresse email','phone'=>'Téléphone','country'=>'Pays','city'=>'Ville','sector'=>"Secteur d'activité"] as $key=>$label):?>
<label class="crm-field"><?= $e($label) ?><input name="<?= $e($key) ?>" value="<?= $e($clientEdit[$key]??'') ?>" <?= $key==='name'?'required':'' ?> maxlength="<?= $key==='name'?180:($key==='email'?150:160) ?>"></label>
<?php endforeach;?>
<h3 class="crm-full">Identification légale</h3>
<p style="color:#64748b;font-size:.83rem">Pour les sociétés ivoiriennes avec NCC, la vérification DGI est obligatoire avant enregistrement. Pour les autres identifiants, la vérification reste en attente.</p>
<label class="crm-field">Type de client (nature juridique)<select id="crm-legal-form" name="legal_form" required>
<option value="personne_morale" <?= ($clientEdit['legal_form']??'personne_morale')==='personne_morale'?'selected':'' ?>>Personne morale (entreprise)</option>
<option value="personne_physique" <?= ($clientEdit['legal_form']??'')==='personne_physique'?'selected':'' ?>>Personne physique</option></select></label>
<?= Form::selectSearch('identity_country', 'Pays d'identification', [['value'=>'CI','label'=>'Côte d’Ivoire'],['value'=>'FR','label'=>'France'],['value'=>'DE','label'=>'Allemagne'],['value'=>'BE','label'=>'Belgique'],['value'=>'CH','label'=>'Suisse'],['value'=>'US','label'=>'États-Unis'],['value'=>'CA','label'=>'Canada'],['value'=>'SN','label'=>'Sénégal'],['value'=>'BF','label'=>'Burkina Faso'],['value'=>'ML','label'=>'Mali'],['value'=>'GH','label'=>'Ghana'],['value'=>'TG','label'=>'Togo'],['value'=>'BJ','label'=>'Bénin'],['value'=>'NG','label'=>'Nigeria'],['value'=>'MA','label'=>'Maroc'],['value'=>'TN','label'=>'Tunisie'],['value'=>'GB','label'=>'Royaume-Uni'],['value'=>'CN','label'=>'Chine'],['value'=>'IN','label'=>'Inde'],['value'=>'AE','label'=>'Émirats arabes unis']], $clientEdit['identity_country']??'CI', ['required'=>true,'placeholder'=>'Rechercher et sélectionner...']) ?>
<?= Form::selectSearch('identifier_kind', 'Type d’identifiant', [['value'=>'CNI','label'=>'Carte nationale d’identité'],['value'=>'NCC','label'=>'Numéro de compte contribuable'],['value'=>'RCCM','label'=>'Registre du commerce'],['value'=>'SIREN','label'=>'SIREN France'],['value'=>'SIRET','label'=>'SIRET France'],['value'=>'REGISTRE','label'=>'Registre national'],['value'=>'TVA','label'=>'TVA intracommunautaire'],['value'=>'LEI','label'=>'Identifiant international']], $clientEdit['identifier_kind']??'', ['required'=>true,'placeholder'=>'Rechercher et sélectionner...']) ?>
<label class="crm-field">Numéro de l'identifiant officiel<input name="identifier_value" required maxlength="100" value="<?= $e($clientEdit['identifier_value']??'') ?>"></label>
<label class="crm-field"><input type="checkbox" id="crm-cni-present" name="cni_present" value="1" style="width:auto;display:inline" <?= !empty($clientEdit['cni_present'])?'checked':'' ?>> CNI présentée (personne physique ivoirienne)</label>
<?php if($clientEdit):?><p><strong>Vérification :</strong> <?= $e($clientEdit['verification_status']??'pending') ?> — aucune validation externe automatique dans cette version.</p><?php endif;?>
<?= Form::selectSearch('type', 'Catégorie commerciale', [['value'=>'prospect','label'=>'Prospect'],['value'=>'client','label'=>'Client'],['value'=>'partner','label'=>'Partenaire']], $clientEdit['type']??'prospect', ['required'=>true,'placeholder'=>'Rechercher et sélectionner...']) ?>
<?= Form::selectSearch('status', 'Statut commercial', [['value'=>'new','label'=>'Nouveau'],['value'=>'active','label'=>'Actif'],['value'=>'dormant','label'=>'Inactif'],['value'=>'lost','label'=>'Perdu']], $clientEdit['status']??'new', ['required'=>true,'placeholder'=>'Rechercher et sélectionner...']) ?>
<label class="crm-field crm-full">Notes<textarea name="notes" rows="3"><?= $e($clientEdit['notes']??'') ?></textarea></label>
<button class="crm-submit crm-full" type="submit"><?= $clientEdit?'Enregistrer les modifications':'Créer la fiche' ?></button>
<?php if($clientEdit):?><a href="<?= $e(View::url('crm/clients')) ?>">Annuler</a><?php endif;?>
</form></section><?php else:?><section class="crm-card"><h2>Répertoire CRM (<?= count($clients) ?>)</h2>
<form class="crm-search" method="get"><input name="q" placeholder="Rechercher un client" value="<?= $e($_GET['q']??'') ?>"><button class="crm-submit" type="submit">Rechercher</button></form>
<div class="crm-scroll"><table class="crm-table"><thead><tr><th>Client</th><th>Type</th><th>Contact</th><th>Téléphone</th><th>Ville</th><th></th></tr></thead><tbody>
<?php foreach($clients as $client):?><tr><td><strong><?= $e($client['name']) ?></strong></td><td><?= $e($client['type']) ?></td><td><?= $e($client['contact_name']) ?></td><td><?= $e($client['phone']) ?></td><td><?= $e($client['city']) ?></td><td><a href="<?= $e(View::url('crm/clients/modifier')) ?>?id=<?= (int)$client['id'] ?>">Modifier</a></td></tr><?php endforeach;?>
<?php if(!$clients):?><tr><td colspan="6">Aucun client enregistré. Créez votre première fiche.</td></tr><?php endif;?></tbody></table></div></section><?php endif;?></div></div>
<script>
(function(){
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
