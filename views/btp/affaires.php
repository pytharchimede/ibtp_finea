<?php
declare(strict_types=1);
$e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$app = require BASE_PATH . '/config/app.php';
$action = rtrim($app['url'], '/') . '/btp/affaires';
ob_start();
?>
<main style="max-width:1100px;margin:2rem auto;padding:1rem">
<h1>Affaires et chantiers — BTP</h1>
<p>Répertoire des affaires et budgets prévisionnels. Les chantiers seront rattachés à chaque affaire.</p>
<?php if (!empty($errorBtp)): ?><p role="alert"><?= $e($errorBtp) ?></p><?php endif; ?>
<section><h2>Nouvelle affaire</h2>
<form method="post" action="<?= $e($action) ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrfBtp) ?>">
<label>Référence <input name="reference" maxlength="40" required></label>
<label>Intitulé <input name="intitule" maxlength="180" required></label>
<label>Client <input name="client_nom" maxlength="180" required></label>
<label>Budget prévisionnel (FCFA) <input name="budget_previsionnel" type="number" min="0" step="0.01" value="0" required></label>
<button type="submit">Créer l'affaire</button>
</form></section>
<section><h2>Liste des affaires</h2>
<table><thead><tr><th>Référence</th><th>Intitulé</th><th>Client</th><th>Statut</th><th>Budget (FCFA)</th></tr></thead><tbody>
<?php foreach ($affaires as $affaire): ?>
<tr><td><?= $e($affaire['reference']) ?></td><td><?= $e($affaire['intitule']) ?></td><td><?= $e($affaire['client_nom']) ?></td><td><?= $e($affaire['statut']) ?></td><td><?= $e(number_format((float)$affaire['budget_previsionnel'], 2, ',', ' ')) ?></td></tr>
<?php endforeach; ?>
</tbody></table></section>
</main>
<?php
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/app.php';
