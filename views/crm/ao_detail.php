<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\Form;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$entry=$aoItem;
$deadline=(string)($entry['date_limite']??'');
$expired=$deadline===''||$deadline<date('Y-m-d');
$linked=(int)($entry['crm_opportunity_id']??0)>0;
$source=(string)($entry['url_source']??'');
$sourceSafe=preg_match('~^https://[^[:space:]]+$~i',$source)===1;
$documents=json_decode((string)($entry['documents_source_json']??'[]'),true);
$documents=is_array($documents)?$documents:[];
$clientOptions=[['value'=>'','label'=>'Sélectionnez le client ou prospect existant']];
foreach($aoClients as $client)$clientOptions[]=['value'=>(string)$client['id'],'label'=>(string)$client['name']];
$statusOptions=[];
foreach(['nouveau'=>'Nouveau','a_analyser'=>'À analyser','interessant'=>'Intéressant','non_interessant'=>'Non intéressant','dossier_en_preparation'=>'Dossier en préparation','soumission'=>'Soumission','soumissionne'=>'Soumissionné','gagne'=>'Gagné','perdu'=>'Perdu','expire'=>'Expiré'] as $code=>$label)$statusOptions[]=['value'=>$code,'label'=>$label];
ob_start();?>
<div class="ao-wrap"><header class="ao-hero"><a class="ao-back" href="<?= $e(View::url('crm/veille-ao')) ?>">← Retour à la veille</a><h1><?= $e($entry['titre']) ?></h1><p><?= $e($entry['source']) ?> · <?= $e(($entry['numero_ao']??'')?:($entry['reference']??'')) ?></p></header>
<?php if($aoFlash):?><p class="ao-alert" role="status"><?= $e($aoFlash) ?></p><?php endif;?>
<div class="ao-detail-grid"><section class="ao-panel ao-details"><h2>Informations de l’appel d’offres</h2>
<dl><div><dt>Autorité contractante</dt><dd><?= $e($entry['autorite_contractante']??'') ?></dd></div><div><dt>Type de marché</dt><dd><?= $e($entry['type_marche']??'') ?></dd></div><div><dt>Secteur</dt><dd><?= $e($entry['secteur']??'') ?></dd></div><div><dt>Publication</dt><dd><?= $e($entry['date_publication']??'Non indiquée') ?></dd></div><div><dt>Échéance</dt><dd><?= $e($deadline?:'Non indiquée') ?></dd></div><div><dt>Pertinence BTP</dt><dd><?= (int)$entry['score_pertinence'] ?> % — indicateur de correspondance, pas de probabilité d’attribution</dd></div></dl>
<h3>Objet et description</h3><p><?= nl2br($e(($entry['resume']??'')?:$entry['titre'])) ?></p>
<?php if($sourceSafe):?><p><a class="ao-primary ao-inline" href="<?= $e($source) ?>" target="_blank" rel="noopener noreferrer">Consulter la source officielle</a></p><?php endif;?>
<?php if($documents):?><h3>Documents mentionnés par la source</h3><div class="ao-docs"><?php foreach($documents as $document):?><?php $url=(string)($document['url']??'');if(!preg_match('~^https://[^[:space:]]+$~i',$url))continue;?><a href="<?= $e($url) ?>" target="_blank" rel="noopener noreferrer"><?= $e(($document['label']??'')?:'Consulter un document') ?></a><?php endforeach;?></div><?php endif;?>
</section>
<aside class="ao-panel ao-actions"><h2>Traitement commercial</h2><p>Statut : <strong><?= $e(str_replace('_',' ',$entry['statut_interne'])) ?></strong></p>
<?php if(!$linked):?>
<form method="post" action="<?= $e(View::url('crm/veille-ao/statut')) ?>"><input type="hidden" name="_csrf" value="<?= $e($aoCsrf) ?>"><input type="hidden" name="id" value="<?= (int)$entry['id'] ?>">
<?= Form::selectSearch('status','Qualification interne',$statusOptions,(string)$entry['statut_interne'],['placeholder'=>'Choisir un statut']) ?>
<button type="submit" class="ao-primary">Enregistrer le statut</button></form>
<hr>
<h3>Convertir en opportunité CRM</h3>
<p>Pour conserver la traçabilité, sélectionnez un client ou prospect déjà présent dans le CRM. Aucune fiche client n’est créée automatiquement.</p>
<?php if($expired):?><p class="ao-warning">Cet AO est expiré ou sa date limite n’est pas renseignée. La conversion est désactivée.</p><?php elseif($entry['statut_interne']==='non_interessant'):?><p class="ao-warning">Cet AO est classé non intéressant. Modifiez sa qualification avant conversion.</p><?php else:?>
<form method="post" action="<?= $e(View::url('crm/veille-ao/convertir')) ?>"><input type="hidden" name="_csrf" value="<?= $e($aoCsrf) ?>"><input type="hidden" name="id" value="<?= (int)$entry['id'] ?>">
<?= Form::selectSearch('client_id','Client / prospect',$clientOptions,'',['placeholder'=>'Rechercher un client...']) ?>
<button type="submit" class="ao-primary">Créer l’opportunité</button></form>
<?php endif;?>
<?php else:?><p class="ao-success">Déjà converti en opportunité CRM #<?= (int)$entry['crm_opportunity_id'] ?>.</p><p>La création en double est bloquée.</p><?php endif;?>
</aside></div></div>
<?php $content=ob_get_clean();$additionalStyles=array_merge($additionalStyles??[],['css/components/crm-ao.css']);require BASE_PATH.'/views/layouts/module.php';?>
