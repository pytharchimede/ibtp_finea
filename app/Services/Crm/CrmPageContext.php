<?php
declare(strict_types=1);
namespace App\Services\Crm;
use App\Services\Shared\ModuleDashboardService;
final class CrmPageContext {
 public static function forPage(string $title,string $active):array {
  $module=(new ModuleDashboardService())->dashboard('crm');
  return ['pageTitle'=>$title,'moduleName'=>$module['label'],'moduleCode'=>$module['code'],
   'moduleTheme'=>$module,'activeModule'=>$active,'moduleNavigation'=>$module['navigation'],
   'additionalStyles'=>['css/finea-ui.css']];
 }
}
