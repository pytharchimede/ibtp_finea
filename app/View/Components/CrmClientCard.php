<?php
declare(strict_types=1);

namespace App\View\Components;

use App\Helpers\View;

final class CrmClientCard
{
    /** @param array<string, mixed> $client */
    public static function render(array $client, string $csrf = ''): string
    {
        $e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $name = trim((string) ($client['name'] ?? ''));
        $phone = trim((string) ($client['phone'] ?? ''));
        $phoneHref = preg_replace('/[^+0-9]/', '', $phone) ?? '';
        $editUrl = View::url('crm/clients/modifier') . '?id=' . (int) ($client['id'] ?? 0);
        $required = ['name','contact_name','email','phone','city','country','sector','identifier_value'];
        $filled = 0;
        foreach ($required as $key) if (trim((string)($client[$key] ?? '')) !== '') $filled++;
        $percent = (int)round($filled / count($required) * 100);
        $verified = (string)($client['verification_status'] ?? 'pending') === 'verified';
        $verificationLabel = $verified ? 'Identité vérifiée' : 'Identité non vérifiée';
        ob_start();
        ?>
<article class="crm-client-card">
<div class="crm-client-head"><span class="crm-client-avatar" aria-hidden="true"><?= $e(mb_strtoupper(mb_substr($name, 0, 1))) ?></span><div class="crm-client-title"><h3><?= $e($name) ?></h3><span class="crm-client-type"><?= $e($client['type'] ?? '') ?></span><div class="crm-client-badges"><span class="crm-client-badge <?= $percent===100?'is-good':'is-warning' ?>">Fiche <?= $percent ?> %</span><span class="crm-client-badge <?= $verified?'is-good':'is-warning' ?>"><?= $e($verificationLabel) ?></span></div></div></div>
<dl class="crm-client-details">
<div><dt>Contact</dt><dd><?= $e($client['contact_name'] ?: 'Non renseigné') ?></dd></div>
<div><dt>Téléphone</dt><dd><?php if ($phone !== '' && $phoneHref !== ''): ?><a href="tel:<?= $e($phoneHref) ?>"><?= $e($phone) ?></a><?php else: ?>Non renseigné<?php endif; ?></dd></div>
<div><dt>Ville</dt><dd><?= $e($client['city'] ?: 'Non renseignée') ?></dd></div>
</dl>
<div class="crm-client-footer">
<a class="crm-client-action primary" href="<?= $e(View::url('crm/opportunites/nouvelle')) ?>?client_id=<?= (int)$client['id'] ?>">+ Opportunité</a>
<a class="crm-client-action" href="<?= $e($editUrl) ?>">Modifier</a>
<a class="crm-client-action" href="<?= $e(View::url('crm/clients/export')) ?>?id=<?= (int)$client['id'] ?>">Exporter</a>
</div>
<form class="crm-client-qualification" method="post" action="<?= $e(View::url('crm/clients/qualifier')) ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrf) ?>"><input type="hidden" name="id" value="<?= (int)$client['id'] ?>">
<label for="qualify-<?= (int)$client['id'] ?>">Qualification</label>
<select id="qualify-<?= (int)$client['id'] ?>" name="type"><option value="prospect" <?= ($client['type']??'')==='prospect'?'selected':'' ?>>Prospect</option><option value="client" <?= ($client['type']??'')==='client'?'selected':'' ?>>Client</option><option value="partner" <?= ($client['type']??'')==='partner'?'selected':'' ?>>Partenaire</option></select>
<button type="submit">Mettre à jour</button>
</form>
</article>
        <?php
        return (string) ob_get_clean();
    }
}
