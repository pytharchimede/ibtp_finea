<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\Ui;
$e=static fn($v):string=>View::e((string)$v);
$states=['brouillon'=>['Brouillon','neutral'],'soumis'=>['Soumise','info'],'complement'=>['Complément demandé','warning'],'valide'=>['Validée','success'],'refuse'=>['Refusée','danger'],'annule'=>['Annulée','neutral'],'partiellement_paye'=>['Paiement partiel','warning'],'paye'=>['Payée','success']];
ob_start();
?>
<div class="finea-shell feb-employee-list"><div class="finea-container">
<?=Ui::pageHeader('Mes fiches d’expression de besoin','Consultez vos demandes, leurs décisions et leur avancement jusqu’au paiement.',['eyebrow'=>'FINEA · Suivi des FEB','actions'=>$canCreate?[Ui::button('Nouvelle FEB',['href'=>'feb/nouvelle','variant'=>'primary','icon'=>'plus'])]:[]])?>
<?php if(!$requests): ?><?= Ui::emptyState('Aucune FEB à afficher','Créez votre première demande depuis l’Espace employé.') ?><?php else: ?>
<div class="feb-approval-cards">
<?php foreach($requests as $r):
 $state=$states[(string)$r['status']]??[(string)$r['status'],'neutral'];
 $canAct=(bool)($r['can_decide']??false);
?>
<article class="feb-approval-card">
 <div class="feb-card-head"><div><small>FICHE DE BESOIN · <?= $e($r['reference']?:'#'.$r['id']) ?></small><h2><?= $e($r['reference']?:'FEB #'.$r['id']) ?></h2></div><?= Ui::badge($state[0],$state[1]) ?></div>
 <div class="feb-card-meta"><span><i class="fa-solid fa-helmet-safety"></i> <?= $e($r['chantier_code'].' · '.$r['chantier_name']) ?></span><span><i class="fa-solid fa-user"></i> <?= $e($r['requester_name']) ?></span><span><i class="fa-solid fa-hand-holding-dollar"></i> <?= $e($r['beneficiary_name']) ?></span></div>
 <div class="feb-card-amount"><span>Montant demandé</span><strong><?= number_format((float)$r['total_amount'],0,',',' ') ?> FCFA</strong></div>
 <div class="feb-card-actions">
  <?= Ui::button('Consulter',['href'=>'feb/'.(int)$r['id'],'variant'=>'secondary','icon'=>'search']) ?>
  <?php if($canAct): ?>
  <button type="button" class="feb-action feb-action--approve" data-feb-decision="<?= (int)$r['id'] ?>" data-decision="approuve">Approuver</button>
  <button type="button" class="feb-action feb-action--other" data-feb-decision="<?= (int)$r['id'] ?>" data-decision="complement">Demander complément</button>
  <button type="button" class="feb-action feb-action--other" data-feb-decision="<?= (int)$r['id'] ?>" data-decision="reporte">Reporter</button>
  <button type="button" class="feb-action feb-action--reject" data-feb-decision="<?= (int)$r['id'] ?>" data-decision="refuse">Refuser</button>
  <?php endif; ?>
 </div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<dialog id="feb-decision-dialog" class="feb-decision-dialog"><form method="post" id="feb-decision-form"><input type="hidden" name="_csrf_token" value="<?= $e($csrf??'') ?>"><input type="hidden" name="decision" id="feb-decision-value"><h2 id="feb-decision-title">Décision</h2><p id="feb-decision-description"></p><label id="feb-report-date-wrap" hidden>Date de report<input type="date" name="report_date" id="feb-report-date"></label><label id="feb-comment-wrap">Motif obligatoire<textarea name="comment" id="feb-decision-comment" required minlength="3" rows="4" placeholder="Précisez votre décision"></textarea></label><div class="feb-dialog-actions"><button type="button" id="feb-dialog-close">Annuler</button><button type="submit">Confirmer</button></div></form></dialog>
