<?php
declare(strict_types=1);
namespace App\Services\Btp;
use DomainException;
/**
 * Validation des métadonnées d'une signature vérifiée par un fournisseur de confiance.
 * ATTENTION : ce composant ne réalise pas la vérification cryptographique.
 */
final class DevisSignaturePolicy
{
    public static function assertVerified(array $signature, int $actorId, int $devisId, int $version): void
    {
        if ($actorId <= 0 || $devisId <= 0 || $version <= 0) {
            throw new DomainException('Identité ou document invalide.');
        }
        if (($signature['verified'] ?? false) !== true) {
            throw new DomainException('La signature doit être vérifiée avant approbation.');
        }
        if (!in_array($signature['method'] ?? null, ['passkey','signature_electronique'], true)) {
            throw new DomainException('Méthode de signature non prise en charge.');
        }
        if (($signature['actor_id'] ?? null) !== $actorId
            || ($signature['devis_id'] ?? null) !== $devisId
            || ($signature['version'] ?? null) !== $version) {
            throw new DomainException('La signature ne correspond pas au signataire ou au devis.');
        }
        if (!is_string($signature['document_sha256'] ?? null)
            || !preg_match('/^[a-f0-9]{64}$/D', $signature['document_sha256'])) {
            throw new DomainException('Empreinte documentaire invalide.');
        }
        if (!is_string($signature['proof_reference'] ?? null)
            || trim($signature['proof_reference']) === ''
            || strlen($signature['proof_reference']) > 255) {
            throw new DomainException('Preuve de signature manquante.');
        }
    }
}
