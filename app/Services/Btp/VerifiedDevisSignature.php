<?php
declare(strict_types=1);
namespace App\Services\Btp;
/** Only a server-side cryptographic verifier may construct this object. */
final class VerifiedDevisSignature
{
    private function __construct(
        public readonly int $actorId,
        public readonly int $devisId,
        public readonly int $version,
        public readonly string $method,
        public readonly string $documentHash,
        public readonly string $proofReference
    ) {}
    /** @internal Called by a verified authentication/signature adapter, never from request input. */
    public static function fromTrustedVerifier(int $actorId,int $devisId,int $version,string $method,string $hash,string $proof): self
    {
        DevisSignaturePolicy::assertVerified([
            'verified'=>true,'actor_id'=>$actorId,'devis_id'=>$devisId,'version'=>$version,
            'method'=>$method,'document_sha256'=>$hash,'proof_reference'=>$proof
        ],$actorId,$devisId,$version);
        return new self($actorId,$devisId,$version,$method,$hash,$proof);
    }
}
