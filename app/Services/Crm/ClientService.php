<?php
declare(strict_types=1);
namespace App\Services\Crm;
use App\Repositories\Crm\ClientRepository;
use DomainException;
final class ClientService {
 public function __construct(private ClientRepository $repo){}
 public function all(string $search=''):array{return $this->repo->all(mb_substr(trim($search),0,100));}
 public function find(int $id):?array{return $this->repo->find($id);}
 public function save(array $input,int $actor,?int $id=null):int{
  if($actor<=0)throw new DomainException('Session invalide.');
  $data=[];
  foreach(['name'=>180,'contact_name'=>160,'email'=>150,'phone'=>60,'country'=>100,'city'=>120,'sector'=>120,'notes'=>5000] as $key=>$max){
   $v=trim((string)($input[$key]??''));if(mb_strlen($v)>$max)throw new DomainException('Champ trop long : '.$key);
   $data[$key]=$v!==''?$v:null;
  }
  if($data['name']===null)throw new DomainException('Nom du client obligatoire.');
  if($data['email']!==null&&!filter_var($data['email'],FILTER_VALIDATE_EMAIL))throw new DomainException('Adresse email invalide.');
  $data['type']=(string)($input['type']??'prospect');$data['status']=(string)($input['status']??'new');
  if(!in_array($data['type'],['prospect','client','partner'],true)||!in_array($data['status'],['new','active','dormant','lost'],true))throw new DomainException('Statut ou catégorie invalide.');
  if($id!==null){if($id<=0)throw new DomainException('Identifiant invalide.');$this->repo->update($id,$data);return $id;}
  return $this->repo->create($data,$actor);
 }
}
