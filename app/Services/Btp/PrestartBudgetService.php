<?php
declare(strict_types=1);
namespace App\Services\Btp;
use PDO;
use InvalidArgumentException;
final class PrestartBudgetService {
    public function __construct(private PDO $pdo) {}
    public function orders(): array {
        return $this->pdo->query("SELECT bc.id,bc.reference,bc.projet_id,bc.amount_ht,p.reference AS project_reference,p.intitule,p.client_nom FROM btp_client_orders bc JOIN btp_projets p ON p.id=bc.projet_id ORDER BY bc.id DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function order(int $id): ?array {
        $q=$this->pdo->prepare("SELECT bc.*,p.reference AS project_reference,p.intitule,p.client_nom,d.revision AS quote_revision FROM btp_client_orders bc JOIN btp_projets p ON p.id=bc.projet_id JOIN btp_offer_drafts d ON d.id=bc.offer_id WHERE bc.id=?");
        $q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:null;
    }
    public function lines(int $id): array {
        $q=$this->pdo->prepare('SELECT id,line_order,group_name,description,unit,quantity,unit_price,line_total FROM btp_client_order_lines WHERE order_id=? ORDER BY line_order,id');$q->execute([$id]);return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function all(): array {
        return $this->pdo->query("SELECT b.*,bc.reference AS order_reference,p.intitule,p.client_nom FROM btp_prestart_budgets b JOIN btp_client_orders bc ON bc.id=b.order_id JOIN btp_projets p ON p.id=b.projet_id ORDER BY b.id DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function save(int $orderId,array $data,?int $actor): int {
        $order=$this->order($orderId);if(!$order)throw new InvalidArgumentException('Bon de commande introuvable.');
        $lines=$this->lines($orderId);if(!$lines)throw new InvalidArgumentException('Le BC ne contient aucune ligne.');
        $input=$data['costs']??null;if(!is_array($input)||count($input)!==count($lines))throw new InvalidArgumentException('Toutes les lignes du BC doivent être chiffrées.');
        $parsed=[];$sum=0;
        foreach($lines as $line){
            $id=(int)$line['id'];$costs=$input[$id]??null;
            if(!is_array($costs))throw new InvalidArgumentException('Coûts manquants pour la ligne '.$id);
            $values=[];$total=0;
            foreach(['materials','labor','equipment','subcontracting','other_cost'] as $field){
                $raw=trim((string)($costs[$field]??''));
                if(!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/',$raw))throw new InvalidArgumentException('Montant invalide : '.$field.' / ligne '.$id);
                $cents=(int)round((float)$raw*100);$total+=$cents;$values[]=number_format($cents/100,2,'.','');
            }
            $parsed[]=[$id,...$values,number_format($total/100,2,'.','')];$sum+=$total;
        }
        $this->pdo->beginTransaction();
        try{
            $q=$this->pdo->prepare('SELECT id FROM btp_client_orders WHERE id=? FOR UPDATE');$q->execute([$orderId]);if(!$q->fetchColumn())throw new InvalidArgumentException('BC introuvable.');
            $q=$this->pdo->prepare('SELECT COALESCE(MAX(revision),0)+1 FROM btp_prestart_budgets WHERE order_id=?');$q->execute([$orderId]);$rev=(int)$q->fetchColumn();
            $q=$this->pdo->prepare('INSERT INTO btp_prestart_budgets(order_id,projet_id,revision,total_cost,expected_margin,created_by) VALUES(?,?,?,?,?,?)');
            $q->execute([$orderId,$order['projet_id'],$rev,number_format($sum/100,2,'.',''),number_format((int)round((float)$order['amount_ht']*100)/100-$sum/100,2,'.',''),$actor]);$budgetId=(int)$this->pdo->lastInsertId();
            $q=$this->pdo->prepare('INSERT INTO btp_prestart_budget_lines(budget_id,order_line_id,materials,labor,equipment,subcontracting,other_cost,total_cost) VALUES(?,?,?,?,?,?,?,?)');
            foreach($parsed as $line)$q->execute([$budgetId,...$line]);
            $this->pdo->commit();return $budgetId;
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
}
