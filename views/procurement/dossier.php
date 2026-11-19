<?php
declare(strict_types=1);
use App\Helpers\View;
use App\View\Components\Ui;
$e=static fn($v)=>View::e((string)$v);
$uploaded=array_column($documents,'document_type');
$requiredCount=count(array_filter($requirements,static fn($r)=>(int)$r['required']===1));
$completedCount=count(array_filter($requirements,static fn($r)=>(int)$r['required']===1&&in_array($r['document_type'],$uploaded,true)));
$completion=$requiredCount?round(100*$completedCount/$requiredCount):100;
$statusLabels=['brouillon'=>'Brouillon','soumis'=>'En cours d’agrément','complement_requis'=>'Complément demandé','approuve'=>'Agréé','refuse'=>'Refusé','suspendu'=>'Suspendu'];
ob_start();
?>
<div class="finea-shell supplier-workspace supplier-dossier"><div class="finea-container">
<?= Ui::pageHeader('Dossier fournisseur','Identification, justificatifs et suivi de la conformité du partenaire.',['eyebrow'=>'FINEA · Fournisseurs & Achats','actions'=>[Ui::button('Retour au répertoire',['href'=>'fournisseurs','variant'=>'secondary']),Ui::button('Nouveau fournisseur',['href'=>'fournisseurs/nouveau','icon'=>'plus'])]]) ?>
<section class="supplier-panel supplier-identity">
 <div class="supplier-identity-top">
  <div class="supplier-avatar"><i class="fa-solid <?= $supplier['person_type']==='physique'?'fa-user':'fa-building' ?>"></i></div>
  <div class="supplier-identity-main"><span class="supplier-eyebrow">FICHE FOURNISSEUR · <?= $e($supplier['supplier_code']) ?></span><h2><?= $e($supplier['legal_name']) ?></h2><p><?= $supplier['person_type']==='physique'?'Personne physique':'Personne morale' ?><?php if($supplier['trade_name']): ?> · <?= $e($supplier['trade_name']) ?><?php endif; ?></p></div>
  <span class="supplier-status supplier-status--<?= $e($supplier['status']) ?>"><?= $e($statusLabels[$supplier['status']]??$supplier['status']) ?></span>
 </div>
 <div class="supplier-identity-meta">
  <div><span>Téléphone</span><strong><?= $e($supplier['phone']?:'Non renseigné') ?></strong></div>
  <div><span>Email</span><strong><?= $e($supplier['email']?:'Non renseigné') ?></strong></div>
  <div><span>Identifiant fiscal</span><strong><?= $e($supplier['tax_identifier']?:'Non renseigné') ?></strong></div>
  <div><span>Pays</span><strong><?= $e($supplier['country_code']) ?></strong></div>
 </div>
</section>
<div class="supplier-kpis supplier-dossier-kpis">
 <div class="supplier-kpi"><i class="fa-solid fa-file-lines"></i><span>Pièces déposées</span><strong><?= count($documents) ?></strong></div>
 <div class="supplier-kpi"><i class="fa-solid fa-list-check"></i><span>Obligatoires présentes</span><strong><?= $completedCount ?> / <?= $requiredCount ?></strong></div>
 <div class="supplier-kpi"><i class="fa-solid fa-chart-pie"></i><span>Complétude documentaire</span><strong><?= $completion ?> %</strong></div>
