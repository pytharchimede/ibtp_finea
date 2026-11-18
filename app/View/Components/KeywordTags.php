<?php
declare(strict_types=1);

namespace App\View\Components;

/**
 * Champ de mots-clés réutilisable, avec édition et suppression de badges.
 * Le textarea reste la source de vérité envoyée au serveur (progressive enhancement).
 */
final class KeywordTags
{
    public static function render(string $name,string $label,string $value,array $options=[]):string
    {
        $e=static fn(mixed $v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $id=(string)($options['id']??'tags-'.preg_replace('/[^a-z0-9_-]/i','-',$name));
        $tone=in_array($options['tone']??'', ['positive','negative'],true)?$options['tone']:'positive';
        $hint=(string)($options['hint']??'Ajoutez un mot-clé puis appuyez sur Entrée.');
        $placeholder=(string)($options['placeholder']??'Saisir un mot-clé…');
        ob_start();?>
<div class="finea-keyword-tags" data-keyword-tags data-tone="<?= $e($tone) ?>">
    <label class="finea-keyword-tags-label" for="<?= $e($id) ?>-input"><?= $e($label) ?></label>
    <p class="finea-keyword-tags-hint"><?= $e($hint) ?></p>
    <div class="finea-keyword-tags-surface">
        <div class="finea-keyword-tags-list" data-tags-list aria-live="polite"></div>
        <div class="finea-keyword-tags-entry">
            <input id="<?= $e($id) ?>-input" data-tags-input type="text" maxlength="120" autocomplete="off" placeholder="<?= $e($placeholder) ?>">
            <button type="button" class="finea-keyword-tags-add" data-tags-add>+ Ajouter</button>
        </div>
    </div>
    <p class="finea-keyword-tags-feedback" data-tags-feedback role="status" aria-live="polite"></p>
    <textarea class="finea-keyword-tags-source" name="<?= $e($name) ?>" id="<?= $e($id) ?>" maxlength="10000" rows="3" data-tags-source aria-label="<?= $e($label) ?>"><?= $e($value) ?></textarea>
    <small class="finea-keyword-tags-count" data-tags-count></small>
</div>
<?php return (string)ob_get_clean();
    }
}
