<?php
declare(strict_types=1);
use App\View\Components\CrmDashboard;
ob_start();
echo CrmDashboard::render($crmAnalytics);
$content=ob_get_clean();
$additionalStyles=array_merge($additionalStyles??[],['css/components/crm-dashboard.css']);
require BASE_PATH.'/views/layouts/module.php';
