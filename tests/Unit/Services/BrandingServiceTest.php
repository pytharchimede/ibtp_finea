<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Shared\BrandingService;
use Tests\TestCase;

final class BrandingServiceTest extends TestCase
{
    public function test_brand_identity_comes_from_config(): void
    {
        $brand = new BrandingService([
            'product_name' => 'FINEA',
            'company_name' => 'Entreprise de démonstration',
            'tagline' => 'Gestion intégrée',
        ]);
        self::assertSame('FINEA', $brand->productName());
        self::assertSame('Entreprise de démonstration', $brand->companyName());
        self::assertSame('Gestion intégrée', $brand->tagline());
    }

    public function test_untrusted_colors_cannot_inject_css(): void
    {
        $brand = new BrandingService([
            'colors' => ['primary' => '#123456', 'accent' => 'red;}body{display:none'],
        ]);
        $css = $brand->styleSheet();
        self::assertStringContainsString('--brand-primary: #123456;', $css);
        self::assertStringContainsString('--brand-accent: #fabd02;', $css);
        self::assertStringNotContainsString('display:none', $css);
    }

    public function test_logo_cannot_be_a_remote_or_parent_path(): void
    {
        self::assertSame('', (new BrandingService(['logo_path' => '../secret']))->logoPath());
        self::assertSame('', (new BrandingService(['logo_path' => 'https://example.org/logo.svg']))->logoPath());
        self::assertSame('logos/client.svg', (new BrandingService(['logo_path' => 'logos/client.svg']))->logoPath());
    }
}
