<?php
declare(strict_types=1);
namespace App\Services\Feb;
use DomainException;
final class FebUploadService {
 public function store(?array $file): ?array {
  if(!$file||($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
  if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK||!is_uploaded_file((string)($file['tmp_name']??'')))throw new DomainException('Justificatif invalide.');
  $size=(int)($file['size']??0);if($size<1||$size>10485760)throw new DomainException('Le justificatif doit faire au maximum 10 Mo.');
  $mime=(new \finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);
  $allowed=['application/pdf','image/jpeg','image/png','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
  if(!in_array($mime,$allowed,true))throw new DomainException('Format de justificatif non autorisé.');
  $month=date('Y/m');$dir=BASE_PATH.'/storage/private/feb/'.$month;if(!is_dir($dir)&&!mkdir($dir,0770,true)&&!is_dir($dir))throw new \RuntimeException('Stockage FEB indisponible.');
  $name=bin2hex(random_bytes(20));$path=$dir.'/'.$name;if(!move_uploaded_file((string)$file['tmp_name'],$path))throw new \RuntimeException('Échec du stockage du justificatif.');
  return ['original_name'=>mb_substr(basename((string)$file['name']),0,255),'storage_name'=>$month.'/'.$name,'mime_type'=>$mime,'file_size'=>$size,'sha256'=>hash_file('sha256',$path),'absolute_path'=>$path];
 }
 public function discard(?array $upload):void { $path=(string)($upload['absolute_path']??'');if($path!==''&&is_file($path)&&str_starts_with($path,BASE_PATH.'/storage/private/feb/'))@unlink($path); }
 public function resolve(string $storageName):?string {
  $clean=ltrim(str_replace('\\','/',$storageName),'/');if($clean===''||str_contains($clean,'..'))return null;
  $private=BASE_PATH.'/storage/private/feb/'.$clean;if(is_file($private))return $private;
  $legacy=BASE_PATH.'/public/uploads/feb/'.$clean;if(is_file($legacy))return $legacy;
  if(!str_contains($clean,'/'))foreach((array)glob(BASE_PATH.'/public/uploads/feb/*/*/'.basename($clean)) as $candidate)if(is_file($candidate))return $candidate;
  return null;
 }
}
