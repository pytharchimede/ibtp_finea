<?php
declare(strict_types=1);
namespace App\Services\Btp;
use App\Repositories\Btp\DevisWorkflowRepository;
use DomainException;
final class DevisWorkflowService
{
    public function __construct(private DevisWorkflowRepository $repository) {}
    public function submit(int $devisId): void
    {
        $this->repository->transaction(function() use($devisId): void {
            $devis=$this->requireDevis($devisId);
            if($devis['statut']!=='brouillon') throw new DomainException('Seul un brouillon peut être soumis.');
            if(!$this->repository->steps()) throw new DomainException('Aucun circuit de validation configuré.');
            $this->repository->status($devisId,'validation_interne');
        });
    }
    public function decide(int $devisId,int $userId,string $decision,?string $comment=null): void
    {
        if(!in_array($decision,['approuve','rejete','correction'],true)) throw new DomainException('Décision invalide.');
        if($userId<=0) throw new DomainException('Acteur invalide.');
        $comment=trim((string)$comment);
        if($decision!=='approuve' && $comment==='') throw new DomainException('Motif obligatoire.');
        if(mb_strlen($comment)>2000) throw new DomainException('Commentaire trop long.');
        $this->repository->transaction(function() use($devisId,$userId,$decision,$comment): void {
            $devis=$this->requireDevis($devisId);
            if($devis['statut']!=='validation_interne') throw new DomainException('Devis non soumis à validation.');
            $steps=$this->repository->steps();
            if(!$steps) throw new DomainException('Circuit de validation absent.');
            $latest=$this->repository->latestDecisions($devisId,(int)$devis['version']);
            $pending=null;
            foreach($steps as $step) {
                if(($latest[(int)$step['id']] ?? null)!=='approuve') { $pending=$step; break; }
            }
            if(!$pending) throw new DomainException('Tous les visas sont déjà acquis.');
            if(!$this->repository->assigned((int)$pending['id'],$userId)) throw new DomainException('Vous ne pouvez pas valider cette étape.');
            $this->repository->record($devisId,(int)$devis['version'],(int)$pending['id'],$decision,$userId,$comment?:null);
            if($decision!=='approuve') {
                $this->repository->status($devisId,'brouillon');
                return;
            }
            $remaining=array_filter($steps,static fn(array $s): bool => (int)$s['id']!==(int)$pending['id'] && ($latest[(int)$s['id']] ?? null)!=='approuve');
            if(!$remaining) $this->repository->status($devisId,'approuve');
        });
    }
    public function markSent(int $devisId): void
    {
        $this->repository->transaction(function() use($devisId): void {
            $devis=$this->requireDevis($devisId);
            if($devis['statut']!=='approuve') throw new DomainException('Le devis doit être approuvé avant envoi.');
            $this->repository->status($devisId,'envoye');
        });
    }
    private function requireDevis(int $id): array
    {
        if($id<=0 || !($devis=$this->repository->lockDevis($id))) throw new DomainException('Devis introuvable.');
        return $devis;
    }
}
