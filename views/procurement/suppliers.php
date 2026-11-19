<?php
declare(strict_types=1);
use App\Helpers\View;use App\View\Components\Ui;
$e=static fn($v)=>View::e((string)$v);
$counts=array_count_values(array_column($suppliers,'status'));
ob_start();
?>
<div class="finea-shell supplier-workspace"><div class="finea-container">
<?= Ui::pageHeader(match($section){'dashboard'=>'Pilotage des fournisseurs','nouveau'=>'Référencer un fournisseur','circuits'=>'Paramétrage des agréments',default=>'Répertoire fournisseurs'},'Référencement, contrôle administratif et agrément des partenaires IBTP.',['eyebrow'=>'FINEA · Fournisseurs & Achats','actions'=>[Ui::button('Nouveau fournisseur',['href'=>'fournisseurs/nouveau','icon'=>'plus']),Ui::button('Répertoire',['href'=>'fournisseurs','variant'=>'secondary'])]]) ?>
<?php if($section==='dashboard'): ?>
<div class="supplier-kpis">
<?php foreach([['Total fournisseurs',count($suppliers),'fa-building'],['À approuver',$counts['soumis']??0,'fa-clock'],['Agréés',$counts['approuve']??0,'fa-circle-check'],['Compléments requis',$counts['complement_requis']??0,'fa-file-circle-exclamation']] as $kpi): ?>
<div class="supplier-kpi"><i class="fa-solid <?= $e($kpi[2]) ?>"></i><span><?= $e($kpi[0]) ?></span><strong><?= (int)$kpi[1] ?></strong></div>
<?php endforeach; ?>
</div>
<div class="supplier-panel"><h2>Accès rapide</h2><div class="supplier-shortcuts"><a href="<?= $e(View::url('fournisseurs')) ?>">Consulter les dossiers <i class="fa-solid fa-arrow-right"></i></a><a href="<?= $e(View::url('fournisseurs/nouveau')) ?>">Créer un fournisseur <i class="fa-solid fa-arrow-right"></i></a><?php if($isAdmin): ?><a href="<?= $e(View::url('fournisseurs/circuits')) ?>">Configurer les circuits <i class="fa-solid fa-arrow-right"></i></a><?php endif; ?></div></div>
<?php elseif($section==='nouveau'): ?>
<div class="supplier-panel"><div class="supplier-panel-heading"><span class="supplier-panel-icon"><i class="fa-solid fa-address-card"></i></span><div><h2>Identification du fournisseur</h2><p>Complétez les informations de la personne physique ou morale. Le dossier sera créé en brouillon.</p></div></div>
<form method="post" action="<?= $e(View::url('fournisseurs')) ?>">
 <input type="hidden" name="_csrf_token" value="<?= $e($csrf) ?>">
 <input type="hidden" name="person_type" id="supplier-person-type" value="morale">
 <div class="supplier-type-tabs" role="tablist" aria-label="Nature du fournisseur">
  <button type="button" class="supplier-type-tab is-active" role="tab" id="supplier-tab-morale" aria-selected="true" aria-controls="supplier-fields-morale" data-supplier-type="morale"><i class="fa-solid fa-building"></i><span><strong>Personne morale</strong><small>Société, entreprise ou organisation</small></span></button>
  <button type="button" class="supplier-type-tab" role="tab" id="supplier-tab-physique" aria-selected="false" aria-controls="supplier-fields-physique" data-supplier-type="physique"><i class="fa-solid fa-user"></i><span><strong>Personne physique</strong><small>Indépendant, artisan ou prestataire individuel</small></span></button>
 </div>
 <div class="supplier-type-panel" id="supplier-fields-morale" role="tabpanel" aria-labelledby="supplier-tab-morale">
  <div class="supplier-section-label"><i class="fa-solid fa-building-columns"></i> Informations de l'entreprise</div>
  <div class="supplier-grid">
   <label>Raison sociale <span aria-hidden="true">*</span><input name="company_name" data-required-for="morale" required maxlength="200" placeholder="Dénomination légale"></label>
   <label>Nom commercial<input name="trade_name" maxlength="200" placeholder="Enseigne ou marque"></label>
   <label>Nom du représentant / contact<input name="contact_name" maxlength="180" placeholder="Interlocuteur principal"></label>
   <label>NCC / Identifiant fiscal<input name="tax_identifier" maxlength="100"></label>
   <label>RCCM / Registre du commerce<input name="trade_register" maxlength="100"></label>
  </div>
  <p class="supplier-note"><i class="fa-solid fa-circle-info"></i> Les justificatifs de constitution, d'identification fiscale et les pièces bancaires seront contrôlés dans le dossier d'agrément.</p>
 </div>
 <div class="supplier-type-panel" id="supplier-fields-physique" role="tabpanel" aria-labelledby="supplier-tab-physique" hidden>
  <div class="supplier-section-label"><i class="fa-solid fa-id-card"></i> Identité du prestataire</div>
  <div class="supplier-grid">
   <label>Nom et prénoms <span aria-hidden="true">*</span><input name="individual_name" data-required-for="physique" disabled maxlength="200" placeholder="Nom complet du prestataire"></label>
   <label>Activité / spécialité<input name="individual_activity" disabled maxlength="200" placeholder="Ex. Électricien, maçon, consultant"></label>
   <label>NCC / Identifiant fiscal (si applicable)<input name="individual_tax_identifier" disabled maxlength="100"></label>
   <label>Registre du commerce (si applicable)<input name="individual_trade_register" disabled maxlength="100"></label>
  </div>
  <p class="supplier-note"><i class="fa-solid fa-circle-info"></i> Une pièce d'identité valide et les autres justificatifs applicables à l'activité seront examinés avant l'agrément.</p>
 </div>
 <div class="supplier-section-label"><i class="fa-solid fa-address-book"></i> Coordonnées et règlement</div>
 <div class="supplier-grid">
  <label>Email<input type="email" name="email" maxlength="190"></label>
  <label>Téléphone<input name="phone" maxlength="40"></label>
  <label>Adresse<input name="address" maxlength="500"></label>
  <label>Pays (code ISO)<input name="country_code" value="CI" maxlength="2"></label>
  <label>Banque<input name="bank_name" maxlength="150"></label>
  <label>RIB / IBAN<input name="bank_iban" maxlength="120"></label>
 </div><p><button type="submit">Créer le dossier fournisseur</button></p>
 </form>
