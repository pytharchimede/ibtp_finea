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
  $legal=(string)($input['legal_form']??'personne_morale');
  $country=strtoupper(trim((string)($input['identity_country']??'CI')));
  $kind=strtoupper(trim((string)($input['identifier_kind']??'')));
  $value=trim((string)($input['identifier_value']??''));
  $cni=isset($input['cni_present']) && (string)$input['cni_present']==='1';
  if(!in_array($legal,['personne_physique','personne_morale'],true))throw new DomainException('Nature juridique invalide.');
  if(!preg_match('/^[A-Z]{2}$/',$country))throw new DomainException('Code pays ISO à deux lettres requis.');
  if(strlen($kind)>32||strlen($value)>100)throw new DomainException('Identifiant légal trop long.');
  if($legal==='personne_physique' && $country==='CI'){
   if(!$cni||$kind!=='CNI'||$value==='')throw new DomainException('CNI présente et numéro CNI obligatoires pour une personne physique ivoirienne.');
  }elseif($legal==='personne_morale'){
   $allowed=match($country){'CI'=>['NCC','RCCM'],'FR'=>['SIREN','SIRET'],default=>['REGISTRE','TVA','LEI','SIREN','SIRET']};
   if(!in_array($kind,$allowed,true)||$value==='')throw new DomainException('Identifiant officiel requis pour ce pays.');
  }
  $data['legal_form']=$legal;$data['identity_country']=$country;$data['identifier_kind']=$kind!==''?$kind:null;
  $data['identifier_value']=$value!==''?$value:null;$data['cni_present']=$cni?1:0;
  if($id!==null){if($id<=0)throw new DomainException('Identifiant invalide.');$this->repo->update($id,$data);return $id;}
  return $this->repo->create($data,$actor);
 }
}
