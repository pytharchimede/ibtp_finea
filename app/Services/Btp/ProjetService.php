<?php
declare(strict_types=1);
namespace App\Services\Btp;
use App\Repositories\Btp\ProjetRepository;
use InvalidArgumentException;
final class ProjetService
{
    public function __construct(private ProjetRepository $repository) {}
    public function list(): array { return $this->repository->list(); }
    public function opportunities(): array { return $this->repository->opportunities(); }
    public function summary(): array { return $this->repository->summary(); }
    public function clients(): array { return $this->repository->clients(); }
    public function create(array $input, ?int $author): int
    {
        $opportunityId=filter_var($input['opportunity_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if(!$opportunityId)throw new InvalidArgumentException('Sélectionnez une opportunité CRM.');
        $opportunity=$this->repository->opportunity((int)$opportunityId);
        if(!$opportunity)throw new InvalidArgumentException('Opportunité CRM introuvable.');
        return $this->repository->create([
            'intitule'=>mb_substr((string)$opportunity['title'],0,180),
            'client_nom'=>mb_substr((string)$opportunity['client_name'],0,180),
            'client_id'=>(int)$opportunity['client_id'],
            'opportunity_id'=>(int)$opportunityId,
        ],$author);
    }
}