</div>
<?php elseif($section==='circuits'): ?>
<div class="supplier-panel"><div class="supplier-panel-heading"><span class="supplier-panel-icon"><i class="fa-solid fa-diagram-project"></i></span><div><h2>Circuits d’approbation</h2><p>Définissez les approbateurs dans l’ordre de passage. Un seul circuit est actif à la fois.</p></div></div>
<form method="post" action="<?= $e(View::url('fournisseurs/circuits')) ?>">
 <input type="hidden" name="_csrf_token" value="<?= $e($csrf) ?>">
 <label>Nom du circuit<input name="label" required placeholder="Ex. Achats → Finance → Direction générale"></label>
 <p>Choisissez les approbateurs dans l'ordre. Laissez les étapes supplémentaires vides.</p>
 <div class="supplier-grid"><?php for($i=0;$i<5;$i++): ?>
 <label>Étape <?= $i+1 ?><select name="assignees[]"><option value="">Aucune</option><?php foreach($users as $user): ?><option value="<?= (int)$user['id'] ?>"><?= $e($user['full_name']) ?></option><?php endforeach; ?></select></label>
 <?php endfor; ?></div><p><button type="submit">Créer le circuit</button></p></form>
 <?php foreach($workflows as $workflow): ?>
 <div class="supplier-actions"><strong><?= $e($workflow['label']) ?></strong><small><?= (int)$workflow['active']?'Actif':'Inactif' ?></small>
 <?php if(!(int)$workflow['active']): ?><form method="post" action="<?= $e(View::url('fournisseurs/circuits/'.$workflow['id'].'/activer')) ?>"><input type="hidden" name="_csrf_token" value="<?= $e($csrf) ?>"><button class="secondary">Activer</button></form><?php endif; ?></div>
 <?php endforeach; ?>
