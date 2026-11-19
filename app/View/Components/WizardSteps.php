<?php
declare(strict_types=1);
namespace App\View\Components;
use App\Helpers\View;
/** Shared step navigation for BTP and FEB wizards. Business validation remains in services. */
final class WizardSteps
{
    /** @param array<int,array{label:string,icon?:string,done?:bool}> $steps */
    public static function render(array $steps, string $title = 'Étapes du parcours'): string
    {
        $html='<nav class="btp-wizard-steps" aria-label="'.View::e($title).'"><div class="btp-wizard-track" aria-hidden="true"><span data-wizard-fill></span></div>';
        foreach ($steps as $index => $step) {
            $icon=preg_replace('/[^a-z0-9 _-]/i','',(string)($step['icon']??'fa-solid fa-circle'));
            $html.='<button type="button" data-wizard-nav="'.$index.'" data-complete="'.(!empty($step['done'])?'1':'0').'"'.($index===0?' aria-current="step"':'').'><span class="btp-wizard-step-icon"><i class="'.View::e($icon).'" aria-hidden="true"></i></span><small>'.View::e((string)$step['label']).'</small></button>';
        }
        return $html.'</nav>';
    }
}
