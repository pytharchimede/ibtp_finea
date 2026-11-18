<?php
declare(strict_types=1);
namespace App\Repositories\Btp;
use PDO;
final class ProjetRepository
{
    public function __construct(private PDO $pdo) {}
    public function list(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare('SELECT id,reference,intitule,client_nom,statut FROM btp_projets ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':lim', max(1,min(100,$limit)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function create(array $data, ?int $author): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO btp_projets(reference,intitule,client_nom,created_by) VALUES (:ref,:name,:client,:author)');
        $stmt->execute(['ref'=>$data['reference'],'name'=>$data['intitule'],'client'=>$data['client_nom'],'author'=>$author]);
        return (int) $this->pdo->lastInsertId();
    }
}
