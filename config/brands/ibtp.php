<?php
declare(strict_types=1);
// Identité client uniquement : couleurs FINEA par défaut en attendant la charte IBTP.
return array_replace_recursive(require __DIR__ . '/finea.php', [
    'company_name' => 'IBTP',
    'tagline' => 'Gestion intégrée de votre entreprise.',
]);
