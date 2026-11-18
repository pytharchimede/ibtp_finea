<?php
declare(strict_types=1);
namespace App\Services\Btp;
use App\Repositories\Btp\ProjetRepository;
use InvalidArgumentException;
final class ProjetService
{
    public function __construct(private ProjetRepository $repository) {}
    public function list(): array { return $this->repository->list(); }
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
        return $this->repository->create($data,$author);
    }
}
