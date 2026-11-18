# Signatures des workflows FINEA BTP
## Règle
Un visa d'approbation ne vaut que pour une **version immuable** du document et une **preuve de signature vérifiée côté serveur**. Les refus et demandes de correction sont également attribués, horodatés et auditables.

## Modèle
- Signataire : utilisateur identifié et autorisé pour l'étape ; rôle, affectation et délégation conservés.
- Objet signé : devis/version, décision, étape, horodatage et SHA-256 des octets canoniques du document.
- Preuve : référence vers une assertion WebAuthn/passkey vérifiée par le serveur ou un prestataire de signature électronique.
- Les clés privées et secrets d'authentification ne sont jamais stockés dans le registre de visas.
- Le PDF affichera l'identité, le rôle, l'horodatage, l'empreinte et l'identifiant de preuve ; une image de signature seule n'est pas une preuve cryptographique.

## Intégration bloquante à développer
1. Générer et figer la version du devis et son PDF canonique avant validation.
2. Vérifier l'assertion passkey côté serveur (challenge unique, origine, RP ID, authentificateur, association à l'utilisateur et au hash/document).
3. Effectuer dans **une seule transaction** l'insertion du visa et de la preuve ; refuser tout visa approuvé sans preuve vérifiée.
4. Empêcher la réutilisation d'une preuve ; stocker la décision et l'empreinte signées.
5. Sur correction : nouvelle version, nouveaux visas, anciennes preuves conservées en lecture seule.
6. Ajouter les tests de falsification, de rejeu, d'identité, d'ACL, de concurrence et d'intégrité du document.

## État du code
La table et la politique de validation des métadonnées sont des **préparatifs**. Elles ne prouvent pas une signature et ne sont pas encore branchées sur `DevisWorkflowService::decide()`. Ce service **ne doit pas être exposé aux utilisateurs** tant que la signature vérifiée et le versionnement n'y sont pas imposés. Ne pas déployer en production.
