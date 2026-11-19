<?php
declare(strict_types=1);
namespace App\Repositories\Feb;
use PDO;
final class FebRepository {
 public function __construct(private PDO $pdo){}
 public function pdo():PDO{return $this->pdo;}
 public function workflows():array{return $this->pdo->query("SELECT * FROM feb_workflows WHERE active=1 ORDER BY id")->fetchAll();}
 public function chantiersFor(int $user,string $role):array{$q=$this->pdo->prepare("SELECT s.id,s.code,s.nom,p.intitule FROM feb_chantier_access a JOIN btp_chantier_starts s ON s.id=a.chantier_id JOIN feb_chantier_budgets cb ON cb.chantier_id=s.id JOIN btp_prestart_budgets b ON b.id=cb.budget_id AND b.status='valide' JOIN btp_projets p ON p.id=s.projet_id WHERE a.user_id=? AND a.access_role=? AND s.status<>'annule' ORDER BY s.nom");$q->execute([$user,$role]);return $q->fetchAll();}

 /** Un service RH actif peut porter un budget FEB bureau. */
 public function officeServices():array{
  return $this->pdo->query("SELECT id,name FROM rh_services WHERE is_active=1 ORDER BY name")->fetchAll()?:[];
 }
 /** Les allocations doivent être budgetisées en amont; pas de création implicite. */
 public function officeAllocations():array{
  return $this->pdo->query("SELECT a.id,a.service_id,a.code,a.label,a.budget_amount,a.fiscal_year,s.name service_name FROM feb_office_budget_allocations a JOIN rh_services s ON s.id=a.service_id AND s.is_active=1 WHERE a.active=1 AND a.fiscal_year=YEAR(CURDATE()) AND a.budget_amount>0 ORDER BY s.name,a.label")->fetchAll()?:[];
 }
 public function officeAllocation(int $allocation,int $service,bool $lock=false):?array{
  $q=$this->pdo->prepare("SELECT a.* FROM feb_office_budget_allocations a JOIN rh_services s ON s.id=a.service_id AND s.is_active=1 WHERE a.id=? AND a.service_id=? AND a.fiscal_year=YEAR(CURDATE()) AND a.active=1 AND a.budget_amount>0".($lock?' FOR UPDATE':''));
  $q->execute([$allocation,$service]);return $q->fetch()?:null;
 }
 /** Permissions globales pour les actions bureau, sans élargir les accès chantier. */
 public function officeRoleAuthorized(int $user,string $role):bool{
  $column=match($role){'demander'=>'can_create','valider'=>'can_update','payer'=>'can_delete',default=>null};
  if($column===null)return false;
  $q=$this->pdo->prepare("SELECT u.is_admin OR COALESCE(p.$column,0) FROM users u LEFT JOIN permission_entities e ON e.code='feb_requests' LEFT JOIN user_permissions p ON p.user_id=u.id AND p.entity_id=e.id WHERE u.id=? AND u.status='active'");
  $q->execute([$user]);return (bool)$q->fetchColumn();
 }
 public function canActOnRequest(array $request,int $user,string $role):bool{
  return ($request['scope']??'chantier')==='bureau'
   ? $this->officeRoleAuthorized($user,$role)
   : $this->hasChantierAccess($user,(int)$request['chantier_id'],$role);
 }
 public function hasChantierAccess(int $user,int $chantier,string $role,bool $lock=false):bool{$q=$this->pdo->prepare('SELECT id FROM feb_chantier_access WHERE user_id=? AND chantier_id=? AND access_role=?'.($lock?' FOR UPDATE':''));$q->execute([$user,$chantier,$role]);return (bool)$q->fetchColumn();}
 public function budgetLines(int $chantier,bool $lock=false):array{$sql="SELECT l.id,l.budget_id,l.order_line_id quote_line_id,l.total_cost,l.materials,l.labor,l.equipment,l.subcontracting,l.other_cost,cl.description FROM feb_chantier_budgets cb JOIN btp_prestart_budgets b ON b.id=cb.budget_id AND b.status='valide' JOIN btp_prestart_budget_lines l ON l.budget_id=b.id JOIN btp_client_order_lines cl ON cl.id=l.order_line_id WHERE cb.chantier_id=? ORDER BY cl.line_order,l.id".($lock?' FOR UPDATE':'');$q=$this->pdo->prepare($sql);$q->execute([$chantier]);return $q->fetchAll();}
 public function listOwn(int $user):array{
  $q=$this->pdo->prepare("SELECT r.id,r.reference,r.scope,r.status,r.needed_at,r.created_at,r.purpose,COALESCE(s.nom,rs.name) location_name,COALESCE(SUM(i.total_amount),0) total_amount FROM feb_requests r LEFT JOIN btp_chantier_starts s ON s.id=r.chantier_id LEFT JOIN rh_services rs ON rs.id=r.service_id LEFT JOIN feb_request_items i ON i.request_id=r.id WHERE r.requester_id=? AND (r.scope='bureau' OR EXISTS(SELECT 1 FROM feb_chantier_access a WHERE a.chantier_id=r.chantier_id AND a.user_id=? AND a.access_role='demander')) GROUP BY r.id ORDER BY r.created_at DESC,r.id DESC LIMIT 500");
  $q->execute([$user,$user]);return $q->fetchAll()?:[];
 }
 public function listFor(int $user,string $role,?int $requester=null):array{
  $sql="SELECT r.*,COALESCE(s.code,CONCAT('BUR-',r.service_id)) chantier_code,COALESCE(s.nom,CONCAT('Bureau · ',rs.name)) chantier_name,u.full_name requester_name,COALESCE(SUM(i.total_amount),0) total_amount FROM feb_requests r LEFT JOIN btp_chantier_starts s ON s.id=r.chantier_id LEFT JOIN rh_services rs ON rs.id=r.service_id JOIN users u ON u.id=r.requester_id LEFT JOIN feb_request_items i ON i.request_id=r.id WHERE ((r.scope='chantier' AND EXISTS(SELECT 1 FROM feb_chantier_access a WHERE a.chantier_id=r.chantier_id AND a.user_id=? AND a.access_role=?)) OR (r.scope='bureau' AND ".($role==='demander'?"r.requester_id=?":"1=1")."))";
  $params=[$user,$role];if($role==='demander')$params[]=$user;
  if($requester!==null){$sql.=' AND r.requester_id=?';$params[]=$requester;}
  $sql.=' GROUP BY r.id ORDER BY r.id DESC LIMIT 500';$q=$this->pdo->prepare($sql);$q->execute($params);return $q->fetchAll()?:[];
 }
 public function find(int $id,bool $lock=false):?array{$q=$this->pdo->prepare('SELECT r.*,s.code chantier_code,s.nom chantier_name,w.label workflow_label,st.label step_label FROM feb_requests r LEFT JOIN btp_chantier_starts s ON s.id=r.chantier_id JOIN feb_workflows w ON w.id=r.workflow_id LEFT JOIN feb_workflow_steps st ON st.id=r.current_step_id WHERE r.id=?'.($lock?' FOR UPDATE':''));$q->execute([$id]);$r=$q->fetch();if(!$r)return null;foreach(['items'=>'SELECT i.*,cl.description budget_description FROM feb_request_items i LEFT JOIN btp_prestart_budget_lines bl ON bl.id=i.budget_line_id LEFT JOIN btp_client_order_lines cl ON cl.id=bl.order_line_id WHERE i.request_id=? ORDER BY i.id','documents'=>'SELECT * FROM feb_documents WHERE request_id=? ORDER BY id','decisions'=>'SELECT d.*,s.label step_label,u.full_name actor_name FROM feb_decisions d JOIN feb_workflow_steps s ON s.id=d.step_id JOIN users u ON u.id=d.actor_id WHERE d.request_id=? ORDER BY d.id','payments'=>'SELECT p.*,u.full_name actor_name FROM feb_payments p JOIN users u ON u.id=p.recorded_by WHERE p.request_id=? ORDER BY p.id','history'=>'SELECT h.*,u.full_name actor_name FROM feb_history h LEFT JOIN users u ON u.id=h.actor_id WHERE h.request_id=? ORDER BY h.id'] as $key=>$sql){$x=$this->pdo->prepare($sql);$x->execute([$id]);$r[$key]=$x->fetchAll();}return $r;}
 public function document(int $request,int $document):?array{$q=$this->pdo->prepare('SELECT * FROM feb_documents WHERE id=? AND request_id=?');$q->execute([$document,$request]);return $q->fetch()?:null;}
 public function stepAssignedUser(int $stepId):?int{$q=$this->pdo->prepare('SELECT assigned_user_id FROM feb_workflow_steps WHERE id=?');$q->execute([$stepId]);$id=$q->fetchColumn();return $id===false||$id===null?null:(int)$id;}
 public function currentStepPermission(int $request):string{$q=$this->pdo->prepare('SELECT s.permission_action FROM feb_requests r JOIN feb_workflow_steps s ON s.id=r.current_step_id WHERE r.id=?');$q->execute([$request]);$action=(string)$q->fetchColumn();return in_array($action,['view','create','update','delete'],true)?$action:'update';}
}
