<?php
declare(strict_types=1);
use App\View\Components\CrmOpportunityForm;
ob_start();
echo CrmOpportunityForm::render($clients,$selectedClient,$csrfOpp,$errorOpp);
$content=ob_get_clean();
$additionalStyles=array_merge($additionalStyles??[],['css/components/crm-opportunity-form.css']);
$additionalScripts=array_merge($additionalScripts??[],['js/components/crm-opportunity-form.js']);
require BASE_PATH.'/views/layouts/module.php';
