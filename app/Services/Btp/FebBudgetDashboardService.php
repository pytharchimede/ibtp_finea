<?php
declare(strict_types=1);
namespace App\Services\Btp;
use PDO;
final class FebBudgetDashboardService {
 public function __construct(private PDO $pdo){}
 public function summary():array{
  $sql="SELECT COALESCE(SUM(bl.total_cost),0) planned,
   COALESCE((SELECT SUM(i.total_amount) FROM feb_request_items i JOIN feb_requests r ON r.id=i.request_id WHERE r.scope='chantier' AND r.status NOT IN ('brouillon','refuse','annule')),0) committed,
   COALESCE((SELECT SUM(i.total_amount) FROM feb_request_items i JOIN feb_requests r ON r.id=i.request_id WHERE r.scope='chantier' AND r.status IN ('approuve','partiellement_paye','paye')),0) approved,
   COALESCE((SELECT SUM(amount) FROM feb_payments pay JOIN feb_requests req ON req.id=pay.request_id WHERE req.scope='chantier'),0) paid
   FROM btp_prestart_budget_lines bl JOIN btp_prestart_budgets b ON b.id=bl.budget_id AND b.status='valide'";
  $r=$this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);return $r?:['planned'=>0,'committed'=>0,'approved'=>0,'paid'=>0];
 }
}
