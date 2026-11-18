<?php
declare(strict_types=1);
namespace App\Services\Btp;
use PDO;
use InvalidArgumentException;
final class DossierService
{
    public function __construct(private PDO $pdo) {}
    public function project(int $id): ?array {
        $q=$this->pdo->prepare('SELECT p.*,o.description,o.deadline,o.estimated_amount,o.currency,ao.url_source FROM btp_projets p LEFT JOIN crm_opportunities o ON o.id=p.crm_opportunity_id LEFT JOIN crm_ao_notices ao ON ao.crm_opportunity_id=o.id WHERE p.id=?');
        $q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:null;
    }
    public function events(int $id):array {
        $q=$this->pdo->prepare('SELECT * FROM btp_dossier_events WHERE projet_id=? ORDER BY occurred_at DESC,id DESC LIMIT 300');
        $q->execute([$id]);return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function quotes(int $id):array {
        $q=$this->pdo->prepare('SELECT * FROM btp_dossier_quotes WHERE projet_id=? ORDER BY revision DESC');
        $q->execute([$id]);return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function addEvent(int $id,array $data,?int $actor):void {
        $type=(string)($data['event_type']??'');
        if(!in_array($type,['soumission','demande_client','reponse','negociation','reunion','decision','note'],true))throw new InvalidArgumentException('Type d’événement invalide.');
        $subject=trim((string)($data['subject']??''));
        $details=trim((string)($data['details']??''));
        if($id<1||$subject===''||mb_strlen($subject)>180||mb_strlen($details)>10000)throw new InvalidArgumentException('Contenu invalide.');
        $q=$this->pdo->prepare('INSERT INTO btp_dossier_events(projet_id,event_type,subject,details,created_by) VALUES (?,?,?,?,?)');
        $q->execute([$id,$type,$subject,$details,$actor]);
    }
    public function addQuote(int $id,array $data,?int $actor):void {
        $label=trim((string)($data['label']??''));
        $amount=trim((string)($data['amount_ht']??''));
        if($id<1||$label===''||mb_strlen($label)>180||!preg_match('/^\\d{1,15}(?:\\.\\d{1,2})?$/',$amount))throw new InvalidArgumentException('Intitulé ou montant du devis invalide.');
        $this->pdo->beginTransaction();
        try {
            $lock=$this->pdo->prepare('SELECT id FROM btp_projets WHERE id=? FOR UPDATE');
            $lock->execute([$id]);
            if(!$lock->fetchColumn())throw new InvalidArgumentException('Projet introuvable.');
            $q=$this->pdo->prepare('SELECT COALESCE(MAX(revision),0)+1 FROM btp_dossier_quotes WHERE projet_id=?');
            $q->execute([$id]);$revision=(int)$q->fetchColumn();
            $insert=$this->pdo->prepare('INSERT INTO btp_dossier_quotes(projet_id,revision,label,amount_ht,created_by) VALUES (?,?,?,?,?)');
            $insert->execute([$id,$revision,$label,$amount,$actor]);
            $this->pdo->commit();
        } catch (\Throwable $e) {if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
}
