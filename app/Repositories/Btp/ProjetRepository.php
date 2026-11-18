<?php
declare(strict_types=1);
namespace App\Repositories\Btp;
use PDO;
final class ProjetRepository
{
    public function __construct(private PDO $pdo) {}
    public function summary(): array
    {
        return $this->pdo->query("SELECT COUNT(*) AS total,COALESCE(SUM(statut='etude'),0) AS etudes,COALESCE(SUM(statut='soumission'),0) AS soumissions,COALESCE(SUM(statut='execution'),0) AS execution FROM btp_projets")->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    public function list(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare('SELECT id,reference,intitule,client_nom,client_id,crm_opportunity_id,statut FROM btp_projets ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':lim', max(1,min(100,$limit)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function opportunities(): array
    {
        return $this->pdo->query("SELECT o.id,o.title,o.description,o.estimated_amount,o.currency,o.deadline,o.client_id,c.name AS client_name,ao.url_source AS source_url FROM crm_opportunities o JOIN crm_clients c ON c.id=o.client_id LEFT JOIN crm_ao_notices ao ON ao.crm_opportunity_id=o.id ORDER BY o.id DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function opportunity(int $id): ?array
    {
        $q=$this->pdo->prepare("SELECT o.id,o.title,o.description,o.estimated_amount,o.currency,o.deadline,o.client_id,c.name AS client_name,ao.url_source AS source_url FROM crm_opportunities o JOIN crm_clients c ON c.id=o.client_id LEFT JOIN crm_ao_notices ao ON ao.crm_opportunity_id=o.id WHERE o.id=? LIMIT 1");
        $q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function clients(): array
    {
        return $this->pdo->query("SELECT id,name FROM crm_clients WHERE type IN ('client','prospect') ORDER BY name LIMIT 300")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function create(array $data, ?int $author): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO btp_projets(reference,intitule,client_nom,client_id,crm_opportunity_id,created_by) VALUES (:ref,:name,:client,:client_id,:opportunity,:author)');
        $stmt->execute(['ref'=>$data['reference'],'name'=>$data['intitule'],'client'=>$data['client_nom'],'client_id'=>$data['client_id'],'opportunity'=>$data['opportunity_id'],'author'=>$author]);
        return (int) $this->pdo->lastInsertId();
    }
}