</div>
<?php else: ?>
<div class="supplier-panel"><div class="supplier-panel-heading"><span class="supplier-panel-icon"><i class="fa-solid fa-list-check"></i></span><div><h2>Dossiers fournisseurs</h2><p>Les fournisseurs sont sélectionnables dans les FEB uniquement après agrément final.</p></div></div>
<div style="overflow-x:auto"><table><thead><tr><th>Fournisseur</th><th>Type</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
 <?php foreach($suppliers as $supplier): ?>
 <tr><td><strong><?= $e($supplier['legal_name']) ?></strong><div><small><?= $e($supplier['supplier_code']) ?> · <?= $e($supplier['email']) ?></small></div></td><td><?= $e($supplier['person_type']) ?></td><td class="status"><?= $e(str_replace('_',' ',$supplier['status'])) ?></td><td>
 <div class="supplier-actions">
 <?php if(in_array($supplier['status'],['brouillon','complement_requis'],true)&&(int)$supplier['created_by']===(int)\App\Helpers\Auth::id()): ?>
 <form method="post" action="<?= $e(View::url('fournisseurs/'.$supplier['id'].'/soumettre')) ?>"><input type="hidden" name="_csrf_token" value="<?= $e($csrf) ?>"><button>Soumettre</button></form>
 <?php endif; ?>
 <?php if($supplier['status']==='soumis'): ?>

 <form method="post" action="<?= $e(View::url('fournisseurs/'.$supplier['id'].'/decision')) ?>" class="supplier-actions">
 <input type="hidden" name="_csrf_token" value="<?= $e($csrf) ?>">
 <select name="decision"><option value="approuve">Approuver l'étape</option><option value="complement_requis">Demander un complément</option><option value="refuse">Refuser</option></select>
 <input name="comment" placeholder="Commentaire / motif"><button>Décider</button>
 </form>
 <?php endif; ?>
 </div></td></tr>
 <?php endforeach; ?>
 <?php if(!$suppliers): ?><tr><td colspan="4">Aucun fournisseur enregistré.</td></tr><?php endif; ?>
 </tbody></table></div>