</div>
<section class="supplier-panel">
 <div class="supplier-panel-heading"><span class="supplier-panel-icon"><i class="fa-solid fa-clipboard-check"></i></span><div><h2>Constitution du dossier</h2><p>Les justificatifs sont adaptés à la nature juridique du fournisseur.</p></div></div>
 <div class="supplier-progress" role="progressbar" aria-label="Complétude des pièces obligatoires" aria-valuenow="<?= $completion ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?= $completion ?>%"></span></div>
 <?php if($missing): ?><div class="supplier-alert supplier-alert--warning"><i class="fa-solid fa-circle-exclamation"></i><div><strong>Dossier incomplet</strong><p>Pièces obligatoires manquantes : <?= $e(implode(', ',$missing)) ?>.</p></div></div><?php else: ?><div class="supplier-alert supplier-alert--success"><i class="fa-solid fa-circle-check"></i><div><strong>Pièces obligatoires présentes</strong><p>Le contrôle de validité des documents reste à effectuer par les approbateurs.</p></div></div><?php endif; ?>
 <div class="supplier-doc-list">
 <?php foreach($requirements as $req): $present=in_array($req['document_type'],$uploaded,true); ?>
  <div class="supplier-doc-row">
   <div class="supplier-doc-icon"><i class="fa-regular fa-file-lines"></i></div>
   <div class="supplier-doc-detail"><strong><?= $e($req['label']) ?></strong><span><?= (int)$req['required']?'Pièce obligatoire':'Pièce facultative' ?></span></div>
   <span class="supplier-doc-state <?= $present?'is-present':'is-missing' ?>"><i class="fa-solid <?= $present?'fa-circle-check':'fa-clock' ?>"></i><?= $present?'Déposée':'À fournir' ?></span>
   <?php if($canUpload): ?>
   <form class="supplier-upload" method="post" enctype="multipart/form-data" action="<?= $e(View::url('fournisseurs/'.$supplier['id'].'/documents')) ?>">
    <input type="hidden" name="_csrf_token" value="<?= $e($csrf) ?>"><input type="hidden" name="document_type" value="<?= $e($req['document_type']) ?>">
    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" aria-label="Fichier : <?= $e($req['label']) ?>" required>
    <button type="submit"><i class="fa-solid fa-cloud-arrow-up"></i> Déposer</button>
   </form>
   <?php endif; ?>
  </div>
 <?php endforeach; ?>
 </div>
 <p class="supplier-upload-hint"><i class="fa-solid fa-shield-halved"></i> Formats acceptés : PDF, JPG, PNG · 10 Mo maximum par fichier · Stockage privé.</p>
</section>
<section class="supplier-panel">
 <div class="supplier-panel-heading"><span class="supplier-panel-icon"><i class="fa-solid fa-folder-open"></i></span><div><h2>Documents enregistrés</h2><p>Historique des pièces transmises dans ce dossier.</p></div></div>
 <?php if(!$documents): ?><div class="supplier-empty"><i class="fa-regular fa-folder-open"></i><strong>Aucun document enregistré</strong><span>Les fichiers déposés apparaîtront ici.</span></div><?php else: ?>
 <div class="supplier-files">
 <?php foreach($documents as $doc): ?><a class="supplier-file" href="<?= $e(View::url('fournisseurs/documents/'.$doc['id'].'/telecharger')) ?>"><span class="supplier-file-icon"><i class="fa-solid fa-file-arrow-down"></i></span><span><strong><?= $e($doc['original_name']) ?></strong><small><?= $e($doc['document_type']) ?> · <?= number_format((int)$doc['file_size']/1024,0,',',' ') ?> Ko · <?= $e($doc['uploaded_at']) ?></small></span><i class="fa-solid fa-download"></i></a><?php endforeach; ?>
 </div><?php endif; ?>
