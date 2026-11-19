<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\WizardSteps;
$e = static fn(mixed $v):string => View::e((string)$v);
$scope = ($febData['scope'] ?? 'chantier') === 'bureau' ? 'bureau' : 'chantier';
$chantiers = $febData['chantiers'] ?? [];
$selectedChantier = (int)($febData['selectedChantier'] ?? 0);
$lines = $febData['lines'] ?? [];
$workflows = $febData['workflows'] ?? [];
$services = $febData['officeServices'] ?? [];
$allocations = $febData['officeAllocations'] ?? [];
$csrf = $page->csrfToken;
$quoteLines = [];
$budgets = [];
foreach($lines as $line){
 $quoteId = (int)$line['quote_line_id'];
 $quoteLines[$quoteId] = (string)$line['description'];
 $budgetId = (int)$line['budget_id'];
 $budgets[$budgetId] = 'Déboursé validé #'.$budgetId;
}
$ready = $scope === 'bureau' ? count($allocations)>0 : $selectedChantier>0 && count($lines)>0;
?>
<div class="feb-employee-create btp-workspace btp-chantier-wizard">
 <?php if(!empty($febData['setupError'])): ?><p class="feb-budget-warning" role="alert"><?= $e($febData['setupError']) ?></p><?php endif; ?>
 <div class="feb-scope-choice" aria-label="Type de fiche de besoin">
  <a class="<?= $scope==='chantier'?'is-selected':'' ?>" href="<?= $e(View::url('espace-employe/demandes/nouvelle?type=feb&scope=chantier')) ?>"><i class="fa-solid fa-helmet-safety"></i><strong>FEB Chantier</strong><small>Projet et déboursé d’exécution</small></a>
  <a class="<?= $scope==='bureau'?'is-selected':'' ?>" href="<?= $e(View::url('espace-employe/demandes/nouvelle?type=feb&scope=bureau')) ?>"><i class="fa-solid fa-building"></i><strong>FEB Bureau</strong><small>Service et budget administratif</small></a>
 </div>
 <?php if($scope==='chantier'): ?>
 <form method="get" action="<?= $e(View::url('espace-employe/demandes/nouvelle')) ?>" class="feb-chantier-selection">
  <input type="hidden" name="type" value="feb"><input type="hidden" name="scope" value="chantier">
  <label>Chantier concerné
   <select name="chantier" required>
    <option value="">Sélectionnez un chantier autorisé…</option>
    <?php foreach($chantiers as $c): ?>
     <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id']===$selectedChantier?'selected':'' ?>><?= $e($c['code'].' — '.$c['nom']) ?></option>
    <?php endforeach; ?>
   </select>
  </label>
  <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Charger l’affectation budgétaire</button>
 </form>
 <?php if(!$chantiers): ?><p class="feb-budget-warning" role="status">Aucun chantier FEB accessible pour l’utilisateur connecté (ID <?= (int)($febData['userId']??0) ?>). Vérifiez que cet identifiant figure dans les affectations « demander » et que son budget est validé.</p><?php endif; ?>
 <?php if($selectedChantier&&!$lines): ?><p class="feb-budget-warning">Ce chantier ne contient aucune ligne de déboursé exploitable.</p><?php endif; ?>
 <?php else: ?>
 <p class="feb-budget-info">Pour une FEB de fonctionnement, sélectionnez d’abord le service bénéficiaire, puis une affectation financière budgétisée.</p>
 <?php if(!$services): ?><p class="feb-budget-warning">Aucun service RH actif n’est configuré.</p><?php endif; ?>
 <?php if(!$allocations): ?><p class="feb-budget-warning">Aucun poste budgétaire Bureau disponible pour l’exercice en cours. L’administrateur doit définir le budget du service avant toute demande.</p><?php endif; ?>
 <?php endif; ?>
 <?php if($ready && $workflows): ?>
 <?= WizardSteps::render([
 ['label'=>'Affectation','icon'=>'fa-solid fa-layer-group'],
 ['label'=>'Besoins','icon'=>'fa-solid fa-list-check'],
 ['label'=>'Bénéficiaire','icon'=>'fa-solid fa-user-check'],
 ['label'=>'Justificatif','icon'=>'fa-solid fa-file-arrow-up'],
 ['label'=>'Validation finale','icon'=>'fa-solid fa-flag-checkered'],
 ], 'Étapes de la FEB') ?>
 <form method="post" id="feb-wizard-form" novalidate enctype="multipart/form-data" action="<?= $e(View::url('feb')) ?>" class="feb-create-form" data-scope="<?= $scope ?>">
 <input type="hidden" name="_csrf_token" value="<?= $e($csrf) ?>">
 <input type="hidden" name="scope" value="<?= $scope ?>">
 <input type="hidden" name="chantier_id" value="<?= $scope==='chantier'?$selectedChantier:0 ?>">
 <section class="btp-panel btp-section" data-wizard-step="0">
  <h2><i class="fa-solid fa-layer-group"></i> Affectation financière — <?= $scope==='chantier'?'chantier':'bureau' ?></h2>
  <?php if($scope==='chantier'): ?>
   <p><strong>Chantier sélectionné :</strong> <?php foreach($chantiers as $c)if((int)$c['id']===$selectedChantier)echo $e($c['code'].' — '.$c['nom']); ?></p>
   <p>Chaque besoin sera associé à la chaîne <strong>ligne de devis → déboursé validé → ligne de déboursé</strong> à l’étape suivante.</p>
  <?php else: ?>
   <div class="feb-create-grid">
    <label>Service bénéficiaire
     <select class="feb-field" name="service_id" id="feb-office-service" required>
      <option value="">Sélectionner un service</option>
      <?php foreach($services as $service): ?><option value="<?= (int)$service['id'] ?>"><?= $e($service['name']) ?></option><?php endforeach; ?>
     </select>
    </label>
    <label>Affectation budgétaire
     <select class="feb-field" name="office_allocation_id" id="feb-office-allocation" required>
      <option value="">Sélectionner un poste budgété</option>
      <?php foreach($allocations as $allocation): ?><option value="<?= (int)$allocation['id'] ?>" data-service="<?= (int)$allocation['service_id'] ?>" data-budget="<?= $e($allocation['budget_amount']) ?>"><?= $e($allocation['code'].' — '.$allocation['label'].' · '.number_format((float)$allocation['budget_amount'],0,',',' ').' FCFA') ?></option><?php endforeach; ?>
     </select>
    </label>
   </div>
   <p class="feb-budget-info">Les montants demandés seront contrôlés au regard du crédit annuel disponible, en tenant compte des autres FEB engagées.</p>
  <?php endif; ?>
  <label>Circuit de validation
   <select class="feb-field" name="workflow_id" required>
    <option value="">Sélectionner un circuit…</option>
    <?php foreach($workflows as $w): ?><option value="<?= (int)$w['id'] ?>" <?= count($workflows)===1?'selected':'' ?>><?= $e($w['label']) ?></option><?php endforeach; ?>
   </select>
  </label>
 </section>
 <section class="btp-panel btp-section" data-wizard-step="1" hidden>
  <h2><i class="fa-solid fa-list-check"></i> Lignes de besoin</h2>
  <p><?= $scope==='chantier'?'Rattachez chaque besoin à son devis et à sa ligne de déboursé validée.':'Décrivez les besoins financés par l’affectation du service.' ?></p>
  <div class="feb-lines" id="feb-lines">
   <?php for($n=0;$n<3;$n++): ?>
   <fieldset class="feb-line-card" data-feb-line>
    <legend>Besoin <?= $n+1 ?><?= $n?' · facultatif':'' ?></legend>
    <?php if($scope==='chantier'): ?>
    <div class="feb-create-grid">
     <label>Ligne de devis
      <select class="feb-field" name="lines[<?= $n ?>][quote_line_id]" data-quote-line>
       <option value="">Sélectionner une ligne de devis</option>
       <?php foreach($quoteLines as $id=>$name): ?><option value="<?= $id ?>"><?= $e($name) ?></option><?php endforeach; ?>
      </select>
     </label>
     <label>Déboursé
      <select class="feb-field" name="lines[<?= $n ?>][debourse_id]" data-debourse>
       <option value="">Sélectionner un déboursé validé</option>
       <?php foreach($budgets as $id=>$label): ?><option value="<?= $id ?>"><?= $e($label) ?></option><?php endforeach; ?>
      </select>
     </label>
     <label>Ligne de déboursé
      <select class="feb-field" name="lines[<?= $n ?>][budget_line_id]" data-budget-line>
       <option value="">Sélectionner un poste de dépense</option>
       <?php foreach($lines as $l): ?><option value="<?= (int)$l['id'] ?>" data-quote="<?= (int)$l['quote_line_id'] ?>" data-debourse="<?= (int)$l['budget_id'] ?>"><?= $e($l['description'].' — '.number_format((float)$l['total_cost'],0,',',' ').' FCFA') ?></option><?php endforeach; ?>
      </select>
     </label>
    </div>
    <?php endif; ?>
    <div class="feb-create-grid">
     <label>Désignation<input class="feb-field" name="lines[<?= $n ?>][description]" <?= $n===0?'required':'' ?> placeholder="Fourniture, prestation, main-d’œuvre…"></label>
     <label>Quantité<input class="feb-field feb-quantity" name="lines[<?= $n ?>][quantity]" type="number" min="0.001" step="0.001" value="<?= $n===0?'1':'' ?>"></label>
     <label>Unité<input class="feb-field" name="lines[<?= $n ?>][unit]" value="u" maxlength="30"></label>
     <label>Prix unitaire (FCFA)<input class="feb-field feb-price" name="lines[<?= $n ?>][unit_amount]" type="number" min="0" step="0.01"></label>
    </div>
    <p class="feb-line-total">Sous-total : <strong data-feb-line-total>0 FCFA</strong></p>
   </fieldset>
   <?php endfor; ?>
  </div>
  <div class="feb-grand-total"><span>Montant estimé</span><strong id="feb-grand-total">0 FCFA</strong></div>
 </section>
 <section class="btp-panel btp-section" data-wizard-step="2" hidden>
  <h2><i class="fa-solid fa-user-check"></i> Bénéficiaire et motif</h2>
  <div class="feb-create-grid">
   <label>Type de bénéficiaire<select class="feb-field" name="beneficiary_type" required><option value="fournisseur">Fournisseur</option><option value="beneficiaire">Autre bénéficiaire</option></select></label>
   <label>Nom du bénéficiaire<input class="feb-field" name="beneficiary_name" required maxlength="180"></label>
   <label>Date souhaitée<input class="feb-field" name="needed_at" type="date" min="<?= date('Y-m-d') ?>" required></label>
   <label>Coordonnées ou détails<input class="feb-field" name="beneficiary_details" maxlength="500"></label>
  </div>
  <label>Motif détaillé<textarea class="feb-field" name="purpose" rows="4" required></textarea></label>
 </section>
 <section class="btp-panel btp-section" data-wizard-step="3" hidden>
  <h2><i class="fa-solid fa-file-arrow-up"></i> Justificatif de la FEB</h2>
  <p>Joindre un devis, une proforma, une facture ou un justificatif admissible.</p>
  <label>Document (PDF, JPG, PNG, DOCX — 10 Mo)
   <input class="feb-field" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,.docx" required>
  </label>
 </section>
 <section class="btp-panel btp-section" data-wizard-step="4" hidden>
  <h2><i class="fa-solid fa-flag-checkered"></i> Vérification finale</h2>
  <div id="feb-wizard-review" aria-live="polite"></div>
  <p>Ce bouton enregistre un brouillon. La transmission au circuit d’approbation se fait ensuite depuis la FEB enregistrée.</p>
  <button type="submit" class="feb-submit"><i class="fa-solid fa-floppy-disk"></i> Enregistrer le brouillon FEB</button>
 </section>
 <div class="btp-wizard-controls">
  <button type="button" data-wizard-prev><i class="fa-solid fa-arrow-left"></i> Précédent</button>
  <span data-wizard-progress>Étape 1 / 5</span>
  <button type="button" data-wizard-next>Suivant <i class="fa-solid fa-arrow-right"></i></button>
 </div>
 </form>
 <?php elseif(!$workflows): ?>
 <p class="feb-budget-warning">Aucun circuit FEB actif. Configurez-en un dans l’administration avant de créer la demande.</p>
 <?php endif; ?>
