<?php
declare(strict_types=1);
namespace App\Repositories\Crm;
use PDO;
final class CrmAnalyticsRepository {
 public function __construct(private PDO $pdo){}
 public function overview():array{
  $counts=$this->pdo->query("SELECT COUNT(*) AS total, SUM(type='client') AS clients, SUM(type='prospect') AS prospects FROM crm_clients")->fetch(PDO::FETCH_ASSOC);
  $pipeline=$this->pdo->query("SELECT COUNT(*) AS total, COALESCE(SUM(estimated_amount),0) AS amount FROM crm_opportunities")->fetch(PDO::FETCH_ASSOC);
  $actions=$this->pdo->query("SELECT COUNT(*) AS total FROM crm_interactions WHERE next_action_date IS NOT NULL AND next_action_date <= CURRENT_DATE()")->fetch(PDO::FETCH_ASSOC);
  $stages=$this->pdo->query("SELECT stage, COUNT(*) AS total, COALESCE(SUM(estimated_amount),0) AS amount FROM crm_opportunities GROUP BY stage ORDER BY total DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
  $recent=$this->pdo->query("SELECT c.id,c.name,c.type,c.status,c.created_at FROM crm_clients c ORDER BY c.id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
  $upcoming=$this->pdo->query("SELECT i.id,i.subject,i.next_action_date,c.name AS client_name FROM crm_interactions i JOIN crm_clients c ON c.id=i.client_id WHERE i.next_action_date IS NOT NULL AND i.next_action_date <= DATE_ADD(CURRENT_DATE(), INTERVAL 14 DAY) ORDER BY i.next_action_date ASC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
  return ['counts'=>$counts,'pipeline'=>$pipeline,'actions'=>$actions,'stages'=>$stages,'recent'=>$recent,'upcoming'=>$upcoming];
 }
}
