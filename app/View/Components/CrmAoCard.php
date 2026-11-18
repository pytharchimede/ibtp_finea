<?php
declare(strict_types=1);
namespace App\View\Components;
use App\Helpers\View;
final class CrmAoCard
{
    public static function render(array $item):string
    {
        $e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $deadline=(string)($item['date_limite']??'');
        $days=$deadline!==''?(int)floor((strtotime($deadline)-strtotime(date('Y-m-d')))/86400):null;
        $url=(string)($item['url_source']??'');
        $score=max(0,min(100,(int)($item['score_pertinence']??0)));
        $safeUrl=preg_match('~^https://[^[:space:]]+$~i',$url)===1;
        ob_start();?>
<article class="ao-card">
<div class="ao-card-top"><?= AoScoreBadge::render($score) ?><span class="ao-status"><?= $e(str_replace('_',' ',(string)$item['statut_interne'])) ?></span></div>
<p class="ao-source"><?= $e($item['source']) ?> · <?= $e(($item['numero_ao']??'')?:($item['reference']??'Sans référence')) ?></p>
<h3><?= $e($item['titre']) ?></h3>
<p class="ao-authority"><?= $e(($item['autorite_contractante']??'')?:'Autorité non renseignée') ?></p>
<dl class="ao-card-meta"><div><dt>Type</dt><dd><?= $e(($item['type_marche']??'')?:'Non précisé') ?></dd></div><div><dt>Date limite</dt><dd class="<?= $days!==null&&$days<0?'ao-expired':'' ?>"><?= $deadline!==''?$e(date('d/m/Y',strtotime($deadline))):'Non précisée' ?><?= $days!==null&&$days>=0?' · J-'.$days:'' ?></dd></div></dl>
<div class="ao-card-actions"><a class="ao-primary" href="<?= $e(View::url('crm/veille-ao/detail')) ?>?id=<?= (int)$item['id'] ?>">Examiner l'appel d'offres</a><?php if($safeUrl):?><a href="<?= $e($url) ?>" target="_blank" rel="noopener noreferrer">Source officielle</a><?php endif;?></div>
</article>
<?php return (string)ob_get_clean();
    }
}
