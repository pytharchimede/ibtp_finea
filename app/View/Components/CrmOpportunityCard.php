<?php
declare(strict_types=1);
namespace App\View\Components;
use App\Helpers\View;
final class CrmOpportunityCard {
 public static function render(array $row):string{
  $e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
  $stage=(string)($row['stage']??'qualification');
  $labels=['qualification'=>'Qualification','proposition'=>'Proposition','negociation'=>'Négociation','gagnee'=>'Gagnée','perdue'=>'Perdue'];
  $date=(string)($row['deadline']??$row['expected_close_date']??'');
  $late=$date!==''&&$date<date('Y-m-d')&&!in_array($stage,['gagnee','perdue'],true);
  $amount=$row['estimated_amount']!==null?number_format((float)$row['estimated_amount'],0,',',' ').' '.($row['currency']??'XOF'):'Non estimé';
  ob_start();?>
<article class="crm-opp-card">
<div class="crm-opp-card-top"><span class="crm-opp-stage crm-opp-stage-<?= $e($stage) ?>"><?= $e($labels[$stage]??$stage) ?></span><span class="crm-opp-ref">#<?= (int)$row['id'] ?></span></div>
<h3><?= $e($row['title']) ?></h3><p class="crm-opp-client"><?= $e($row['client_name']) ?></p>
<?php if(trim((string)($row['description']??''))!==''):?><p class="crm-opp-description"><?= $e(mb_strimwidth((string)$row['description'],0,155,'…')) ?></p><?php endif;?>
<dl class="crm-opp-details"><div><dt>Montant estimé</dt><dd><?= $e($amount) ?></dd></div><div><dt>Échéance</dt><dd class="<?= $late?'crm-opp-late':'' ?>"><?= $date!==''?$e(date('d/m/Y',strtotime($date))):'Non définie' ?><?= $late?' · En retard':'' ?></dd></div><div><dt>Documents</dt><dd><?= (int)($row['attachment_count']??0) ?> pièce(s) jointe(s)</dd></div></dl>
</article>
<?php return (string)ob_get_clean();
 }
}
