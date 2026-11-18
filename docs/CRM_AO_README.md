# Veille appels d'offres — CRM IBTP

## Origine
Adaptation isolée du module AO de pytharchimede/banamur_planification :
AoOpportunity, AoScoringService, SigomapAoAdapter et
ContenuLocalAoAdapter. Aucun accès aux données, utilisateurs, secrets
ou bases de données BANAMUR n'est effectué.

## Installation
La migration MigrationRunner::createCrmAoTables() est exécutée avec le
bootstrap habituel et crée les tables crm_ao_notices et
crm_ao_sync_logs. Elle ne supprime aucune donnée.

Routes :
- /crm/veille-ao : cartes et filtres multicritères.
- /crm/veille-ao/detail?id=… : détail, liens sources, qualification et conversion.
- /crm/veille-ao/synchronisations : historique des synchronisations.
- /crm/veille-ao/export : export CSV filtré.

## Collecte et configuration
Exécuter côté serveur uniquement :
php bin/sync_crm_ao.php

Par défaut, le script interroge la page publique DGMP
https://marchespublics.ci/appel_offre. Les annonces DGH
nécessitent un compte dédié autorisé et les variables de configuration
locales FINEA_CONTENU_LOCAL_USERNAME et
FINEA_CONTENU_LOCAL_PASSWORD, ou un fichier privé défini par
FINEA_CONTENU_LOCAL_CONFIG. Ne jamais commiter d'identifiants.
L'absence de configuration DGH désactive cette source, sans bloquer DGMP.

Exemple cron sous Linux (adapter le chemin et l'utilisateur d'exécution) :
0 7 * * 1-5 cd /var/www/html/ibtp_finea && /usr/bin/php bin/sync_crm_ao.php >> /var/log/finea-ibtp-ao.log 2>&1

Le script impose un verrou MariaDB pour éviter deux collectes concurrentes,
et journalise les statistiques et erreurs par source.
Une erreur réseau n'est pas masquée par une annonce fictive.

## Cycle métier
Un appel d'offres importé conserve son état interne lors des synchronisations.
Le score est un indicateur lexical BTP ; il ne mesure pas la probabilité
réelle de remporter le marché.
La conversion nécessite une échéance non expirée et le choix explicite
d'un client/prospect IBTP existant. Une conversion répétée retrouve le
même identifiant CRM.

## Limites actuelles
Les notifications e-mail et rapports collectifs périodiques, les
décisions DG avec permissions dédiées et les pièces locales automatiques
ne sont pas encore repris. Le module ne déclenche aucun e-mail.
Les collectes réelles nécessitent tests d'intégration sur un serveur
disposant de PHP cURL, DOM, accès Internet et MariaDB.

Tester les adaptations sans réseau :
php tests/CrmAoSourceTest.php
