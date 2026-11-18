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
        try {
            $result=$callback();
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
    public function lockDevis(int $id): ?array
    {
        $stmt=$this->pdo->prepare('SELECT id,projet_id,version,statut FROM btp_devis WHERE id=:id FOR UPDATE');
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function steps(): array
    {
        return $this->pdo->query('SELECT id,code,libelle,ordre FROM btp_devis_workflow_etapes WHERE actif=1 ORDER BY ordre,id')->fetchAll(PDO::FETCH_ASSOC);
    }
    public function latestDecisions(int $devisId,int $version): array
    {
        $stmt=$this->pdo->prepare('SELECT v.etape_id,v.decision FROM btp_devis_visas v JOIN (SELECT etape_id,MAX(id) AS latest_id FROM btp_devis_visas WHERE devis_id=:id AND version=:version GROUP BY etape_id) latest ON latest.latest_id=v.id');
        $stmt->execute(['id'=>$devisId,'version'=>$version]);
        $out=[];
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $out[(int)$row['etape_id']]=$row['decision'];
        return $out;
    }
    public function assigned(int $stepId,int $userId): bool
    {
        $stmt=$this->pdo->prepare('SELECT 1 FROM btp_devis_workflow_affectations WHERE etape_id=:step AND user_id=:user LIMIT 1');
        $stmt->execute(['step'=>$stepId,'user'=>$userId]);
        return (bool)$stmt->fetchColumn();
    }
    public function record(int $devisId,int $version,int $stepId,string $decision,int $userId,?string $comment): void
    {
        $stmt=$this->pdo->prepare('INSERT INTO btp_devis_visas(devis_id,version,etape_id,decision,acteur_id,commentaire) VALUES(:devis,:version,:step,:decision,:actor,:comment)');
        $stmt->execute(['devis'=>$devisId,'version'=>$version,'step'=>$stepId,'decision'=>$decision,'actor'=>$userId,'comment'=>$comment]);
    }
    public function status(int $devisId,string $status): void
    {
        $stmt=$this->pdo->prepare('UPDATE btp_devis SET statut=:status WHERE id=:id');
        $stmt->execute(['status'=>$status,'id'=>$devisId]);
    }
}
