<?php
declare(strict_types=1);
namespace App\Repositories\Crm;
use PDO;
final class CrmReferenceRepository {
 public function __construct(private PDO $pdo){}
 public function all(bool $activeOnly=true):array{
  $sql="SELECT id,list_code,option_code,label,country_code,legal_form,is_active,sort_order FROM crm_reference_options";
  if($activeOnly)$sql.=" WHERE is_active=1";
  $sql.=" ORDER BY list_code,sort_order,label";
  $lists=['country'=>[],'identifier'=>[],'category'=>[],'status'=>[]];
  foreach($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row){$lists[$row['list_code']][]=$row;}
  return $lists;
 }
 public function save(array $input):void{
  $list=(string)($input['list_code']??'');
  if(!in_array($list,['country','identifier','category','status'],true))throw new \DomainException('Liste invalide.');
  $code=trim((string)($input['option_code']??''));
  if($list==='country'||$list==='identifier')$code=strtoupper($code);
  if(!preg_match('/^[a-zA-Z0-9_-]{2,40}$/',$code))throw new \DomainException('Code invalide.');
  $label=trim((string)($input['label']??''));
  if($label===''||mb_strlen($label)>150)throw new \DomainException('Libellé invalide.');
  $country=strtoupper(trim((string)($input['country_code']??'')));
  if($country!==''&&!preg_match('/^[A-Z]{2}$/',$country))throw new \DomainException('Code pays invalide.');
  $legal=trim((string)($input['legal_form']??''));
  if($legal!==''&&!in_array($legal,['personne_physique','personne_morale'],true))throw new \DomainException('Nature juridique invalide.');
  $stmt=$this->pdo->prepare("INSERT INTO crm_reference_options(list_code,option_code,label,country_code,legal_form,sort_order) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE label=VALUES(label),country_code=VALUES(country_code),legal_form=VALUES(legal_form),sort_order=VALUES(sort_order)");
  $stmt->execute([$list,$code,$label,$country?:null,$legal?:null,max(0,min(9999,(int)($input['sort_order']??100)))]);
 }
 public function toggle(int $id,bool $active):void{
  $stmt=$this->pdo->prepare("UPDATE crm_reference_options SET is_active=? WHERE id=?");
  $stmt->execute([$active?1:0,$id]);
 }
}
