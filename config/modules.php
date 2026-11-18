<?php
declare(strict_types=1);
$profile = (string) (getenv('FINEA_MODULE_PROFILE') ?: 'ibtp');
if (!in_array($profile, ['default', 'ibtp'], true)) {
    $profile = 'ibtp';
}
return require __DIR__ . '/module-profiles/' . $profile . '.php';
