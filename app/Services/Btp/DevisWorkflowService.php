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
        $this->repository->transaction(function()use($devisId):void{
            $d=$this->requireDevis($devisId);
            if($d['statut']!=='brouillon')throw new DomainException('Devis non modifiable.');
            if(!$this->repository->steps())throw new DomainException('Circuit non configuré.');
            $this->repository->status($devisId,'validation_interne');
        });
    }
    /**
     * Signatures must originate from a server-side verified provider.
     * No signature = no decision. A trusted verifier must be integrated before exposing an endpoint.
     */
    public function decide(int $devisId,int $userId,string $decision,VerifiedDevisSignature $signature,?string $comment=null): void
    {
        if(!in_array($decision,['approuve','rejete','correction'],true))throw new DomainException('Décision invalide.');
        if($userId<=0)throw new DomainException('Acteur invalide.');
        $comment=trim((string)$comment);
        if($decision!=='approuve'&&$comment==='')throw new DomainException('Motif obligatoire.');
        if(mb_strlen($comment)>2000)throw new DomainException('Commentaire trop long.');
        $this->repository->transaction(function()use($devisId,$userId,$decision,$signature,$comment):void{
            $d=$this->requireDevis($devisId);
            if($d['statut']!=='validation_interne')throw new DomainException('Devis non soumis.');
            if($signature->actorId!==$userId||$signature->devisId!==$devisId||$signature->version!==(int)$d['version'])
                throw new DomainException('Signature non liée à cette version et à cet acteur.');
            $steps=$this->repository->steps();
            if(!$steps)throw new DomainException('Circuit absent.');
            $latest=$this->repository->latestDecisions($devisId,(int)$d['version']);
            $pending=null;
            foreach($steps as $step){if(($latest[(int)$step['id']]??null)!=='approuve'){$pending=$step;break;}}
            if(!$pending)throw new DomainException('Circuit déjà approuvé.');
            if(!$this->repository->assigned((int)$pending['id'],$userId))throw new DomainException('Acteur non habilité.');
            $visa=$this->repository->record($devisId,(int)$d['version'],(int)$pending['id'],$decision,$userId,$comment?:null);
            $this->repository->recordSignature($visa,$devisId,(int)$d['version'],$userId,$signature->method,$signature->documentHash,$signature->proofReference);
            if($decision!=='approuve'){$this->repository->reopenNewVersion($devisId,(int)$d['version']);return;}
            $remaining=array_filter($steps,static fn(array $s):bool=>(int)$s['id']!==(int)$pending['id']&&($latest[(int)$s['id']]??null)!=='approuve');
            if(!$remaining)$this->repository->status($devisId,'approuve');
        });
    }
    public function markSent(int $devisId): void
    {
        $this->repository->transaction(function()use($devisId):void{
            $d=$this->requireDevis($devisId);
            if($d['statut']!=='approuve')throw new DomainException('Devis non approuvé.');
            $this->repository->status($devisId,'envoye');
        });
    }
    private function requireDevis(int $id): array
    {
        if($id<=0||!($d=$this->repository->lockDevis($id)))throw new DomainException('Devis introuvable.');
        return $d;
    }
}
