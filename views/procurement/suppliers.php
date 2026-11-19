<?php
declare(strict_types=1);
use App\Helpers\View;
$e=static fn($v)=>View::e((string)$v);
?>
<style>
.supplier-workspace{max-width:1250px;margin:30px auto;padding:0 20px;color:#15243b}.supplier-workspace h1{font-size:30px}.supplier-card{background:#fff;border:1px solid #dce5f0;border-radius:17px;padding:23px;margin:18px 0;box-shadow:0 5px 25px #1831530b}.supplier-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:13px}.supplier-workspace label{display:grid;gap:6px;font-weight:600;font-size:14px}.supplier-workspace input,.supplier-workspace select,.supplier-workspace textarea{width:100%;min-height:43px;padding:10px;border:1px solid #cbd5e1;border-radius:10px;background:#fff}.supplier-workspace button{border:0;border-radius:9px;padding:10px 15px;background:#2456a4;color:white;cursor:pointer}.supplier-workspace button.secondary{background:#e8eef8;color:#254a80}.supplier-workspace table{width:100%;border-collapse:collapse}.supplier-workspace td,.supplier-workspace th{text-align:left;padding:12px;border-bottom:1px solid #e5eaf2;vertical-align:top}.supplier-workspace .supplier-actions{display:flex;flex-wrap:wrap;gap:8px;align-items:center}.supplier-workspace small{color:#64748b}.supplier-workspace .status{font-weight:700;text-transform:capitalize}
</style>
<main class="supplier-workspace">
 <h1><i class="fa-solid fa-truck"></i> Référencement des fournisseurs</h1>
 <p>Enregistrement, agrément et suivi des fournisseurs physiques ou moraux. Seuls les dossiers approuvés sont utilisables dans les FEB.</p>
 <div class="supplier-card"><h2>Nouveau fournisseur</h2>
 <form method="post" action="<?= $e(View::url('fournisseurs')) ?>">
 <input type="hidden" name="_csrf_token" value="<?= $e($csrf) ?>">
 <div class="supplier-grid">
 <label>Nature juridique<select name="person_type" required><option value="morale">Personne morale / Société</option><option value="physique">Personne physique</option></select></label>
 <label>Nom / Raison sociale<input name="legal_name" required maxlength="200"></label>
 <label>Nom commercial<input name="trade_name" maxlength="200"></label>
 <label>Contact principal<input name="contact_name" maxlength="180"></label>
 <label>Email<input type="email" name="email" maxlength="190"></label>
 <label>Téléphone<input name="phone" maxlength="40"></label>
 <label>Adresse<input name="address" maxlength="500"></label>
 <label>Pays (ISO 2 lettres)<input name="country_code" value="CI" maxlength="2"></label>
 <label>NCC / Identifiant fiscal<input name="tax_identifier" maxlength="100"></label>
 <label>RCCM / Registre du commerce<input name="trade_register" maxlength="100"></label>
 <label>Banque<input name="bank_name" maxlength="150"></label>
 <label>RIB / IBAN<input name="bank_iban" maxlength="120"></label>
 </div><p><button type="submit">Créer le dossier fournisseur</button></p>
 </form></div>
 <?php if($isAdmin): ?>
 <div class="supplier-card"><h2>Circuits d'agrément configurables</h2>
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
 <?php endforeach; ?></div>
 <?php endif; ?>
 <div class="supplier-card"><h2>Dossiers fournisseurs</h2><div style="overflow-x:auto"><table><thead><tr><th>Fournisseur</th><th>Type</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
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
 </tbody></table></div></div>
</main>