</div>
<style>
.feb-employee-create{min-width:0}.feb-scope-choice{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-bottom:20px}.feb-scope-choice a{display:grid;gap:5px;padding:18px;border:1px solid #d4dfed;border-radius:16px;color:#233a57;text-decoration:none;background:#fff}.feb-scope-choice a i{font-size:25px;color:#3567b7}.feb-scope-choice a small{font-size:12px;color:#64748b}.feb-scope-choice a.is-selected{border:2px solid #2563eb;background:#eff6ff}.feb-chantier-selection{display:flex;flex-wrap:wrap;align-items:end;gap:14px;border:1px solid #dbe4f0;background:#fff;border-radius:14px;padding:16px;margin-bottom:18px}.feb-chantier-selection label{flex:1 1 260px}.feb-employee-create label{display:grid;gap:6px;font-weight:600;color:#344457;margin:12px 0}.feb-employee-create select,.feb-employee-create input,.feb-employee-create textarea{min-height:44px;width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#172337;font:inherit}.feb-employee-create button[type=submit]{border:0;background:#24519b;color:white;padding:12px 17px;border-radius:9px;cursor:pointer}.feb-employee-create button:disabled{opacity:.5;cursor:not-allowed}.feb-create-form{display:grid;gap:14px}.feb-create-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px 18px}.feb-line-card{border:1px solid #dbe4f0;border-radius:14px;padding:16px;margin:14px 0;background:#fff}.feb-line-card legend{color:#24519b;font-weight:700;padding:0 8px}.feb-grand-total{display:flex;justify-content:space-between;gap:16px;padding:16px;background:#eff6ff;border-radius:13px;font-weight:700}.feb-line-total{text-align:right;color:#64748b}.feb-budget-warning{border:1px solid #f3cb80;background:#fffbeb;color:#92400e;padding:15px;border-radius:12px;margin:14px 0}.feb-budget-info{padding:12px;background:#eff6ff;border-radius:10px;color:#1e40af}.feb-employee-create [data-wizard-step][hidden]{display:none!important}.feb-employee-create .btp-wizard-steps{grid-template-columns:repeat(5,minmax(0,1fr))}@media(max-width:760px){.feb-create-grid,.feb-scope-choice{grid-template-columns:1fr}.feb-employee-create .btp-wizard-steps{grid-template-columns:repeat(3,minmax(0,1fr))}}
</style>
