<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\FormWizard;
$e=static fn($v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start();?>
<style>.opp-wrap{max-width:860px;margin:25px auto;padding:0 16px}.opp-card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:26px;box-shadow:0 8px 25px #15284d12}.opp-card h1{font-size:1.5rem;color:#19335d}.opp-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}.opp-form label{display:grid;gap:7px;font-weight:700;font-size:.85rem;color:#334766}.opp-form input,.opp-form select,.opp-form textarea{width:100%;min-width:0;min-height:44px;padding:10px;border:1px solid #cbd5e1;border-radius:9px;background:white}.opp-wide{grid-column:1/-1}.opp-form button{border:0;border-radius:10px;background:#254b95;color:#fff;padding:13px 18px;font-weight:800;cursor:pointer}.opp-help{font-size:.8rem;color:#667b99}.opp-error{background:#fff1f2;color:#9f1239;padding:12px;border-radius:10px}@media(max-width:650px){.opp-form{grid-template-columns:1fr}.opp-card{padding:18px}}</style>
<div class="opp-wrap"><div class="opp-card"><a href="<?= $e(View::url('crm/clients')) ?>">← Clients et prospects</a><h1>Nouvelle opportunité commerciale</h1><p>Rattachez cette affaire à un client ou prospect avant son éventuelle transformation en projet ou chantier.</p>
<?php if($errorOpp):?><p class="opp-error" role="alert"><?= $e($errorOpp) ?></p><?php endif;?>
<form class="opp-form" method="post" enctype="multipart/form-data" action="<?= $e(View::url('crm/opportunites')) ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrfOpp) ?>">
<label class="opp-wide">Client / prospect *<select name="client_id" required><option value="">Sélectionner un client</option><?php foreach($clients as $client):?><option value="<?= (int)$client['id'] ?>" <?= (int)$client['id']===$selectedClient?'selected':'' ?>><?= $e($client['name']) ?></option><?php endforeach;?></select></label>
<label class="opp-wide">Intitulé de l'opportunité *<input name="title" maxlength="180" required placeholder="Ex. Construction d'un bâtiment administratif"></label>
<label>Étape commerciale<select name="stage"><option value="qualification">Qualification</option><option value="proposition">Proposition</option><option value="negociation">Négociation</option><option value="gagnee">Gagnée</option><option value="perdue">Perdue</option></select></label>
<label>Montant estimé (FCFA)<input type="number" name="estimated_amount" min="0" step="0.01"></label>
<label>Date limite / deadline<input type="date" name="deadline"></label>
<label class="opp-wide">Description et besoins<textarea name="description" rows="5" maxlength="10000" placeholder="Objet, périmètre, attentes du client, prochaines étapes..."></textarea></label>
<label class="opp-wide">Pièces jointes<input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"><span class="opp-help">Choisissez les fichiers depuis votre appareil. 5 fichiers maximum, 10 Mo chacun. Aucun glisser-déposer requis.</span></label>
<div class="opp-wide"><button type="submit">Enregistrer l'opportunité</button></div>
</form></div></div>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';?>