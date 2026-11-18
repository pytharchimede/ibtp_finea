<?php
declare(strict_types=1);
// Liste fermée : aucune inclusion arbitraire depuis les variables d'environnement.
$profile = (string) (getenv('FINEA_BRAND_PROFILE') ?: 'ibtp');
if (!in_array($profile, ['finea', 'ibtp'], true)) {
    $profile = 'finea';
}
return require __DIR__ . '/brands/' . $profile . '.php';