</section>
</div></div>
<style>
.supplier-dossier{--sp-ink:#14233d;--sp-line:#e0e8f1;color:var(--sp-ink)}
.supplier-dossier .supplier-panel{background:#fff;border:1px solid var(--sp-line);border-radius:19px;padding:clamp(18px,3vw,30px);margin:18px 0;box-shadow:0 9px 28px #162e5009}
.supplier-identity-top{display:flex;gap:17px;align-items:center}.supplier-avatar,.supplier-panel-icon{display:grid;place-items:center;flex:none;background:#dff5ef;color:#0f766e;border-radius:14px}.supplier-avatar{width:66px;height:66px;font-size:26px}.supplier-identity-main{flex:1;min-width:0}.supplier-eyebrow{font-size:11px;font-weight:800;letter-spacing:.09em;color:#0f766e}.supplier-identity-main h2{font-size:24px;margin:6px 0}.supplier-identity-main p{margin:0;color:#64748b;font-size:14px}.supplier-status{border-radius:99px;padding:9px 13px;background:#f1f5f9;color:#475569;font-size:12px;font-weight:800;white-space:nowrap}.supplier-status--approuve{background:#dcfce7;color:#166534}.supplier-status--soumis{background:#dbeafe;color:#1d4ed8}.supplier-status--complement_requis{background:#fef3c7;color:#92400e}.supplier-status--refuse,.supplier-status--suspendu{background:#fee2e2;color:#991b1b}
.supplier-identity-meta{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:23px;padding-top:22px;border-top:1px solid var(--sp-line)}.supplier-identity-meta>div{min-width:0;display:grid;gap:6px}.supplier-identity-meta span{font-size:12px;color:#64748b}.supplier-identity-meta strong{font-size:13px;overflow-wrap:anywhere}
.supplier-dossier-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.supplier-dossier .supplier-kpi{border:1px solid var(--sp-line);background:white;border-radius:17px;padding:21px;display:grid;gap:10px}.supplier-dossier .supplier-kpi i{font-size:23px;color:#0f766e}.supplier-dossier .supplier-kpi span{color:#64748b;font-size:13px}.supplier-dossier .supplier-kpi strong{font-size:27px}
.supplier-panel-heading{display:flex;align-items:center;gap:15px;margin-bottom:23px}.supplier-panel-icon{width:49px;height:49px;font-size:21px}.supplier-panel-heading h2{font-size:20px;margin:0 0 6px}.supplier-panel-heading p{font-size:14px;color:#64748b;margin:0}
.supplier-progress{height:8px;background:#e7eef2;border-radius:99px;overflow:hidden;margin-bottom:18px}.supplier-progress span{display:block;height:100%;background:#0f766e;border-radius:99px;transition:width .2s}
.supplier-alert{display:flex;gap:13px;padding:15px 17px;border-radius:12px;margin-bottom:23px;font-size:13px}.supplier-alert i{font-size:19px}.supplier-alert strong{display:block;margin-bottom:4px}.supplier-alert p{margin:0;line-height:1.6}.supplier-alert--warning{background:#fffbeb;color:#92400e}.supplier-alert--success{background:#ecfdf5;color:#065f46}
.supplier-doc-list{border:1px solid var(--sp-line);border-radius:14px;overflow:hidden}.supplier-doc-row{display:flex;align-items:center;gap:15px;padding:16px;border-bottom:1px solid var(--sp-line);flex-wrap:wrap}.supplier-doc-row:last-child{border-bottom:0}.supplier-doc-icon,.supplier-file-icon{width:42px;height:42px;display:grid;place-items:center;flex:none;border-radius:12px;background:#eff6f9;color:#0f766e}.supplier-doc-detail{flex:1;min-width:150px;display:grid;gap:5px}.supplier-doc-detail strong{font-size:14px}.supplier-doc-detail span{font-size:12px;color:#64748b}.supplier-doc-state{font-size:12px;font-weight:700;white-space:nowrap;color:#92400e}.supplier-doc-state i{margin-right:6px}.supplier-doc-state.is-present{color:#047857}.supplier-upload{display:flex;align-items:center;gap:9px;flex-wrap:wrap}.supplier-upload input{max-width:215px;font-size:12px}.supplier-upload button{background:#0f766e;color:#fff;border:0;border-radius:10px;padding:11px 14px;font-weight:700;cursor:pointer;white-space:nowrap}.supplier-upload-hint{font-size:12px;color:#64748b;margin:17px 0 0}.supplier-upload-hint i{color:#0f766e;margin-right:6px}
.supplier-empty{padding:35px;display:grid;place-items:center;gap:9px;color:#64748b;text-align:center}.supplier-empty i{font-size:30px;color:#94a3b8}.supplier-empty strong{color:#34445d}.supplier-files{display:grid;gap:9px}.supplier-file{display:flex;align-items:center;gap:13px;padding:13px;border:1px solid var(--sp-line);border-radius:12px;color:#155e75;text-decoration:none}.supplier-file:hover{background:#f5faf9}.supplier-file>span:nth-child(2){flex:1;min-width:0}.supplier-file strong{display:block;font-size:13px;overflow-wrap:anywhere}.supplier-file small{display:block;font-size:12px;color:#64748b;margin-top:5px}
@media(max-width:900px){.supplier-identity-meta{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:650px){.supplier-identity-top{flex-wrap:wrap}.supplier-status{margin-left:0}.supplier-dossier-kpis{grid-template-columns:1fr}.supplier-identity-meta{grid-template-columns:1fr}.supplier-doc-row{align-items:flex-start}.supplier-upload{width:100%}.supplier-upload input{max-width:100%}}
</style>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';
