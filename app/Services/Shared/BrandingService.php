<?php
declare(strict_types=1);

namespace App\Services\Shared;

final class BrandingService
{
    /** @param array<string,mixed>|null $branding */
    public function __construct(private ?array $branding = null)
    {
        $this->branding ??= require dirname(__DIR__, 3) . '/config/branding.php';
    }

    public function productName(): string
    {
        return (string) ($this->branding['product_name'] ?? 'FINEA');
    }

    public function companyName(): string
    {
        return (string) ($this->branding['company_name'] ?? 'Votre entreprise');
    }

    public function tagline(): string
    {
        return (string) ($this->branding['tagline'] ?? '');
    }

    public function logoPath(): string
    {
        $path = (string) ($this->branding['logo_path'] ?? '');
        return preg_match('#^(?:images|logos)/[A-Za-z0-9_./-]+\.(?:png|svg|webp|jpg|jpeg)$#D', $path) === 1
            && !str_contains($path, '..') ? $path : '';
    }

    public function styleSheet(): string
    {
        $colors = $this->branding['colors'] ?? [];
        $colors = is_array($colors) ? $colors : [];
        $primary = $this->color($colors, 'primary', '#1d2b57');
        $accent = $this->color($colors, 'accent', '#fabd02');
        $surface = $this->color($colors, 'surface', '#f4f7fb');
        $text = $this->color($colors, 'text', '#172033');
        $muted = $this->color($colors, 'muted', '#64748b');

        return ":root {\n"
            . "  --brand-primary: {$primary};\n"
            . "  --brand-accent: {$accent};\n"
            . "  --brand-surface: {$surface};\n"
            . "  --brand-text: {$text};\n"
            . "  --brand-muted: {$muted};\n"
            . "  --color-primary: var(--brand-primary);\n"
            . "  --color-primary-2: var(--brand-primary);\n"
            . "  --color-secondary: var(--brand-accent);\n"
            . "  --finea-navy: var(--brand-primary);\n"
            . "  --finea-navy-2: var(--brand-primary);\n"
            . "  --finea-gold: var(--brand-accent);\n"
            . "  --finea-bg: var(--brand-surface);\n"
            . "  --finea-text: var(--brand-text);\n"
            . "  --finea-muted: var(--brand-muted);\n"
            . "}\n"
            . ".module-sidebar { background: var(--brand-primary); }\n"
            . ".module-brand-mark { background: var(--brand-accent); }\n"
            . ".finea-page-header { background: var(--brand-primary); }\n"
            . ".btn-primary { background: var(--brand-primary); }\n";
    }

    /** @param array<string,mixed> $colors */
    private function color(array $colors, string $key, string $fallback): string
    {
        $value = (string) ($colors[$key] ?? '');
        return preg_match('/^#[0-9a-fA-F]{6}$/D', $value) === 1 ? $value : $fallback;
    }
}
