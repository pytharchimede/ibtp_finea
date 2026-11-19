<?php
declare(strict_types=1);
namespace Tests\Unit\Security;
use PHPUnit\Framework\TestCase;
final class FebRouteSecurityTest extends TestCase {
 public function testEveryMutationChecksPermissionAndCsrf():void{$source=(string)file_get_contents(BASE_PATH.'/app/Controllers/Feb/FebController.php');foreach(['store','submit','document','cancel','decide','pay'] as $method){self::assertMatchesRegularExpression('/function '.$method.'\([^}]+\$this->guard\([^}]+\$this->csrf\(\)/s',$source,$method.' doit vérifier permission et CSRF.');}}
 public function testCriticalServiceOperationsCheckChantierAccess():void{$source=(string)file_get_contents(BASE_PATH.'/app/Services/Feb/FebService.php');$methods=['create','submit','addDocument','cancel','decide','pay'];foreach($methods as $index=>$method){$start=strpos($source,'function '.$method.'(');$end=isset($methods[$index+1])?strpos($source,'function '.$methods[$index+1].'(',$start):strlen($source);$body=substr($source,$start,$end-$start);self::assertStringContainsString('hasChantierAccess(',$body,$method.' doit vérifier une affectation chantier.');}}
}
