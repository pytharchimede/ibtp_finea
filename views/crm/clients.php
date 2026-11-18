<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn($x):string=>htmlspecialchars((string)$x,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<style>
.crm-wrap{max-width:1200px;margin:32px auto;padding:0 18px;color:#17233c}.crm-hero{background:linear-gradient(115deg,#192d59,#385b9b);color:white;padding:28px;border-radius:20px;margin-bottom:20px}.crm-hero h1{color:white;margin:0 0 8px}.crm-hero a{color:#ffdb62}.crm-grid{display:grid;grid-template-columns:minmax(290px,380px) minmax(0,1fr);gap:20px}.crm-card{background:#fff;border:1px solid #e2e8f0;border-radius:17px;padding:24px;box-shadow:0 8px 26px #15233d0d}.crm-card h2{margin:0 0 14px;font-size:1.2rem}.crm-field{display:block;margin-bottom:13px;font-size:.85rem;font-weight:700;color:#334155}.crm-field input,.crm-field select,.crm-field textarea{display:block;width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:9px;background:white;color:#0f172a;font:inherit;margin-top:5px}.crm-submit{background:#1d3c84;color:white;border:0;border-radius:10px;padding:12px 20px;font-weight:800;cursor:pointer}.crm-table{width:100%;border-collapse:collapse;font-size:.87rem}.crm-table td,.crm-table th{padding:11px;border-bottom:1px solid #e2e8f0;text-align:left}.crm-table th{background:#f1f5f9}.crm-scroll{overflow-x:auto}.crm-alert{padding:12px;margin-bottom:15px;background:#fff1f2;color:#9f1239;border-radius:9px}.crm-success{background:#dcfce7;color:#166534}.crm-search{display:flex;gap:8px;margin-bottom:14px}.crm-search input{flex:1;min-width:0;padding:10px;border:1px solid #cbd5e1;border-radius:9px}@media(max-width:850px){.crm-grid{grid-template-columns:1fr}}
</style>
<div class="crm-wrap"><div class="crm-hero"><h1>Clients &amp; Prospects</h1><p>Référentiel CRM partagé avec les projets BTP.</p><a href="<?= $e(View::url('crm/dashboard')) ?>">Tableau de bord CRM</a> · <a href="<?= $e(View::url('crm/clients/nouveau')) ?>">+ Nouveau client</a></div>
<?php if($errorCrm):?><div class="crm-alert" role="alert"><?= $e($errorCrm) ?></div><?php endif;?>
<?php if($successCrm):?><div class="crm-alert crm-success"><?= $e($successCrm) ?></div><?php endif;?>
<div class="crm-grid"><section class="crm-card"><h2><?= $clientEdit?'Modifier la fiche':($crmNewMode?'Créer un client / prospect':'Ajouter un client / prospect') ?></h2>
<form method="post" action="<?= $e(View::url('crm/clients')) ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrfCrm) ?>">
<?php if($clientEdit):?><input type="hidden" name="id" value="<?= (int)$clientEdit['id'] ?>"><?php endif;?>
<?php foreach(['name'=>'Raison sociale / nom','contact_name'=>'Personne à contacter','email'=>'Adresse email','phone'=>'Téléphone','country'=>'Pays','city'=>'Ville','sector'=>"Secteur d'activité"] as $key=>$label):?>
<label class="crm-field"><?= $e($label) ?><input name="<?= $e($key) ?>" value="<?= $e($clientEdit[$key]??'') ?>" <?= $key==='name'?'required':'' ?> maxlength="<?= $key==='name'?180:($key==='email'?150:160) ?>"></label>
<?php endforeach;?>
<h3>Identification légale</h3>
<p style="color:#64748b;font-size:.83rem">L'enregistrement reste en attente de vérification officielle. Aucun numéro n'est considéré comme authentifié automatiquement.</p>
<label class="crm-field">Nature juridique<select name="legal_form" required>
<option value="personne_morale" <?= ($clientEdit['legal_form']??'personne_morale')==='personne_morale'?'selected':'' ?>>Personne morale (entreprise)</option>
<option value="personne_physique" <?= ($clientEdit['legal_form']??'')==='personne_physique'?'selected':'' ?>>Personne physique</option></select></label>
<label class="crm-field">Pays d'immatriculation / identité (code ISO : CI, FR, DE...)<input name="identity_country" required maxlength="2" value="<?= $e($clientEdit['identity_country']??'CI') ?>"></label>
<label class="crm-field">Type d'identifiant (CNI, NCC, RCCM, SIREN, SIRET, REGISTRE, TVA, LEI)<input name="identifier_kind" required maxlength="32" value="<?= $e($clientEdit['identifier_kind']??'') ?>"></label>
<label class="crm-field">Numéro de l'identifiant officiel<input name="identifier_value" required maxlength="100" value="<?= $e($clientEdit['identifier_value']??'') ?>"></label>
<label class="crm-field"><input type="checkbox" name="cni_present" value="1" style="width:auto;display:inline" <?= !empty($clientEdit['cni_present'])?'checked':'' ?>> CNI présentée (personne physique ivoirienne)</label>
<?php if($clientEdit):?><p><strong>Vérification :</strong> <?= $e($clientEdit['verification_status']??'pending') ?> — aucune validation externe automatique dans cette version.</p><?php endif;?>
<label class="crm-field">Catégorie<select name="type"><?php foreach(['prospect'=>'Prospect','client'=>'Client','partner'=>'Partenaire'] as $key=>$label):?><option value="<?= $key ?>" <?= ($clientEdit['type']??'prospect')===$key?'selected':'' ?>><?= $label ?></option><?php endforeach;?></select></label>
<label class="crm-field">Statut<select name="status"><?php foreach(['new'=>'Nouveau','active'=>'Actif','dormant'=>'Inactif','lost'=>'Perdu'] as $key=>$label):?><option value="<?= $key ?>" <?= ($clientEdit['status']??'new')===$key?'selected':'' ?>><?= $label ?></option><?php endforeach;?></select></label>
<label class="crm-field">Notes<textarea name="notes" rows="3"><?= $e($clientEdit['notes']??'') ?></textarea></label>
<button class="crm-submit" type="submit"><?= $clientEdit?'Enregistrer les modifications':'Créer la fiche' ?></button>
<?php if($clientEdit):?><a href="<?= $e(View::url('crm/clients')) ?>">Annuler</a><?php endif;?>
</form></section><section class="crm-card"><h2>Répertoire CRM (<?= count($clients) ?>)</h2>
<form class="crm-search" method="get"><input name="q" placeholder="Rechercher un client" value="<?= $e($_GET['q']??'') ?>"><button class="crm-submit" type="submit">Rechercher</button></form>
<div class="crm-scroll"><table class="crm-table"><thead><tr><th>Client</th><th>Type</th><th>Contact</th><th>Téléphone</th><th>Ville</th><th></th></tr></thead><tbody>
<?php foreach($clients as $client):?><tr><td><strong><?= $e($client['name']) ?></strong></td><td><?= $e($client['type']) ?></td><td><?= $e($client['contact_name']) ?></td><td><?= $e($client['phone']) ?></td><td><?= $e($client['city']) ?></td><td><a href="<?= $e(View::url('crm/clients')) ?>?edit=<?= (int)$client['id'] ?>">Modifier</a></td></tr><?php endforeach;?>
<?php if(!$clients):?><tr><td colspan="6">Aucun client enregistré. Créez votre première fiche.</td></tr><?php endif;?></tbody></table></div></section></div></div>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';?>