</div></div>
<style>.feb-employee-list .finea-section-card{border:1px solid #dbe4f0;border-radius:17px;padding:18px;margin:18px 0}.feb-employee-list .finea-table-wrap{overflow-x:auto}.feb-employee-list .finea-action-btn{display:inline-flex;align-items:center;justify-content:center}.feb-employee-list .finea-btn-content{display:inline-flex;align-items:center;justify-content:center;gap:8px}.feb-employee-list .finea-btn-icon{flex-shrink:0}.feb-approval-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin:22px 0}@media(max-width:1350px){.feb-approval-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:760px){.feb-approval-cards{grid-template-columns:1fr}}.feb-approval-card{background:#fff;border:1px solid #dbe4f0;border-radius:18px;padding:23px;box-shadow:0 8px 24px #152d4c0a;display:flex;flex-direction:column;gap:18px}.feb-card-head{display:flex;justify-content:space-between;gap:12px;align-items:start}.feb-card-head small{font-size:11px;font-weight:800;color:#64748b}.feb-card-head h2{font-size:19px;margin:8px 0 0}.feb-card-meta{display:grid;gap:10px;color:#475569;font-size:13px}.feb-card-meta i{color:#0f766e;width:20px}.feb-card-amount{border-top:1px solid #e2e8f0;padding-top:16px;display:grid;gap:5px}.feb-card-amount span{font-size:12px;color:#64748b}.feb-card-amount strong{font-size:24px}.feb-card-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:auto}.feb-card-actions form{margin:0}.feb-action{border:0;border-radius:9px;padding:10px 12px;font-weight:700;cursor:pointer;font-size:12px}.feb-action--approve{background:#dcfce7;color:#166534}.feb-action--other{background:#e0f2fe;color:#075985}.feb-action--reject{background:#fee2e2;color:#991b1b}.feb-decision-dialog{border:1px solid #dbe4f0;border-radius:17px;padding:25px;width:min(92vw,480px)}.feb-decision-dialog::backdrop{background:#0f172a88}.feb-decision-dialog label{display:grid;gap:10px}.feb-decision-dialog textarea,.feb-decision-dialog input[type=date]{padding:12px;border:1px solid #cbd5e1;border-radius:10px;width:100%}.feb-decision-dialog label{margin-top:12px}.feb-decision-dialog [hidden]{display:none}.feb-dialog-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px}.feb-dialog-actions button{padding:10px 15px;border-radius:9px;border:1px solid #cbd5e1;cursor:pointer}.feb-dialog-actions button[type=submit]{background:#0f766e;color:#fff;border:0}
</style>
<script>
(()=>{const dialog=document.querySelector('#feb-decision-dialog'),form=document.querySelector('#feb-decision-form');if(!dialog||!form)return;document.querySelectorAll('[data-feb-decision]').forEach(btn=>btn.addEventListener('click',()=>{form.action=<?= json_encode(View::url('feb/')) ?>+btn.dataset.febDecision+'/decision';form.querySelector('#feb-decision-value').value=btn.dataset.decision;form.querySelector('#feb-decision-title').textContent=btn.textContent;const action=btn.dataset.decision;const dateWrap=form.querySelector('#feb-report-date-wrap'),date=form.querySelector('#feb-report-date'),commentWrap=form.querySelector('#feb-comment-wrap'),comment=form.querySelector('#feb-decision-comment');dateWrap.hidden=action!=='reporte';date.required=action==='reporte';date.value='';date.min=new Date(Date.now()-new Date().getTimezoneOffset()*60000).toISOString().slice(0,10);commentWrap.hidden=action==='approuve';comment.required=action!=='approuve';comment.value='';form.querySelector('#feb-decision-description').textContent=action==='approuve'?'Confirmez-vous l’approbation de cette FEB ?':action==='refuse'?'Confirmez-vous le refus de cette FEB ? Indiquez le motif.':action==='reporte'?'Indiquez la nouvelle date et le motif du report.':'Indiquez les compléments attendus.';dialog.showModal();}));document.querySelector('#feb-dialog-close').addEventListener('click',()=>dialog.close());})();
</script>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';
