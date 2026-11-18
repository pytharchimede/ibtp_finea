# Module FEB FINEA–IBTP

La FEB est un agrégat propre à FINEA : `feb_requests`, `feb_request_items`, pièces, décisions, paiements et journal. Aucune page BANAMUR n’est copiée.

## Correspondance

| BANAMUR | FINEA–IBTP |
|---|---|
| chantier | `btp_chantier_starts` |
| ligne déboursé | `btp_prestart_budget_lines` d’un budget validé |
| conforme / approuvé | étapes ordonnées de `feb_workflow_steps` |
| décaissement | `feb_payments`, références uniques et paiements partiels |
| traces | `feb_history` et décisions immuables |

Les lignes budgétaires et la FEB sont verrouillées (`FOR UPDATE`) à la soumission, à chaque décision et au paiement. La colonne `version` fournit un verrou optimiste supplémentaire. Les montants du tableau de bord BTP distinguent prévisionnel, engagé, approuvé et payé.

L’accès chantier est fermé par défaut. `feb_chantier_budgets` désigne explicitement l’unique budget validé utilisé par la FEB ; `feb_chantier_access` attribue séparément les rôles `demander`, `valider` et `payer`. Une permission globale FEB ne donne donc accès à aucun chantier sans affectation correspondante.

L’administration est disponible sous `/admin/feb` pour les comptes administrateurs actifs. Elle protège les étapes des workflows déjà utilisés, les budgets consommés par une FEB et journalise les changements dans `feb_admin_history`. Les nouveaux justificatifs sont stockés sous `storage/private/feb`; la route autorisée `/feb/{id}/documents/{documentId}` conserve une résolution en lecture des anciens chemins `public/uploads/feb` pendant la transition.

## Déploiement contrôlé

Après sauvegarde en recette, appliquer explicitement `database/migrations/20261118_001_create_feb_module.sql`, puis exécuter `php bin/migrate.php` afin d’enregistrer l’entité de permission déclarée dans le registre. Attribuer `view/create` aux employés, `update` aux validateurs et `delete` à la finance chargée des paiements. Aucun DDL FEB n’est exécuté lors d’une requête HTTP.
