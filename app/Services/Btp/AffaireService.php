<?php

declare(strict_types=1);

namespace App\Services\Btp;

use App\Repositories\Btp\AffaireRepository;
use InvalidArgumentException;

final class AffaireService
{
    public function __construct(private AffaireRepository $repository) {}

    public function list(int $limit = 50, int $offset = 0): array
    {
        return $this->repository->list($limit, $offset);
    }

    public function create(array $input, ?int $authorId): int
    {
        $data = [];
        foreach (['reference'=>40,'intitule'=>180,'client_nom'=>180] as $field=>$max) {
            $value = trim((string) ($input[$field] ?? ''));
            if ($value === '' || mb_strlen($value) > $max) {
                throw new InvalidArgumentException('Champ invalide : ' . $field);
            }
            $data[$field] = $value;
        }
        $budget = (string) ($input['budget_previsionnel'] ?? '0');
        if (!preg_match('/^(?:0|[1-9][0-9]{0,15})(?:\\.[0-9]{1,2})?$/', $budget)) {
            throw new InvalidArgumentException('Budget invalide');
        }
        $data['budget_previsionnel'] = $budget;
        return $this->repository->create($data, $authorId);
    }
}
