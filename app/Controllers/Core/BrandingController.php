<?php
declare(strict_types=1);

namespace App\Controllers\Core;

use App\Services\Shared\BrandingService;

final class BrandingController
{
    public function theme(): void
    {
        header('Content-Type: text/css; charset=utf-8');
        header('Cache-Control: private, max-age=300');
        header('X-Content-Type-Options: nosniff');
        echo (new BrandingService())->styleSheet();
    }
}
