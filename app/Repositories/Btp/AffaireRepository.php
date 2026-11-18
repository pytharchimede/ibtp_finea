<?php

declare(strict_types=1);

namespace App\Repositories\Btp;

use PDO;

final class AffaireRepository
{
    public function __construct(private PDO $pdo) {}

    public function list(int $limit = 50, int $offset = 0): array
    {
        $statement = $this->pdo->prepare('SELECT id, reference, intitule, client_nom, statut, budget_previsionnel, date_debut_prevue, date_fin_prevue FROM btp_affaires ORDER BY id DESC LIMIT :lim OFFSET :off');
        $statement->bindValue(':lim', max(1, min($limit, 100)), PDO::PARAM_INT);
        $statement->bindValue(':off', max(0, $offset), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM btp_affaires WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data, ?int $authorId): int
    {
        $statement = $this->pdo->prepare('INSERT INTO btp_affaires(reference,intitule,client_nom,budget_previsionnel,created_by) VALUES(:reference,:intitule,:client_nom,:budget,:author)');
        $statement->execute(['reference'=>$data['reference'],'intitule'=>$data['intitule'],'client_nom'=>$data['client_nom'],'budget'=>$data['budget_previsionnel'],'author'=>$authorId]);
        return (int) $this->pdo->lastInsertId();
    }
}
