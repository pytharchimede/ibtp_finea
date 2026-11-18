<?php
declare(strict_types=1);
namespace App\View\Components;
use App\Helpers\View;

final class CrmAoFilters
{
    public static function render(array $filters,int $recommended=60):string
    {
        $e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $scope=[['value'=>'active','label'=>'Échéances ouvertes'],['value'=>'all','label'=>'Toutes les échéances'],['value'=>'expired','label'=>'Échéances dépassées'],['value'=>'undated','label'=>'Sans date limite']];
        $statuses=[['value'=>'','label'=>'Tous les statuts']];
        foreach(['nouveau'=>'Nouveau','a_analyser'=>'À analyser','interessant'=>'Intéressant','non_interessant'=>'Non intéressant','dossier_en_preparation'=>'Dossier en préparation','soumission'=>'Soumission','soumissionne'=>'Soumissionné','converti_crm'=>'Converti CRM','gagne'=>'Gagné','perdu'=>'Perdu','expire'=>'Expiré'] as $code=>$label)$statuses[]=['value'=>$code,'label'=>$label];
        $sources=[['value'=>'','label'=>'Toutes les sources'],['value'=>'DGMP public','label'=>'Marchés publics DGMP'],['value'=>'Contenu local DGH','label'=>'Contenu local DGH']];
        $scores=[['value'=>'','label'=>'Tous les scores'],['value'=>'recommended','label'=>'Seuil recommandé ('.$recommended.' % et plus)'],['value'=>'40','label'=>'40 % et plus'],['value'=>'60','label'=>'60 % et plus'],['value'=>'80','label'=>'80 % et plus']];
        ob_start();?>
<form class="ao-filters" action="<?= $e(View::url('crm/veille-ao')) ?>" method="get">
<?= Form::input('q','Rechercher', $filters['q']??'', ['placeholder'=>'Référence, objet, autorité, description...']) ?>
<?= Form::selectSearch('deadline_scope','Échéance',$scope,$filters['deadline_scope']??'active',['placeholder'=>'Choisir une période']) ?>
<?= Form::selectSearch('source','Source',$sources,$filters['source']??'',['placeholder'=>'Toutes les sources']) ?>
<?= Form::selectSearch('status','Statut',$statuses,$filters['status']??'',['placeholder'=>'Tous les statuts']) ?>
<?= Form::selectSearch('min_score','Pertinence',$scores,$filters['min_score']??'',['placeholder'=>'Tous les scores']) ?>
<div class="ao-filter-check"><label><input type="checkbox" name="deadline_soon" value="1" <?= ($filters['deadline_soon']??'')==='1'?'checked':'' ?>> Échéance dans les 14 jours</label></div>
<div class="ao-filter-actions"><button type="submit">Rechercher</button><a href="<?= $e(View::url('crm/veille-ao')) ?>">Réinitialiser</a><a href="<?= $e(View::url('crm/veille-ao/export')) ?>?<?= $e(http_build_query($filters)) ?>">Exporter les résultats CSV</a></div>
</form>
<?php return (string)ob_get_clean();
    }
}
