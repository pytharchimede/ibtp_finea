<?php
declare(strict_types=1);
namespace App\View\Components;
use App\Helpers\View;
final class CrmOpportunityForm {
 public static function render(array $clients,int $selectedClient,string $csrf,?string $error):string {
  $e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
  $options=[['value'=>'','label'=>'Sélectionner un client ou prospect']];
  foreach($clients as $client)$options[]=['value'=>(string)$client['id'],'label'=>(string)$client['name']];
  $stages=[['value'=>'qualification','label'=>'Qualification'],['value'=>'proposition','label'=>'Proposition'],['value'=>'negociation','label'=>'Négociation'],['value'=>'gagnee','label'=>'Gagnée'],['value'=>'perdue','label'=>'Perdue']];
  ob_start();?>
<div class="opp-wrap">
<header class="opp-header"><div><span class="opp-kicker">CRM / Opportunités</span><h1>Nouvelle opportunité</h1><p>Constituez votre dossier commercial et rattachez-le à un client ou prospect.</p></div></header>
<?php if($error):?><p class="opp-error" role="alert"><?= $e($error) ?></p><?php endif;?>
<form class="opp-form" method="post" enctype="multipart/form-data" action="<?= $e(View::url('crm/opportunites')) ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
<section class="opp-section"><div class="opp-section-head"><span class="opp-number">01</span><div><h2>Identification de l'affaire</h2><p>Client concerné et description de l'opportunité</p></div></div>
<div class="opp-grid">
<div class="opp-full"><?= Form::selectSearch('client_id','Client / prospect',$options,(string)($selectedClient?:''),['required'=>true,'placeholder'=>'Rechercher un client ou prospect...']) ?></div>
<div class="opp-full"><?= Form::input('title','Intitulé de l’opportunité','',['required'=>true,'maxlength'=>180,'placeholder'=>'Ex. Construction d’un bâtiment administratif']) ?></div>
<div class="opp-full"><?= Form::textarea('description','Description et besoins','',['rows'=>5,'maxlength'=>10000,'placeholder'=>'Contexte, périmètre, attentes du client, prochaines étapes...']) ?></div>
</div></section>
<section class="opp-section"><div class="opp-section-head"><span class="opp-number">02</span><div><h2>Suivi commercial et échéances</h2><p>Qualification, valeur prévisionnelle et date limite</p></div></div>
<div class="opp-grid">
<?= Form::selectSearch('stage','Étape commerciale',$stages,'qualification',['required'=>true,'placeholder'=>'Rechercher une étape...']) ?>
<?= Form::input('estimated_amount','Montant estimé (FCFA)','',['type'=>'number','min'=>0,'step'=>'0.01','placeholder'=>'Ex. 25000000']) ?>
<div class="finea-field opp-date-field"><label for="opp-deadline">Date limite / deadline</label><div class="opp-date-control"><input type="date" id="opp-deadline" name="deadline"><button type="button" class="opp-calendar-button" data-opp-calendar aria-label="Ouvrir le calendrier">▦</button></div><small class="opp-hint">Date prévisionnelle de réponse ou de clôture</small></div>
</div></section>
<section class="opp-section"><div class="opp-section-head"><span class="opp-number">03</span><div><h2>Pièces jointes</h2><p>Documents du dossier : cahier des charges, plans, courriers, offres...</p></div></div>
<div class="opp-dropzone" data-opp-dropzone>
<input id="opp-files" type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" data-opp-files>
<div class="opp-dropzone-content"><span class="opp-upload-icon" aria-hidden="true">⇧</span><strong>Glissez vos documents ici</strong><span>ou</span><label for="opp-files" class="opp-file-button">Parcourir les fichiers</label><small>PDF, JPG, PNG, DOCX, XLSX · 5 fichiers maximum · 10 Mo par fichier</small></div>
</div><p class="opp-file-error" data-opp-file-error role="alert" aria-live="polite"></p><div class="opp-file-list" data-opp-file-list aria-live="polite"></div>
</section>
<div class="opp-submit"><a href="<?= $e(View::url('crm/opportunites')) ?>">Annuler</a><button type="submit">Enregistrer l'opportunité</button></div>
</form></div>
<?php return (string)ob_get_clean();
 }
}
