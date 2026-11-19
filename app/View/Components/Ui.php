<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Helpers\View;

final class Ui
{
    /**
     * API moderne : Ui::pageHeader('Titre', 'Sous-titre', ['eyebrow' => '...', 'actions' => [...]])
     * API compatible : Ui::pageHeader('Eyebrow', 'Titre', 'Sous-titre', 'Actions', ['class' => '...'])
     *
     * @param string|array<string,mixed> $subtitleOrOptions
     * @param string|array<int,string> $actions
     * @param array<string,mixed> $attrs
     */
    public static function pageHeader(
        string $titleOrEyebrow,
        string $subtitleOrTitle = '',
        string|array $subtitleOrOptions = '',
        string|array $actions = '',
        array $attrs = []
    ): string
    {
        if (is_array($subtitleOrOptions)) {
            $options = $subtitleOrOptions;
            $title = $titleOrEyebrow;
            $subtitle = $subtitleOrTitle;
            $eyebrow = (string) ($options['eyebrow'] ?? '');
            $actions = $options['actions'] ?? '';
            if (!is_string($actions) && !is_array($actions)) {
                $actions = '';
            }
            $attrs = $options;
            unset($attrs['eyebrow'], $attrs['actions']);
        } else {
            $eyebrow = $titleOrEyebrow;
            $title = $subtitleOrTitle;
            $subtitle = $subtitleOrOptions;
        }

        $class = Html::classes(['finea-page-header', (string) ($attrs['class'] ?? '')]);
        $eyebrowHtml = $eyebrow !== '' ? '<p class="rh-eyebrow">' . View::e($eyebrow) . '</p>' : '';
        $actionsHtml = '';
        $renderedActions = is_array($actions)
            ? implode('', array_filter($actions, 'is_string'))
            : $actions;

        if ($renderedActions !== '') {
            $actionsHtml = str_contains($renderedActions, 'class="finea-header-actions"')
                ? $renderedActions
                : '<div class="finea-header-actions">' . $renderedActions . '</div>';
        }

        return '<section class="' . View::e($class) . '"><div>' . $eyebrowHtml . '<h1>' . View::e($title) . '</h1>'
            . ($subtitle !== '' ? '<p>' . View::e($subtitle) . '</p>' : '')
            . '</div>' . $actionsHtml . '</section>';
    }

    /** @param array<string,mixed> $attrs */
    public static function section(string $title, string $content, string $subtitle = '', array $attrs = []): string
    {
        $attrs['class'] = Html::classes(['finea-section-card', (string) ($attrs['class'] ?? '')]);

        return '<section' . Html::attrs($attrs) . '><div class="finea-section-heading"><h2 class="finea-section-title">' . View::e($title) . '</h2>'
            . ($subtitle !== '' ? '<span>' . View::e($subtitle) . '</span>' : '')
            . '</div>' . $content . '</section>';
    }

    /**
     * API moderne : Ui::button('Label', ['href' => '...', 'variant' => 'secondary'])
     * API compatible : Ui::button('Label', 'url', 'secondary', 'button')
     *
     * @param string|array<string,mixed>|null $hrefOrOptions
     * @param string|array<string,mixed> $variantOrOptions
     */
    public static function button(string $label, string|array|null $hrefOrOptions = '', string|array $variantOrOptions = 'primary', string $type = 'button'): string
    {
        $disabled = false;
        $customClass = '';
        $options = [];

        if (is_array($hrefOrOptions)) {
            $options = $hrefOrOptions;
            $href = (string) ($options['href'] ?? '');
            $variant = (string) ($options['variant'] ?? 'primary');
            $type = (string) ($options['type'] ?? $type);
            $disabled = (bool) ($options['disabled'] ?? false);
            $customClass = (string) ($options['class'] ?? '');
        } elseif (is_array($variantOrOptions)) {
            $options = $variantOrOptions;
            $href = (string) ($hrefOrOptions ?? '');
            $variant = (string) ($options['variant'] ?? 'primary');
            $type = (string) ($options['type'] ?? $type);
            $disabled = (bool) ($options['disabled'] ?? false);
            $customClass = (string) ($options['class'] ?? '');
        } else {
            $href = (string) ($hrefOrOptions ?? '');
            $variant = $variantOrOptions;
        }

        $class = Html::classes([
            'finea-action-btn',
            'finea-action-btn--' . preg_replace('/[^a-z0-9_-]/i', '', $variant),
            $customClass,
        ]);

        $iconName = (string) ($options['icon'] ?? '');
        $iconPosition = ($options['iconPosition'] ?? 'start') === 'end' ? 'end' : 'start';
        $icons = [
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'check' => '<path d="m5 12 4 4L19 6"/>',
            'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/>',
            'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
            'ban' => '<circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/>',
            'power' => '<path d="M12 2v10M6.2 5.6a9 9 0 1 0 11.6 0"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
            'pencil' => '<path d="m16 5 3 3M4 20l4-.8L20 7a2.1 2.1 0 0 0-3-3L5 16l-1 4Z"/>',
            'arrow-left' => '<path d="m12 19-7-7 7-7M5 12h14"/>',
        ];
        $icon = isset($icons[$iconName]) ? '<svg class="finea-btn-icon" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$iconName] . '</svg>' : '';
        $buttonContent = '<span class="finea-btn-content">' . ($iconPosition === 'start' ? $icon : '') . '<span class="finea-btn-label">' . View::e($label) . '</span>' . ($iconPosition === 'end' ? $icon : '') . '</span>';
        $class .= ' finea-btn-centered';

        if ($href !== '') {
            return '<a class="' . View::e($class) . '" href="' . View::url(ltrim($href, '/')) . '">' . $buttonContent . '</a>';
        }

        unset($options['href'], $options['variant'], $options['type'], $options['disabled'], $options['class'], $options['icon'], $options['iconPosition']);
        $buttonAttrs = array_merge([
            'class' => $class,
            'type' => $type,
            'disabled' => $disabled,
        ], $options);

        return '<button' . Html::attrs($buttonAttrs) . '>' . $buttonContent . '</button>';
    }

    /** @param array<string,mixed> $options */
    public static function badge(string $label, string $tone = 'neutral', array $options = []): string
    {
        $unstyled = (bool) ($options['unstyled'] ?? false);
        $customClass = (string) ($options['class'] ?? '');

        $class = Html::classes([
            'finea-badge' => !$unstyled,
            'finea-badge--' . preg_replace('/[^a-z0-9_-]/i', '', $tone) => !$unstyled,
            $customClass,
        ]);

        return '<span class="' . View::e($class) . '">' . View::e($label) . '</span>';
    }

    public static function emptyState(string $title, string $message = ''): string
    {
        return '<div class="finea-empty-state"><strong>' . View::e($title) . '</strong>' . ($message !== '' ? '<p>' . View::e($message) . '</p>' : '') . '</div>';
    }
}
