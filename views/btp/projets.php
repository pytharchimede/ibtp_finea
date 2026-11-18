<?php
declare(strict_types=1);
$e=static fn(mixed $v): string => htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$app=require BASE_PATH.'/config/app.php';
ob_start();
?>
<main style="max-width:1100px;margin:2rem auto;padding:1rem">
<h1>Projets &amp; Chantiers</h1>
<p>Chaque projet est lié à un client. Le devis approuvé et le bon de commande permettront ensuite d'ouvrir un chantier.</p>
<?php if($errorBtp): ?><p role="alert"><?= $e($errorBtp) ?></p><?php endif; ?>
<h2>Créer un projet</h2>
<p>Renseignez la référence, l'intitulé et sélectionnez un client CRM existant. Après création, le projet apparaîtra dans la liste ci-dessous.</p>
<form method="post" action="<?= $e(rtrim($app['url'],'/').'/btp/projets') ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrfBtp) ?>">
<label>Référence <input name="reference" required maxlength="40"></label>
<label>Intitulé <input name="intitule" required maxlength="180"></label>
<label>Client CRM
<select id="btp-client-select" name="client_id"><option value="">Client libre (hors CRM)</option>
<?php foreach($clientsBtp as $client): ?>
<option value="<?= (int)$client['id'] ?>" data-name="<?= $e($client['name']) ?>"><?= $e($client['name']) ?></option>
<?php endforeach; ?>
</select></label>
<label>Nom du client <input id="btp-client-name" name="client_nom" required maxlength="180"></label>
<script>
document.getElementById('btp-client-select').addEventListener('change', function(){
 const option=this.options[this.selectedIndex];
 if(option.dataset.name)document.getElementById('btp-client-name').value=option.dataset.name;
});
</script>
<button type="submit">Créer</button>
</form>
<h2>Projets</h2>
<table><thead><tr><th>Référence</th><th>Projet</th><th>Client</th><th>Statut</th></tr></thead><tbody>
<?php foreach($projets as $p): ?>
<tr><td><?= $e($p['reference']) ?></td><td><?= $e($p['intitule']) ?></td><td><?= $e($p['client_nom']) ?></td><td><?= $e($p['statut']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></main>
<?php $content=ob_get_clean(); require BASE_PATH.'/views/layouts/module.php'; ?>
