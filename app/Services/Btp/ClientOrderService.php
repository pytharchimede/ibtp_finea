<?php
declare(strict_types=1);
namespace App\Services\Btp;
use PDO;
use InvalidArgumentException;
final class ClientOrderService {
    public function __construct(private PDO $pdo) {}
    public function all(): array {return $this->pdo->query("SELECT bc.*,p.reference AS project_reference,p.intitule,p.client_nom,q.title AS offer_title,q.revision FROM btp_client_orders bc JOIN btp_projets p ON p.id=bc.projet_id JOIN btp_offer_drafts q ON q.id=bc.offer_id ORDER BY bc.id DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);}
    public function projects(): array {return $this->pdo->query("SELECT id,reference,intitule,client_nom FROM btp_projets ORDER BY id DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);}
    public function offers(int $id): array {$q=$this->pdo->prepare('SELECT id,revision,title,total_ht,status FROM btp_offer_drafts WHERE projet_id=? ORDER BY revision DESC');$q->execute([$id]);return $q->fetchAll(PDO::FETCH_ASSOC);}
    public function lines(int $project,int $offer): array {$q=$this->pdo->prepare('SELECT l.* FROM btp_offer_lines l JOIN btp_offer_drafts d ON d.id=l.offer_id WHERE d.projet_id=? AND d.id=? ORDER BY l.line_order,l.id');$q->execute([$project,$offer]);return $q->fetchAll(PDO::FETCH_ASSOC);}
    public function save(int $project,array $input,array $file,?int $actor): int {
        $reference=trim((string)($input['reference']??''));$date=(string)($input['received_at']??'');$offer=(int)($input['offer_id']??0);
        $d=\DateTimeImmutable::createFromFormat('!Y-m-d',$date);
        if($project<1||$offer<1||$reference===''||mb_strlen($reference)>80||!$d||$d->format('Y-m-d')!==$date)throw new InvalidArgumentException('Référence, date ou devis invalide.');
        $q=$this->pdo->prepare('SELECT total_ht FROM btp_offer_drafts WHERE id=? AND projet_id=?');$q->execute([$offer,$project]);$total=$q->fetchColumn();
        $lines=$this->lines($project,$offer);
        if($total===false||!$lines)throw new InvalidArgumentException('Le devis choisi est introuvable ou ne contient aucune ligne.');
        $q=$this->pdo->prepare('SELECT id FROM btp_client_orders WHERE projet_id=? AND reference=?');$q->execute([$project,$reference]);if($q->fetchColumn())throw new InvalidArgumentException('Cette référence de BC existe déjà.');
        // Stocker la pièce dans la bibliothèque privée, puis lier son identifiant au BC.
        (new DossierService($this->pdo))->uploadDocument($project,['category'=>'client','label'=>'BC '.$reference],$file,$actor);
        $q=$this->pdo->prepare("SELECT id FROM btp_documents WHERE projet_id=? AND category='client' AND label=? ORDER BY id DESC LIMIT 1");$q->execute([$project,'BC '.$reference]);$doc=(int)$q->fetchColumn();
        if(!$doc)throw new \RuntimeException('Pièce justificative non enregistrée.');
        $this->pdo->beginTransaction();
        try {
            $lock=$this->pdo->prepare('SELECT id FROM btp_projets WHERE id=? FOR UPDATE');$lock->execute([$project]);if(!$lock->fetchColumn())throw new InvalidArgumentException('Projet introuvable.');
            $q=$this->pdo->prepare('INSERT INTO btp_client_orders(projet_id,offer_id,reference,received_at,document_id,amount_ht,created_by) VALUES(?,?,?,?,?,?,?)');$q->execute([$project,$offer,$reference,$date,$doc,$total,$actor]);$id=(int)$this->pdo->lastInsertId();
            $q=$this->pdo->prepare('INSERT INTO btp_client_order_lines(order_id,offer_line_id,line_order,group_name,description,unit,quantity,unit_price,line_total) VALUES(?,?,?,?,?,?,?,?,?)');
            foreach($lines as $l)$q->execute([$id,$l['id'],$l['line_order'],$l['group_name'],$l['description'],$l['unit'],$l['quantity'],$l['unit_price'],$l['line_total']]);
            $this->pdo->commit();return $id;
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
}
