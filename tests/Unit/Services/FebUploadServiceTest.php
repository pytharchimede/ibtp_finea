<?php
declare(strict_types=1);
namespace Tests\Unit\Services;
use App\Services\Feb\FebUploadService;use PHPUnit\Framework\TestCase;
final class FebUploadServiceTest extends TestCase {
 public function testDiscardNeverDeletesPublicLegacyFile():void{$dir=BASE_PATH.'/public/uploads/feb/test-orphans';if(!is_dir($dir))mkdir($dir,0775,true);$file=$dir.'/legacy-'.bin2hex(random_bytes(4));file_put_contents($file,'test');(new FebUploadService())->discard(['absolute_path'=>$file]);self::assertFileExists($file);@unlink($file);@rmdir($dir);}
 public function testResolveSupportsLegacyPublicPathAndRejectsTraversal():void{$service=new FebUploadService();$legacyDir=BASE_PATH.'/public/uploads/feb/test-resolve';@mkdir($legacyDir,0775,true);file_put_contents($legacyDir.'/legacy','l');self::assertSame($legacyDir.'/legacy',$service->resolve('test-resolve/legacy'));self::assertNull($service->resolve('../.env'));@unlink($legacyDir.'/legacy');@rmdir($legacyDir);}
 public function testPrivateStorageIsConfigured():void{$source=(string)file_get_contents(BASE_PATH.'/app/Services/Feb/FebUploadService.php');self::assertStringContainsString("/storage/private/feb/",$source);}
}
