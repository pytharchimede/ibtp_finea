<?php
declare(strict_types=1);
namespace App\Services\Btp;
use App\Repositories\Btp\ProjetRepository;
use InvalidArgumentException;
final class ProjetService
{
    public function __construct(private ProjetRepository $repository) {}
    public function list(): array { return $this->repository->list(); }
    public function clients(): array { return $this->repository->clients(); }
    public function create(array $input, ?int $author): int
    {
        $data=[];
        foreach (['reference'=>40,'intitule'=>180,'client_nom'=>180] as $key=>$max) {
            $value=trim((string)($input[$key] ?? ''));
            if ($value === '' || mb_strlen($value)>$max) {
                throw new InvalidArgumentException('Champ invalide : '.$key);
            }
            $data[$key]=$value;
        }
        $clientId=filter_var($input['client_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $data['client_id']=$clientId ?: null;
        if ($data['client_id'] !== null) {
            $matches=array_filter($this->repository->clients(),static fn(array $c):bool=>(int)$c['id']===$data['client_id'] && $c['name']===$data['client_nom']);
            if (!$matches) throw new InvalidArgumentException('Client CRM invalide.');
        }
        return $this->repository->create($data,$author);
    }
}
