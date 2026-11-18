<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Services/Shared/BrandingService.php';
require_once dirname(__DIR__, 2) . '/app/Services/Shared/ModuleRegistry.php';

use App\Services\Shared\BrandingService;
use App\Services\Shared\ModuleRegistry;

$branding = new BrandingService();
if ($branding->productName() !== 'FINEA' || trim($branding->companyName()) === '') {
    throw new RuntimeException('Profil branding invalide.');
}
$css = $branding->styleSheet();
if (!str_contains($css, '--brand-primary: #') || str_contains($css, 'display:none')) {
    throw new RuntimeException('Feuille de marque invalide.');
}

$modules = new ModuleRegistry();
if (!$modules->isEnabled('finance') || !$modules->isEnabled('admin')) {
    throw new RuntimeException('Noyau modulaire indisponible.');
}
if ($modules->isEnabled('transit-douane')) {
    throw new RuntimeException('Ancien module transit encore actif.');
}

putenv('FINEA_MODULE_PROFILE=ibtp');
$profile = require dirname(__DIR__, 2) . '/config/modules.php';
if (($profile['site-admin'] ?? true) !== false) {
    throw new RuntimeException('Le site transit ne doit pas être actif pour IBTP.');
}
putenv('FINEA_MODULE_PROFILE');

echo "COMMERCIAL_CORE_OK\n";
