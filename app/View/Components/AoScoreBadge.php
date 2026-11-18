<?php
declare(strict_types=1);
namespace App\View\Components;
/** Badge de score : interpolation HSL rouge (0 %) -> vert (100 %). */
final class AoScoreBadge
{
    public static function render(int|float $score):string
    {
        $score=max(0,min(100,(int)round($score)));
        $hue=(int)round($score*1.2);
        $background='hsl('.$hue.' 74% 93%)';
        $border='hsl('.$hue.' 54% 69%)';
        $foreground='hsl('.$hue.' 75% 25%)';
        return '<span class="ao-score" style="--ao-score-bg:'.$background.';--ao-score-border:'.$border.';--ao-score-color:'.$foreground.'" aria-label="Pertinence : '.$score.' pour cent"><strong>'.$score.' %</strong><small>pertinence</small></span>';
    }
}
