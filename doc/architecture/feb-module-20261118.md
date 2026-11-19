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


## FEB Bureau et FEB Chantier (19/11/2026)

Le même assistant employé (/espace-employe/demandes/nouvelle?type=feb) propose deux affectations distinctes :

- **Chantier** : chantier autorisé via feb_chantier_access (rôle demander) → ligne de devis, représentée dans le périmètre BTP par btp_client_order_lines → déboursé validé (btp_prestart_budgets, relié par feb_chantier_budgets) → ligne de déboursé (btp_prestart_budget_lines). Les quatre identifiants sont recoupés côté serveur avant création. Une ligne de besoin ne peut pas être rattachée à un autre devis ou déboursé.
- **Bureau** : service RH actif (rh_services) → ligne d'affectation financière annualisée dans feb_office_budget_allocations. Seul l'administrateur peut créer ces allocations ; le montant budgété constitue un plafond lors de la soumission et de chaque validation/paiement. La FEB bureau utilise scope='bureau', service_id et office_allocation_id avec chantier_id NULL et budget_line_id NULL.

Les FEB Bureau restent protégées par les permissions FEB globales, le demandeur propriétaire et les validations ordonnées du workflow. Les FEB Chantier conservent en plus les restrictions chantier. Les deux sont conservées dans feb_requests et listées sous « Mes demandes » sans collision avec employee_legal_requests.

### Déploiement en recette

1. Sauvegarder la base MariaDB.
2. Appliquer une seule fois **database/migrations/20261119_002_feb_office_budget.sql**, après la migration initiale 20261118_001.
3. Vérifier les tables et colonnes ; créer les affectations bureau depuis **/admin/feb**.
4. Tester un compte possédant un rôle demander sur le chantier 1, puis une FEB Bureau avec un service et une allocation de l'exercice.
5. Valider les tests unitaires PHP et le parcours navigateur (création brouillon → envoi → décision → paiement).

Cette évolution ne crée **aucune allocation financière fictive** à l'installation. L'affectation doit être saisie par un administrateur et contrôlée par le service avant d'être utilisée.
