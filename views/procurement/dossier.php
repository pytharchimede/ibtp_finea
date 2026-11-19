<?php
declare(strict_types=1);
use App\Helpers\View;use App\View\Components\Ui;
$e=static fn($v)=>View::e((string)$v);
$uploaded=array_column($documents,'document_type');
ob_start();
?>
<div class="finea-shell"><div class="finea-container">
<?=Ui::pageHeader('Dossier · '.$supplier['legal_name'],'Pièces justificatives et complétude avant agrément',['eyebrow'=>'FINEA · Fournisseurs & Achats','actions'=>[Ui::button('Retour au répertoire',['href'=>'fournisseurs','variant'=>'secondary'])]])?>
<div class="finea-section-card" style="background:white;padding:25px;border:1px solid #dbe4ee;border-radius:16px;margin:20px 0">
<h2><?= $e($supplier['legal_name']) ?> <small style="font-weight:400;color:#64748b"><?= $e($supplier['supplier_code']) ?></small></h2>
<p>Nature : <strong><?= $supplier['person_type']==='physique'?'Personne physique':'Personne morale' ?></strong> · Statut : <strong><?= $e($supplier['status']) ?></strong></p>
<?php if($missing): ?><p style="color:#a16207;background:#fffbeb;padding:13px;border-radius:10px">Pièces obligatoires manquantes : <?= $e(implode(', ',$missing)) ?></p><?php else: ?><p style="color:#047857">Toutes les pièces obligatoires sont présentes. Leur validité reste à vérifier par les approbateurs.</p><?php endif; ?>
<h3>Liste des justificatifs</h3>
<div style="overflow-x:auto"><table class="finea-table" style="width:100%"><thead><tr><th>Document</th><th>Obligation</th><th>État</th><th>Déposer</th></tr></thead><tbody>
<?php foreach($requirements as $requirement): ?>
<tr><td><?= $e($requirement['label']) ?></td><td><?= (int)$requirement['required']?'Obligatoire':'Facultatif' ?></td><td><?= in_array($requirement['document_type'],$uploaded,true)?'Déposé':'Manquant' ?></td><td>
<?php if($canUpload): ?><form method="post" enctype="multipart/form-data" action="<?= $e(View::url('fournisseurs/'.$supplier['id'].'/documents')) ?>" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
<input type="hidden" name="_csrf_token" value="<?= $e($csrf) ?>"><input type="hidden" name="document_type" value="<?= $e($requirement['document_type']) ?>">
<input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required style="max-width:230px"><button type="submit">Envoyer</button></form><?php else: ?>—<?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody></table></div>
<h3>Fichiers enregistrés</h3>
<?php if(!$documents): ?><p>Aucun fichier déposé.</p><?php endif; ?>
<?php foreach($documents as $doc): ?><p><a href="<?= $e(View::url('fournisseurs/documents/'.$doc['id'].'/telecharger')) ?>"><?= $e($doc['original_name']) ?></a> <small>· <?= $e($doc['document_type']) ?> · <?= number_format((int)$doc['file_size']/1024,0,',',' ') ?> Ko</small></p><?php endforeach; ?>
</div></div></div>
<style>.finea-section-card button{background:#0f766e;border:0;color:white;padding:10px 14px;border-radius:9px;cursor:pointer}.finea-section-card th,.finea-section-card td{padding:12px;border-bottom:1px solid #e2e8f0;text-align:left}</style>
<?php $content=ob_get_clean();require BASE_PATH.'/views/layouts/module.php';
