<?php
declare(strict_types=1);

namespace App\View\Components;

/**
 * Composant de présentation d'un formulaire à étapes.
 * Le HTML, les étapes et les commandes sont rendus côté PHP ;
 * le contrôleur JS générique assure uniquement les transitions dans le navigateur.
 */
final class FormWizard
{
    /** @param list<array{id:string,label:string,hint?:string,icon?:string}> $steps */
    public static function progress(array $steps): string
    {
        $e = static fn(string $s):string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '<nav class="crm-tabbar" aria-label="Progression du formulaire">';
        foreach ($steps as $i => $step) {
            $id = (string) $step['id'];
            $label = (string) $step['label'];
            $hint = (string) ($step['hint'] ?? '');
            // Les icônes SVG sont fournies par le composant et ne dépendent d'aucune police externe.
            $icon = self::icon((string) ($step['icon'] ?? $id));
            $html .= '<div class="crm-tab" data-crm-tab="' . $e($id) . '"' . ($i === 0 ? ' aria-current="step"' : '') . '><span class="crm-step-dot">' . $icon . '<span class="crm-step-check" aria-hidden="true" hidden>✓</span></span><span class="crm-tab-title">' . ($i + 1) . '. ' . $e($label) . '</span><small class="crm-step-hint">' . $e($hint) . '</small></div>';
        }
        return $html . '</nav>';
    }

    /** @param list<array{id:string,label:string,hint?:string,icon?:string}> $steps */
    public static function formOpen(string $action, array $steps, string $method = 'post'): string
    {
        $e = static fn(string $s):string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $ids = array_map(static fn(array $step):string => (string)$step['id'], $steps);
        return '<form class="crm-form-layout" data-finea-wizard="1" data-wizard-steps="' . $e(json_encode($ids, JSON_THROW_ON_ERROR)) . '" novalidate method="' . $e($method) . '" action="' . $e($action) . '">';
    }

    public static function controls(string $submitLabel): string
    {
        $e = static fn(string $s):string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<div class="crm-wizard-error" role="alert" aria-live="polite" data-wizard-error></div>'
            . '<div class="crm-wizard-actions">'
            . '<button class="crm-secondary" type="button" data-wizard-prev hidden>← Précédent</button>'
            . '<span data-wizard-counter aria-live="polite">Étape 1 sur 3</span>'
            . '<button class="crm-submit" type="button" data-wizard-next>Suivant →</button>'
            . '<button class="crm-submit" type="submit" data-wizard-submit hidden>' . $e($submitLabel) . '</button>'
            . '</div>';
    }

    private static function icon(string $name): string
    {
        return match ($name) {
            'identity' => "<svg viewBox=\"0 0 24 24\" width=\"21\" height=\"21\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.9\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\"><rect x=\"2.5\" y=\"4.5\" width=\"19\" height=\"15\" rx=\"2\"/><circle cx=\"8\" cy=\"10\" r=\"2\"/><path d=\"M4.5 16c.6-2.2 6.4-2.2 7 0M14 9h5M14 13h5M14 16h3\"/></svg>",
            'contact' => "<svg viewBox=\"0 0 24 24\" width=\"21\" height=\"21\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.9\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\"><rect x=\"5\" y=\"3\" width=\"16\" height=\"18\" rx=\"2\"/><path d=\"M2 7h5M2 12h5M2 17h5\"/><circle cx=\"13\" cy=\"9\" r=\"2\"/><path d=\"M9.5 17c0-3.2 7-3.2 7 0\"/></svg>",
            'business' => "<svg viewBox=\"0 0 24 24\" width=\"21\" height=\"21\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.9\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\"><rect x=\"3\" y=\"7\" width=\"18\" height=\"14\" rx=\"2\"/><path d=\"M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13c5 3 13 3 18 0M11 14h2\"/></svg>",
            default => "<svg viewBox=\"0 0 24 24\" width=\"21\" height=\"21\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.9\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\"><rect x=\"2.5\" y=\"4.5\" width=\"19\" height=\"15\" rx=\"2\"/><circle cx=\"8\" cy=\"10\" r=\"2\"/><path d=\"M4.5 16c.6-2.2 6.4-2.2 7 0M14 9h5M14 13h5M14 16h3\"/></svg>",
        };
    }
}
