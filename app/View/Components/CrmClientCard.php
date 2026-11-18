<?php
declare(strict_types=1);

namespace App\View\Components;

use App\Helpers\View;

final class CrmClientCard
{
    /** @param array<string, mixed> $client */
    public static function render(array $client): string
    {
        $e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $name = trim((string) ($client['name'] ?? ''));
        $phone = trim((string) ($client['phone'] ?? ''));
        $phoneHref = preg_replace('/[^+0-9]/', '', $phone) ?? '';
        $editUrl = View::url('crm/clients/modifier') . '?id=' . (int) ($client['id'] ?? 0);
        ob_start();
        ?>
<article class="crm-client-card">
<div class="crm-client-head"><span class="crm-client-avatar" aria-hidden="true"><?= $e(mb_strtoupper(mb_substr($name, 0, 1))) ?></span><div class="crm-client-title"><h3><?= $e($name) ?></h3><span class="crm-client-type"><?= $e($client['type'] ?? '') ?></span></div></div>
<dl class="crm-client-details">
<div><dt>Contact</dt><dd><?= $e($client['contact_name'] ?: 'Non renseigné') ?></dd></div>
<div><dt>Téléphone</dt><dd><?php if ($phone !== '' && $phoneHref !== ''): ?><a href="tel:<?= $e($phoneHref) ?>"><?= $e($phone) ?></a><?php else: ?>Non renseigné<?php endif; ?></dd></div>
<div><dt>Ville</dt><dd><?= $e($client['city'] ?: 'Non renseignée') ?></dd></div>
</dl>
<div class="crm-client-footer"><a class="crm-client-edit" href="<?= $e($editUrl) ?>">Modifier la fiche <span aria-hidden="true">→</span></a></div>
</article>
        <?php
        return (string) ob_get_clean();
    }
}
