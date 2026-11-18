<?php
declare(strict_types=1);
// Le site public historique contient encore des pages transit : non livré à IBTP.
return array_replace(require __DIR__ . '/default.php', ['site-admin' => false]);
