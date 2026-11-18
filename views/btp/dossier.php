<?php
declare(strict_types=1);
use App\Helpers\View;
use App\Helpers\Auth;
use App\Security\PermissionEntityRegistry;
$e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$pid=(int)$projectBtp['id'];
$canWrite=Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create');
$eventLabels=['soumission'=>'Soumission au client','demande_client'=>'Demande du client','reponse'=>'Réponse / clarification','negociation'=>'Négociation','reunion'=>'Réunion / compte rendu','decision'=>'Décision','note'=>'Note interne'];
$eventTypes=['soumission'=>'submitted','demande_client'=>'request','reponse'=>'reply','negociation'=>'negotiation','reunion'=>'meeting','decision'=>'decision','note'=>'note'];
$hasQuotes=!empty($quotesBtp);
$hasDocuments=!empty($documentsBtp);
$hasSubmission=false;
foreach($eventsBtp as $ev){if($ev['event_type']==='soumission'){$hasSubmission=true;break;}}
$stages=[
 ['title'=>'Consultation','subtitle'=>'DCE et cadrage','done'=>$hasDocuments],
 
 ['title'=>'Devis & planning','subtitle'=>'Offre prévisionnelle','done'=>$hasQuotes],
 ['title'=>'Validation globale','subtitle'=>'Devis · déboursé · planning','done'=>false],
 ['title'=>'Ouverture chantier','subtitle'=>'BC, OS et lancement','done'=>false],
];
ob_start(); ?>
<div class="btp-shell btp-workspace">
  <nav class="btp-breadcrumb" aria-label="Fil d'Ariane"><a href="<?= $e(View::url('btp/dashboard')) ?>">Projets & chantiers</a><span>/</span><a href="<?= $e(View::url('btp/projets')) ?>">Portefeuille</a><span>/</span><strong><?= $e($projectBtp['reference']) ?></strong></nav>
  <header class="btp-banner btp-workspace-header">
    <div class="btp-heading"><div><small>DOSSIER TECHNIQUE · <?= $e($projectBtp['reference']) ?></small><h1><?= $e($projectBtp['intitule']) ?></h1><p>Maître d'ouvrage : <?= $e($projectBtp['client_nom']) ?></p></div><span class="btp-state"><?= $e($projectBtp['statut']) ?></span></div>
    <nav aria-label="Actions du projet"><a href="#btp-documents">Documents</a><a href="#btp-devis">Devis</a></nav>
  </header>
  <?php if(!empty($errorBtp)): ?><p class="btp-error" role="alert"><?= $e($errorBtp) ?></p><?php endif; ?>
  <section class="btp-progress-card" aria-label="Étapes de préparation du chantier"><div class="btp-section-heading"><div><small>PARCOURS DE PRÉPARATION</small><h2>Du dossier d'appel d'offres au lancement des travaux</h2></div><span class="btp-muted">Les jalons indiquent l'existence des éléments, pas leur validation.</span></div>
    <ol class="btp-stepper"><?php foreach($stages as $i=>$stage): ?><li class="<?= $stage['done']?'is-complete':($i===0||($i>0&&$stages[$i-1]['done'])?'is-current':'is-pending') ?>"><span class="btp-step-number"><?= $stage['done']?'✓':($i+1) ?></span><div><strong><?= $e($stage['title']) ?></strong><small><?= $e($stage['subtitle']) ?></small></div></li><?php endforeach; ?></ol>
    <p class="btp-gate-note">Condition de lancement : devis retenu et validé, déboursé global approuvé, planning de référence approuvé, puis BC/contrat et ordre de service. Ce contrôle bloquant sera implémenté dans le workflow métier.</p>
  </section>
  <div class="btp-workspace-layout">
    <main class="btp-workspace-main">
      <nav class="btp-tabs" aria-label="Sections du dossier"><a href="#btp-consultation">Consultation</a><a href="#btp-documents">Documents</a><a href="#btp-devis">Devis</a><a href="#btp-preparation">Préparation chantier</a></nav>
      <section class="btp-panel btp-section" id="btp-consultation"><div class="btp-section-heading"><div><small>01 · CADRAGE</small><h2>Informations de consultation</h2></div></div><p class="btp-description"><?= nl2br($e($projectBtp['description']??'Aucune description renseignée.')) ?></p><div class="btp-facts"><div><small>Date limite</small><strong><?= $e($projectBtp['deadline']??'Non renseignée') ?></strong></div><div><small>Montant estimatif</small><strong><?= $e($projectBtp['estimated_amount']??'Non renseigné') ?> <?= $e($projectBtp['currency']??'') ?></strong></div></div><?php if(!empty($projectBtp['url_source'])&&filter_var($projectBtp['url_source'],FILTER_VALIDATE_URL)&&in_array(parse_url($projectBtp['url_source'],PHP_URL_SCHEME),['http','https'],true)): ?><a class="btp-inline-link" href="<?= $e($projectBtp['url_source']) ?>" target="_blank" rel="noopener noreferrer">Consulter l'avis officiel ↗</a><?php endif; ?></section>
      <section class="btp-panel btp-section" id="btp-documents"><div class="btp-section-heading"><div><small>03 · PIÈCES & VERSIONS</small><h2>Bibliothèque documentaire</h2></div><span class="btp-count"><?= count($documentsBtp) ?> fichier(s)</span></div><p class="btp-help">Même catégorie et même intitulé : nouvelle version automatique. Aucun document existant n'est écrasé.</p>
        <?php if($canWrite): ?><details class="btp-composer" open><summary>+ Déposer un document</summary><form method="post" enctype="multipart/form-data" action="<?= $e(View::url('btp/projets/'.$pid.'/documents')) ?>"><input type="hidden" name="_csrf" value="<?= $e($csrfBtp) ?>"><div class="btp-form-row"><label>Catégorie<select name="category" data-select-search required><?php foreach(['dce'=>'Dossier de consultation','administratif'=>'Administratif','technique'=>'Technique','financier'=>'Financier','client'=>'Correspondance client','chantier'=>'Chantier','autre'=>'Autre'] as $k=>$v): ?><option value="<?= $e($k) ?>"><?= $e($v) ?></option><?php endforeach; ?></select></label><label>Intitulé du document<input name="label" maxlength="180" placeholder="Ex. DQE lot gros œuvre" required></label></div><div class="btp-upload-zone" data-dropzone><div class="btp-drop-icon" aria-hidden="true">⇧</div><strong>Glissez votre fichier dans cette zone</strong><span>ou cliquez pour le sélectionner</span><small>PDF · JPG · PNG · DOCX · XLSX — 20 Mo maximum</small><input id="btp-document-file" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" aria-label="Sélectionner un document" required><span class="btp-upload-preview" data-file-preview aria-live="polite"></span></div><button type="submit">Enregistrer cette version</button></form></details><?php endif; ?>
        <?php if(empty($documentsBtp)): ?><p class="btp-empty">Aucun document déposé pour le moment.</p><?php else: ?><div class="btp-table-wrap"><table><thead><tr><th>Document</th><th>Catégorie</th><th>Version</th><th>Ajouté le</th><th></th></tr></thead><tbody><?php foreach($documentsBtp as $doc): ?><tr><td><strong><?= $e($doc['label']) ?></strong></td><td><?= $e($doc['category']) ?></td><td><span class="btp-version">v<?= (int)$doc['version'] ?></span></td><td><?= $e($doc['created_at']) ?></td><td><a href="<?= $e(View::url('btp/projets/'.$pid.'/documents/'.(int)$doc['id'])) ?>">Télécharger</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
      </section>
      <section class="btp-panel btp-section" id="btp-devis"><div class="btp-section-heading"><div><small>04 · CHIFFRAGE</small><h2>Historique des devis</h2></div><span class="btp-count"><?= count($quotesBtp) ?> révision(s)</span></div><p class="btp-help">Ce formulaire enregistre une estimation versionnée. Le devis détaillé avec lignes, validation et PDF constitue l'étape métier suivante.</p>
        <?php if($canWrite): ?><details class="btp-composer"><summary>+ Créer une estimation révisée</summary><form method="post" action="<?= $e(View::url('btp/projets/'.$pid.'/devis')) ?>"><input type="hidden" name="_csrf" value="<?= $e($csrfBtp) ?>"><div class="btp-form-row"><label>Intitulé<input name="label" maxlength="180" required></label><label>Montant HT (FCFA)<input name="amount_ht" inputmode="decimal" pattern="[0-9]+(\.[0-9]{1,2})?" placeholder="15000000.00" required></label></div><button type="submit">Enregistrer la révision</button></form></details><?php endif; ?>
        <?php if(empty($quotesBtp)): ?><p class="btp-empty">Aucun chiffrage enregistré.</p><?php else: ?><div class="btp-quote-list"><?php foreach($quotesBtp as $quote): ?><article><div><span class="btp-version">Version <?= (int)$quote['revision'] ?></span><strong><?= $e($quote['label']) ?></strong><small><?= $e($quote['status']) ?></small></div><b><?= number_format((float)$quote['amount_ht'],2,',',' ') ?> FCFA HT</b></article><?php endforeach; ?></div><?php endif; ?>
      </section>
      <section class="btp-panel btp-section" id="btp-preparation"><div class="btp-section-heading"><div><small>05 · PRÉPARATION</small><h2>Dossier de lancement chantier</h2></div></div><div class="btp-readiness"><div><span>Devis contractuel validé</span><strong>À formaliser</strong></div><div><span>Déboursé global approuvé</span><strong>À formaliser</strong></div><div><span>Planning de référence approuvé</span><strong>À formaliser</strong></div><div><span>BC / contrat et ordre de service</span><strong>À formaliser</strong></div></div><p class="btp-gate-note">La génération du chantier reste indisponible tant que les validations et les contrôles serveur ne sont pas implémentés. Aucun lancement fictif n'est proposé.</p></section>
    </main>
    <aside class="btp-workspace-aside"><section class="btp-panel"><small>VUE D'ENSEMBLE</small><h2>État du dossier</h2><div class="btp-aside-metrics"><div><strong><?= count($documentsBtp) ?></strong><span>Documents</span></div><div><strong><?= count($quotesBtp) ?></strong><span>Révisions</span></div></div><p class="btp-help">Référence : <?= $e($projectBtp['reference']) ?></p><a class="btp-inline-link" href="<?= $e(View::url('btp/projets')) ?>">← Retour au portefeuille</a></section></aside>
  </div>
</div>
<?php $content=ob_get_clean();$additionalScripts=array_merge($additionalScripts??[],['js/components/form-components.js','js/components/btp-dossier.js']);require BASE_PATH.'/views/layouts/module.php';
