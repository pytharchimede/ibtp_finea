<?php

use App\View\Components\EmployeeRequestForms;
use App\View\Components\Ui;
use App\View\Pages\Employee\RequestFormPage;

/** @var RequestFormPage $page */

ob_start();
?>
<div class="finea-shell employee-shell">
    <div class="finea-container">
        <?= Ui::pageHeader(
            'Quelle démarche souhaitez-vous effectuer ?',
            'Choisissez une catégorie : le formulaire affiche uniquement les informations nécessaires.',
            ['eyebrow' => 'Self-service RH', 'class' => 'employee-hero', 'actions' => [Ui::button('Mes demandes', ['href' => 'espace-employe/demandes', 'variant' => 'secondary', 'icon' => 'arrow-left'])]]
        ) ?>
        <?php
        $chantiers = $febData['chantiers'] ?? [];
        $workflows = $febData['workflows'] ?? [];
        $selectedChantier = $febData['selectedChantier'] ?? 0;
        $lines = $febData['lines'] ?? [];
        $csrf = $page->csrfToken;
        ob_start();
        require BASE_PATH . '/views/feb/employee-wizard.php';
        $febHtml = ob_get_clean();
        ?>
        <?= EmployeeRequestForms::render($page->csrfToken, $page->selectedType, $febHtml) ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/module.php';
