<?php
declare(strict_types=1);
namespace App\View\Components;
use App\Helpers\View;
final class CrmOpportunityFilters {
 public static function render(array $filters,array $clients):string{
  $e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
  $stages=['qualification'=>'Qualification','proposition'=>'Proposition','negociation'=>'Négociation','gagnee'=>'Gagnée','perdue'=>'Perdue'];
  $export=View::url('crm/opportunites/export').'?'.http_build_query($filters);
  ob_start();?>
<form class="crm-opp-filter" method="get" action="<?= $e(View::url('crm/opportunites')) ?>">
<label>Recherche<input name="q" value="<?= $e($filters['q']??'') ?>" placeholder="Opportunité, client, description"></label>
<label>Client<select name="client_id"><option value="">Tous les clients</option><?php foreach($clients as $client):?><option value="<?= (int)$client['id'] ?>" <?= (string)($filters['client_id']??'')===(string)$client['id']?'selected':'' ?>><?= $e($client['name']) ?></option><?php endforeach;?></select></label>
<label>Étape<select name="stage"><option value="">Toutes les étapes</option><?php foreach($stages as $key=>$label):?><option value="<?= $e($key) ?>" <?= ($filters['stage']??'')===$key?'selected':'' ?>><?= $e($label) ?></option><?php endforeach;?></select></label>
<label>Échéance du<input type="date" name="deadline_from" value="<?= $e($filters['deadline_from']??'') ?>"></label>
<label>Échéance au<input type="date" name="deadline_to" value="<?= $e($filters['deadline_to']??'') ?>"></label>
<div class="crm-opp-filter-actions"><button type="submit">Rechercher</button><a href="<?= $e(View::url('crm/opportunites')) ?>">Réinitialiser</a><a href="<?= $e($export) ?>">Exporter les résultats CSV</a></div>
</form>
<?php return (string)ob_get_clean();
 }
}
