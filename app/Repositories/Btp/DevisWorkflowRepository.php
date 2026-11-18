<?php
declare(strict_types=1);
namespace App\Repositories\Btp;
use PDO;
final class DevisWorkflowRepository
{
    public function __construct(private PDO $pdo) {}
    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {$result=$callback();$this->pdo->commit();return $result;}
        catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
    public function lockDevis(int $id): ?array
    {
        $s=$this->pdo->prepare('SELECT id,projet_id,version,statut FROM btp_devis WHERE id=:id FOR UPDATE');
        $s->execute(['id'=>$id]);return $s->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function steps(): array
    {
        return $this->pdo->query('SELECT id,code,libelle,ordre FROM btp_devis_workflow_etapes WHERE actif=1 ORDER BY ordre,id')->fetchAll(PDO::FETCH_ASSOC);
    }
    public function latestDecisions(int $devisId,int $version): array
    {
        $s=$this->pdo->prepare('SELECT v.etape_id,v.decision FROM btp_devis_visas v JOIN (SELECT etape_id,MAX(id) latest_id FROM btp_devis_visas WHERE devis_id=:id AND version=:version GROUP BY etape_id) x ON x.latest_id=v.id');
        $s->execute(['id'=>$devisId,'version'=>$version]);$out=[];
        foreach($s->fetchAll(PDO::FETCH_ASSOC) as $row)$out[(int)$row['etape_id']]=$row['decision'];
        return $out;
    }
    public function assigned(int $stepId,int $userId): bool
    {
        $s=$this->pdo->prepare('SELECT 1 FROM btp_devis_workflow_affectations WHERE etape_id=:step AND user_id=:user LIMIT 1');
        $s->execute(['step'=>$stepId,'user'=>$userId]);return (bool)$s->fetchColumn();
    }
    public function record(int $devisId,int $version,int $stepId,string $decision,int $userId,?string $comment): int
    {
        $s=$this->pdo->prepare('INSERT INTO btp_devis_visas(devis_id,version,etape_id,decision,acteur_id,commentaire) VALUES(:devis,:version,:step,:decision,:actor,:comment)');
        $s->execute(['devis'=>$devisId,'version'=>$version,'step'=>$stepId,'decision'=>$decision,'actor'=>$userId,'comment'=>$comment]);
        return (int)$this->pdo->lastInsertId();
    }
    public function recordSignature(int $visaId,int $devisId,int $version,int $actorId,string $method,string $hash,string $proof): void
    {
        $s=$this->pdo->prepare('INSERT INTO btp_devis_signatures(visa_id,devis_id,devis_version,signataire_id,methode,document_sha256,preuve_reference) VALUES(:visa,:devis,:version,:actor,:method,:hash,:proof)');
        $s->execute(['visa'=>$visaId,'devis'=>$devisId,'version'=>$version,'actor'=>$actorId,'method'=>$method,'hash'=>$hash,'proof'=>$proof]);
    }
    public function status(int $id,string $status): void
    {
        $s=$this->pdo->prepare('UPDATE btp_devis SET statut=:status WHERE id=:id');
        $s->execute(['status'=>$status,'id'=>$id]);
    }
    public function reopenNewVersion(int $id,int $oldVersion): void
    {
        $s=$this->pdo->prepare('UPDATE btp_devis SET version=version+1,statut=\'brouillon\' WHERE id=:id AND version=:version');
        $s->execute(['id'=>$id,'version'=>$oldVersion]);
        if($s->rowCount()!==1)throw new \DomainException('Conflit de version du devis.');
    }
}
