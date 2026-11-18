<?php
declare(strict_types=1);
use App\Helpers\View;
use App\Helpers\Auth;
use App\Security\PermissionEntityRegistry;
$e=static fn(mixed $v): string => htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$pageTitle='Projets & Chantiers';
ob_start();
?>
<style>
.btp-page{max-width:1200px;margin:0 auto;color:#15233d}
.btp-page *{box-sizing:border-box}
.btp-hero{background:linear-gradient(115deg,#182e59,#294d8d);border-radius:24px;padding:32px;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:22px}
.btp-hero h2{font-size:clamp(1.5rem,3vw,2.2rem);margin:8px 0;color:#fff}
.btp-hero p{color:#e0eaff;max-width:680px;line-height:1.6;margin:0}
.btp-eyebrow{font-size:.75rem;text-transform:uppercase;letter-spacing:.14em;font-weight:800;color:#facc15}
.btp-return{border:1px solid #ffffff55;color:#fff;padding:12px 18px;border-radius:12px;white-space:nowrap;font-weight:700}
.btp-grid{display:grid;grid-template-columns:minmax(290px,380px) minmax(0,1fr);gap:22px;align-items:start}
.btp-panel{background:#fff;border:1px solid #dbe3ef;border-radius:20px;padding:26px;box-shadow:0 12px 32px #15233d10}
.btp-panel h3{margin:0 0 8px;font-size:1.2rem;color:#142442}
.btp-help{font-size:.89rem;line-height:1.55;color:#64748b;margin:0 0 22px}
.btp-field{display:flex;flex-direction:column;gap:7px;margin-bottom:16px;font-weight:700;font-size:.88rem;color:#334155}
.btp-field input,.btp-field select{width:100%;padding:12px 14px;background:#fff;border:1px solid #cbd5e1;border-radius:11px;color:#0f172a;font:inherit;font-weight:400}
.btp-field input:focus,.btp-field select:focus{outline:3px solid #bfdbfe;border-color:#2563eb}
.btp-submit{display:block;width:100%;padding:13px 20px;border:0;border-radius:12px;background:#1e40af;color:#fff;font-weight:800;font:inherit;cursor:pointer}
.btp-alert{padding:12px 15px;border-radius:10px;background:#fff1f2;color:#9f1239;margin-bottom:16px}
.btp-table-wrap{overflow-x:auto}
.btp-table{width:100%;border-collapse:collapse;color:#334155;text-align:left;font-size:.9rem}
.btp-table th{background:#f1f5f9;font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;color:#475569}
.btp-table td,.btp-table th{padding:14px 12px;border-bottom:1px solid #e2e8f0}
.btp-table td:first-child{font-weight:800;color:#1e40af}
.btp-pill{display:inline-block;background:#eaf1ff;color:#1d4ed8;padding:5px 9px;border-radius:30px;font-size:.78rem;font-weight:700}
.btp-empty{text-align:center;padding:45px 16px;color:#64748b;background:#f8fafc;border-radius:14px}
.btp-next{margin-top:18px;color:#64748b;font-size:.86rem;line-height:1.55}
@media(max-width:800px){.btp-grid{grid-template-columns:1fr}.btp-hero{flex-direction:column;align-items:start}.btp-panel{padding:19px}}
</style>
<div class="btp-page">
<section class="btp-hero">
<div><div class="btp-eyebrow">FINEA BTP · Gestion des opérations</div>
<h2>Projets &amp; Chantiers</h2>
<p>Centralisez vos projets, rattachez vos clients et préparez le suivi des devis, bons de commande et chantiers.</p></div>
<a class="btp-return" href="<?= $e(View::url('selection_portail')) ?>">← Retour au portail</a>
</section>
<?php if(!empty($errorBtp)): ?><div class="btp-alert" role="alert"><?= $e($errorBtp) ?></div><?php endif; ?>
<div class="btp-grid">
<section class="btp-panel">
<h3>Nouveau projet</h3>
<p class="btp-help">Créez un dossier de projet avec une référence unique et son client.</p>
<?php if(Auth::can(PermissionEntityRegistry::BTP_PROJECTS,'create')): ?>
<form method="post" action="<?= $e(View::url('btp/projets')) ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrfBtp) ?>">
<label class="btp-field">Référence du projet<input name="reference" placeholder="PRJ-2026-001" required maxlength="40"></label>
<label class="btp-field">Intitulé du projet<input name="intitule" placeholder="Construction d'un immeuble R+2" required maxlength="180"></label>
<label class="btp-field">Client CRM<select id="btp-client-select" name="client_id"><option value="">Client non enregistré dans le CRM</option>
<?php foreach($clientsBtp as $client): ?><option value="<?= (int)$client['id'] ?>" data-name="<?= $e($client['name']) ?>"><?= $e($client['name']) ?></option><?php endforeach; ?>
</select></label>
<label class="btp-field">Nom du client<input id="btp-client-name" name="client_nom" placeholder="Nom du maître d'ouvrage" required maxlength="180"></label>
<button class="btp-submit" type="submit">Créer le projet</button>
</form>
<script>document.getElementById('btp-client-select').addEventListener('change',function(){let o=this.options[this.selectedIndex];if(o.dataset.name)document.getElementById('btp-client-name').value=o.dataset.name;});</script>
<?php else: ?><p class="btp-help">Votre compte dispose de la consultation, mais pas de la création de projets.</p><?php endif; ?>
</section>
<section class="btp-panel">
<h3>Portefeuille de projets</h3>
<p class="btp-help"><?= count($projets) ?> projet(s) affiché(s) · dossiers les plus récents en premier.</p>
<?php if(!$projets): ?><div class="btp-empty">Aucun projet pour le moment. Utilisez le formulaire pour créer votre premier dossier BTP.</div>
<?php else: ?><div class="btp-table-wrap"><table class="btp-table"><thead><tr><th>Référence</th><th>Projet</th><th>Client</th><th>Statut</th></tr></thead><tbody>
<?php foreach($projets as $p): ?><tr><td><?= $e($p['reference']) ?></td><td><?= $e($p['intitule']) ?></td><td><?= $e($p['client_nom']) ?></td><td><span class="btp-pill"><?= $e($p['statut']) ?></span></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
<p class="btp-next">Prochaine étape : devis versionnés, validation signée, BC client puis ouverture du chantier. Ces opérations ne sont pas encore accessibles depuis cet écran.</p>
</section>
</div>
</div>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/app.php'; ?>
