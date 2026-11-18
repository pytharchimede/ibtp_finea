<?php
declare(strict_types=1);
namespace Tests\Unit\Security;
use PHPUnit\Framework\TestCase;
final class FebAdminSecurityTest extends TestCase {
 public function testAdminMutationsRequireAdminMiddlewareAndCsrf():void{$source=(string)file_get_contents(BASE_PATH.'/app/Controllers/Admin/AdminFebController.php');self::assertStringContainsString('AdminMiddleware::check()',$source);self::assertStringContainsString("Csrf::verify(\$_POST['_csrf_token']",$source);foreach(['workflowSave','workflowToggle','accessSave','budgetSave'] as $method)self::assertMatchesRegularExpression('/function '.$method.'\([^}]+\$this->post\(\)/s',$source);}
 public function testConfigurationUsesTransactionsLocksAndHistory():void{$source=(string)file_get_contents(BASE_PATH.'/app/Services/Admin/FebAdminService.php');self::assertGreaterThanOrEqual(4,substr_count($source,'beginTransaction()'));self::assertStringContainsString('FOR UPDATE',$source);self::assertStringContainsString("->log(",$source);}
 public function testMigrationDefinesClosedAccessBudgetAndAuditTables():void{$sql=(string)file_get_contents(BASE_PATH.'/database/migrations/20261118_001_create_feb_module.sql');foreach(['feb_chantier_access','feb_chantier_budgets','feb_admin_history'] as $table)self::assertStringContainsString('CREATE TABLE IF NOT EXISTS '.$table,$sql);self::assertStringContainsString("ENUM('demander','valider','payer')",$sql);}
 public function testAdminNavigationExposesFebConfiguration():void{$source=(string)file_get_contents(BASE_PATH.'/app/View/Navigation/AdminNavigation.php');self::assertStringContainsString("'key' => 'feb'",$source);self::assertStringContainsString("'url' => 'admin/feb'",$source);}
}
