# Lot exécution et pilotage BTP (recette uniquement)

Ce lot ajoute les tables de lignes de devis, déboursés, dépenses et offres de sous-traitance, les visas signés versionnés, le service transactionnel BC → chantier et le calcul de pilotage HT par projet.

## Déploiement contrôlé
1. Sauvegarder la base de recette.
2. Appliquer dans l'ordre les SQL manuels : `20261118_btp_projets_devis_bc.sql`, `20261118_btp_devis_workflow.sql`, `20261118_btp_devis_signatures.sql`, `20261118_btp_devis_signature_proof_unique.sql`, puis `20261118_btp_execution_budget.sql`.
3. Les scripts ne sont **pas** exécutés automatiquement. Ne pas les appliquer à la production Banamur.
4. Ne pas considérer le workflow sous-traitant comme utilisable tant que la vérification des signatures, l'affectation des six services et les contrôles transactionnels ne sont pas raccordés.
5. Les indicateurs sont des agrégats contractuels/provisionnels et non du chiffre d'affaires reconnu comptablement. Un paiement est ici une dépense au statut `paye`, sans intégration FEB bancaire.
6. Une offre sous-traitante approuvée n'alimente pas encore automatiquement le déboursé : il faut une transaction de remplacement/ventilation avant d'activer cette fonction.
7. Les BC multiples sur un même devis et les révisions de devis nécessitent des règles de répartition contractuelle complémentaires.

## Critères restant avant livraison commerciale
Parcours web complet, lignes de devis versionnées, PDF devis, téléversement BC sécurisé, validations signées vérifiables, visas sous-traitants, droits par acteur, déboursés et dépenses FEB, planning, contrôle des marges et tests d'intégration sur MySQL/MariaDB. GitHub Actions étant indisponible, exécuter PHP lint et les tests localement avant promotion.
