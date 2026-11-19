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
  $q=$this->pdo->prepare("SELECT bc.id,bc.reference,bc.offer_id,bc.document_id,bc.amount_ht,bc.status,bd.id AS budget_id,bd.status AS budget_status,bd.total_cost,bd.expected_margin FROM btp_client_orders bc LEFT JOIN btp_prestart_budgets bd ON bd.id=(SELECT b2.id FROM btp_prestart_budgets b2 WHERE b2.order_id=bc.id ORDER BY b2.revision DESC LIMIT 1) WHERE bc.projet_id=? ORDER BY bc.id DESC");
  $q->execute([$id]);$project['orders']=$q->fetchAll(PDO::FETCH_ASSOC);
  $project['budget_ready']=false;$project['contract_ready']=false;$project['selected_order']=null;
  foreach($project['orders'] as $bc){
   if($bc['status']==='annule'||!$bc['document_id'])continue;
   $quote=array_values(array_filter($project['quotes'],static fn($v)=>(int)$v['id']===(int)$bc['offer_id']&&$v['status']==='accepte_client'));
   if(!$quote||abs((float)$quote[0]['total_ht']-(float)$bc['amount_ht'])>0.009)continue;
   $q=$this->pdo->prepare("SELECT COUNT(*) FROM btp_client_order_lines WHERE order_id=?");$q->execute([$bc['id']]);$count=(int)$q->fetchColumn();
   $q=$this->pdo->prepare("SELECT COUNT(*) FROM btp_client_order_lines cl JOIN btp_offer_lines ol ON ol.id=cl.offer_line_id AND ol.offer_id=? WHERE cl.order_id=? AND cl.line_order=ol.line_order AND cl.description=ol.description AND cl.unit=ol.unit AND cl.quantity=ol.quantity AND cl.unit_price=ol.unit_price AND cl.line_total=ol.line_total AND (cl.group_name <=> ol.group_name)");
   $q->execute([$bc['offer_id'],$bc['id']]);$matching=(int)$q->fetchColumn();
   $q=$this->pdo->prepare('SELECT COUNT(*) FROM btp_offer_lines WHERE offer_id=?');$q->execute([$bc['offer_id']]);$quoteCount=(int)$q->fetchColumn();
   if(!$count||$count!==$matching||$count!==$quoteCount)continue;
   $project['contract_ready']=true;
   if($bc['budget_status']==='valide'){$project['budget_ready']=true;$project['selected_order']=$bc;break;}
  }
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
   if(!$d['contract_ready'])throw new DomainException('BC et devis accepté non conformes ou justificatif manquant.');
   $definitive=array_values(array_filter($d['plannings'],static fn($p)=>$p['status']==='definitif'&&(int)$p['tasks']>0));
   if(!$definitive)throw new DomainException('Planning définitif avec tâches obligatoire.');
   if(!$d['documents'])throw new DomainException('Documents du dossier obligatoires.');
   if(!$d['budget_ready'])throw new DomainException('Déboursé prévisionnel non validé : création du chantier bloquée.');
   $q=$this->pdo->prepare('INSERT INTO btp_chantier_starts(projet_id,code,nom,devis_id,planning_id,created_by) VALUES(?,?,?,?,?,?)');
   $q->execute([$id,$code,$name,$d['selected_order']['offer_id'],$definitive[0]['id'],$actor]);
   $new=(int)$this->pdo->lastInsertId();$this->pdo->commit();return $new;
  }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
 }
}
