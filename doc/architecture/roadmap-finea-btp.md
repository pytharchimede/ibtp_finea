# FINEA BTP — Domaines métier et ordre de migration

## Principe
La base commerciale actuelle est issue d'un ERP logistique/transit. Ne pas assimiler les tableaux de bord existants à des modules BTP fonctionnels. La source Banamur est une référence en lecture seule. Aucune donnée, aucun secret, aucune personnalisation Banamur ne doit être transféré.

## Modules BTP cibles
1. **Affaires et chantiers** : client, affaire, chantier, état, responsable, dates, budget initial, historique et pièces jointes.
2. **Études et devis** : postes, quantités, prix unitaires, HT, taxes, TTC, versions, approbations, travaux supplémentaires (TS).
3. **Achats et approvisionnements** : demandes, fournisseurs, consultations, commandes, réceptions, imputation chantier.
4. **Budget et coûts réels** : budget par affaire/lot, engagements, déboursés, reste à engager, marges prévisionnelles/réelles.
5. **FEB et trésorerie** : demandes de décaissement, validations, justificatifs, paiement, régularisation et piste d'audit.
6. **RH et affectations** : personnel, équipes, présences, affectations et points financiers.
7. **Pilotage DG** : portefeuille chantiers, échéances, trésorerie, alertes, indicateurs de marge.

## Contrats d'architecture
- Routes → contrôleurs → services → repositories → PDO ; validations serveur et ACL à chaque action.
- Identifiants stables, clés étrangères et migrations idempotentes ; montants en DECIMAL, jamais FLOAT.
- Un chantier appartient à une affaire ; devis et achats sont imputés à l'affaire/chantier.
- Chaque module expose son statut de disponibilité via ModuleRegistry ; ne pas activer un module sans route, ACL, tests et migration.
- Branding et base URL uniquement via config/.env ; aucun nom de client codé en dur.
- Aucun accès direct aux données d'un autre client ; prévoir une stratégie explicite d'isolation des instances.

## Lot 1 — Affaires et chantiers
- Migrations pour affaires, chantiers et événements de suivi, avec index et contraintes.
- Repository + service + contrôleur + routes + formulaires, liste, fiche détail.
- Permissions consulter/créer/modifier/archiver ; journal d'audit.
- Tests de validation, ACL, intégrité référentielle et isolation.

## Conditions de promotion
feature/* → develop → test-update → main, après lint PHP, tests ciblés, revue migrations, recette fonctionnelle et absence de secrets. Les migrations ne s'exécutent pas sur production avant validation.
