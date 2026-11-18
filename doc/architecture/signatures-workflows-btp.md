# Validation signée des devis — état d'implémentation
Le service `DevisWorkflowService::decide()` exige maintenant un objet `VerifiedDevisSignature`, lie le visa et la preuve en transaction et interdit les décisions sans signature. Refus/correction incrémente la version du devis et invalide les anciens visas pour la suite du workflow.

**Bloquant sécurité :** `VerifiedDevisSignature::fromTrustedVerifier()` est un point d'intégration, pas un vérificateur cryptographique. Ne jamais construire cet objet avec les paramètres HTTP. Il faut un adaptateur WebAuthn/Passkey vérifiant challenge, origin, RP ID, authentificateur, signature, compteur et association de l'utilisateur, ou un prestataire de signature électronique certifié. Il faut aussi figer et recalculer côté serveur l'empreinte du PDF canonique, vérifier la décision et l'étape signées, gérer la révocation/expiration, et tester le rejeu.

**Bloquant métier :** l'incrément de version ne réalise pas encore la duplication immuable des lignes et du PDF. Les devis et leur version doivent être figés en base avant d'ouvrir les validations. Le circuit Technique → DG est provisoire.

**Statut :** code non exposé en HTTP, non testé en intégration, non déployable. Migrations manuelles sur base de recette uniquement.
