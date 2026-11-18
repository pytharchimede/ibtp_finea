<?php
declare(strict_types=1);
namespace App\Controllers\Crm;
use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Middleware\AuthMiddleware;
use App\Models\Database;
use PDO;
final class CrmRecordsController extends BaseController {
 private function guard():void{AuthMiddleware::check();if(!Auth::user()?->isAdmin){http_response_code(403);exit('Accès CRM réservé aux administrateurs.');}}
 private function show(string $kind):void{
  $this->guard();$pdo=Database::getConnection();
  if($kind==='opportunities'){
   $rows=$pdo->query('SELECT o.id,o.title,o.stage,o.estimated_amount,o.currency,o.expected_close_date,c.name AS client_name FROM crm_opportunities o JOIN crm_clients c ON c.id=o.client_id ORDER BY o.id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
  }else{
   $rows=$pdo->query('SELECT i.id,i.channel,i.subject,i.interaction_at,i.next_action_date,c.name AS client_name FROM crm_interactions i JOIN crm_clients c ON c.id=i.client_id ORDER BY i.id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
  }
  $this->view('crm/records',['pageTitle'=>$kind==='opportunities'?'Opportunités CRM':'Interactions CRM','crmKind'=>$kind,'crmRows'=>$rows]);
 }
 public function opportunities():void{$this->show('opportunities');}
 public function interactions():void{$this->show('interactions');}
}
