<?php
declare(strict_types=1);
namespace App\Services\Btp;
use PDO;
use DomainException;
final class ChantierStartService {
 public function __construct(private PDO $pdo) {}
 public function projects(): array {
  return $this->pdo->query("SELECT p.id,p.reference,p.intitule,p.client_nom,p.statut,s.id AS chantier_id FROM btp_projets p LEFT JOIN btp_chantier_starts s ON s.projet_id=p.id ORDER BY p.id DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
 }
 public function list(): array {
  return $this->pdo->query("SELECT s.*,p.reference,p.client_nom,p.intitule FROM btp_chantier_starts s JOIN btp_projets p ON p.id=s.projet_id ORDER BY s.id DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
 }
 public function dossier(int $id): ?array {
  $q=$this->pdo->prepare("SELECT id,reference,intitule,client_nom,statut FROM btp_projets WHERE id=?");$q->execute([$id]);$project=$q->fetch(PDO::FETCH_ASSOC);if(!$project)return null;
  $q=$this->pdo->prepare("SELECT id,revision,title,status,total_ht,total_ttc,created_at FROM btp_offer_drafts WHERE projet_id=? ORDER BY revision DESC");$q->execute([$id]);$project['quotes']=$q->fetchAll(PDO::FETCH_ASSOC);
  $q=$this->pdo->prepare("SELECT pl.id,pl.revision,pl.title,pl.status,COUNT(t.id) AS tasks,MIN(t.start_date) AS starts,MAX(t.end_date) AS ends FROM btp_project_plannings pl LEFT JOIN btp_project_planning_tasks t ON t.planning_id=pl.id WHERE pl.projet_id=? GROUP BY pl.id,pl.revision,pl.title,pl.status ORDER BY pl.revision DESC");$q->execute([$id]);$project['plannings']=$q->fetchAll(PDO::FETCH_ASSOC);
  $q=$this->pdo->prepare("SELECT id,label,original_name,category FROM btp_documents WHERE projet_id=? ORDER BY id DESC");$q->execute([$id]);$project['documents']=$q->fetchAll(PDO::FETCH_ASSOC);
  $q=$this->pdo->prepare("SELECT id,code,nom FROM btp_chantier_starts WHERE projet_id=?");$q->execute([$id]);$project['existing']=$q->fetch(PDO::FETCH_ASSOC)?:null;
  // Debourse budgets are chantier-scoped in the legacy schema, so cannot be verified before startup.
  $project['budget_ready']=false;
  return $project;
 }
 public function start(int $id,array $data,?int $actor): int {
  $code=trim((string)($data['code']??''));$name=trim((string)($data['nom']??''));
  if($code===''||mb_strlen($code)>40||$name===''||mb_strlen($name)>180)throw new DomainException('Code ou nom du chantier invalide.');
  $this->pdo->beginTransaction();
  try{
   $q=$this->pdo->prepare('SELECT id FROM btp_projets WHERE id=? FOR UPDATE');$q->execute([$id]);if(!$q->fetchColumn())throw new DomainException('Projet introuvable.');
   $d=$this->dossier($id);
   if($d['existing'])throw new DomainException('Un chantier existe déjà pour ce projet.');
   $quotes=array_values(array_filter($d['quotes'],static fn($q)=>$q['status']==='accepte_client'));
   if(!$quotes)throw new DomainException('Devis accepté par le client obligatoire.');
   if(!$d['plannings'])throw new DomainException('Planning prévisionnel obligatoire.');
   if(!$d['documents'])throw new DomainException('Documents du dossier obligatoires.');
   if(!$d['budget_ready'])throw new DomainException('Déboursé prévisionnel non validé : création du chantier bloquée.');
   $q=$this->pdo->prepare('INSERT INTO btp_chantier_starts(projet_id,code,nom,devis_id,planning_id,created_by) VALUES(?,?,?,?,?,?)');
   $q->execute([$id,$code,$name,$quotes[0]['id'],$d['plannings'][0]['id'],$actor]);
   $new=(int)$this->pdo->lastInsertId();$this->pdo->commit();return $new;
  }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
 }
}