</div>
<?php endif; ?>
</div></div>
<style>
.supplier-workspace{--sp-ink:#14233d;--sp-line:#e0e8f1;color:var(--sp-ink)}.supplier-workspace .finea-page-header{margin-bottom:23px}
.supplier-panel{background:#fff;border:1px solid var(--sp-line);border-radius:19px;padding:clamp(18px,3vw,30px);margin:18px 0;box-shadow:0 9px 28px #162e5009}
.supplier-panel-heading{display:flex;align-items:center;gap:15px;margin-bottom:25px}.supplier-panel-heading h2,.supplier-panel h2{font-size:20px;margin:0 0 6px}.supplier-panel-heading p{color:#64748b;margin:0;font-size:14px}.supplier-panel-icon{width:49px;height:49px;display:grid;place-items:center;border-radius:14px;background:#dff5ef;color:#0f766e;font-size:21px}
.supplier-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 22px}.supplier-workspace label{display:grid;gap:8px;font-size:13px;font-weight:700;color:#34445d}.supplier-workspace input,.supplier-workspace select,.supplier-workspace textarea{width:100%;min-width:0;min-height:47px;border:1px solid #cbd6e4;border-radius:11px;background:#fff;padding:11px 13px;font:inherit;font-weight:400;color:#1e293b;transition:border-color .2s,box-shadow .2s}.supplier-workspace input:focus,.supplier-workspace select:focus,.supplier-workspace textarea:focus{outline:none;border-color:#0f766e;box-shadow:0 0 0 3px #0f766e20}.supplier-workspace button{background:#0f766e;color:white;border:0;border-radius:10px;padding:12px 17px;font-weight:700;cursor:pointer}.supplier-workspace button.secondary{background:#eaf3f5;color:#155e75}.supplier-workspace form>p:last-child{margin-top:25px}
.supplier-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.supplier-kpi{border:1px solid var(--sp-line);background:white;border-radius:17px;padding:21px;display:grid;gap:10px}.supplier-kpi i{font-size:23px;color:#0f766e}.supplier-kpi span{color:#64748b;font-size:13px}.supplier-kpi strong{font-size:31px}.supplier-shortcuts{display:grid;gap:12px}.supplier-shortcuts a{display:flex;justify-content:space-between;padding:17px;border-radius:11px;background:#f1f7f9;color:#155e75;text-decoration:none;font-weight:700}
.supplier-workspace table{width:100%;border-collapse:collapse}.supplier-workspace th,.supplier-workspace td{text-align:left;padding:15px 12px;border-bottom:1px solid #e7edf4;vertical-align:middle}.supplier-workspace th{background:#f6f9fc;color:#475569;font-size:12px;text-transform:uppercase;letter-spacing:.04em}.supplier-workspace small{color:#64748b}.supplier-actions{display:flex;gap:9px;align-items:center;flex-wrap:wrap}.supplier-actions form{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.supplier-actions select{min-width:150px}.supplier-actions input{min-width:170px}.supplier-workspace .status{font-weight:700;text-transform:capitalize;color:#0f766e}
.supplier-type-tabs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:24px}.supplier-type-tab{display:flex;align-items:center;gap:15px;text-align:left!important;background:#f5f8fb!important;color:#34445d!important;border:1px solid #dbe4ee!important;border-radius:14px!important;padding:18px!important}.supplier-type-tab i{font-size:23px;color:#64748b}.supplier-type-tab strong{display:block;font-size:15px}.supplier-type-tab small{display:block;font-size:12px;font-weight:400;margin-top:4px}.supplier-type-tab.is-active{background:#e7f7f2!important;border-color:#0f766e!important;box-shadow:0 0 0 2px #0f766e22}.supplier-type-tab.is-active i{color:#0f766e}.supplier-type-panel[hidden]{display:none!important}.supplier-section-label{display:flex;align-items:center;gap:9px;margin:25px 0 16px;color:#155e75;font-weight:800;border-bottom:1px solid #e2e8f0;padding-bottom:12px}.supplier-note{font-size:13px;line-height:1.6;color:#64748b;background:#f4f8fb;border-radius:10px;padding:12px 15px;margin:17px 0}.supplier-note i{color:#0f766e;margin-right:6px}@media(max-width:650px){.supplier-type-tabs{grid-template-columns:1fr}}@media(max-width:900px){.supplier-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:650px){.supplier-grid,.supplier-kpis{grid-template-columns:1fr}.supplier-panel{padding:16px}.supplier-panel-heading{align-items:flex-start}}
</style>
<?php if($section==='nouveau'): ?>
<script>
(()=>{const root=document.querySelector('.supplier-workspace');if(!root)return;
 const tabs=[...root.querySelectorAll('[data-supplier-type]')],input=root.querySelector('#supplier-person-type');
 const setType=type=>{input.value=type;for(const tab of tabs){const active=tab.dataset.supplierType===type;tab.classList.toggle('is-active',active);tab.setAttribute('aria-selected',String(active));tab.tabIndex=active?0:-1;}
 for(const panel of root.querySelectorAll('.supplier-type-panel')){const active=panel.id==='supplier-fields-'+type;panel.hidden=!active;for(const field of panel.querySelectorAll('input,select,textarea')){field.disabled=!active;field.required=active&&field.dataset.requiredFor===type;}}
 };
 for(const [i,tab] of tabs){tab.addEventListener('click',()=>setType(tab.dataset.supplierType));tab.addEventListener('keydown',event=>{if(!['ArrowLeft','ArrowRight'].includes(event.key))return;event.preventDefault();const next=tabs[(i+(event.key==='ArrowRight'?1:tabs.length-1))%tabs.length];setType(next.dataset.supplierType);next.focus();});}
 setType('morale');
})();
</script>
<?php endif; ?>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';
