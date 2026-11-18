# FINEA BTP — Modèle métier validé : Projet → Devis → BC → Chantier

## Terminologie impérative
- **Projet** : dossier commercial lié à un client, incluant AO éventuel, devis et acteurs. Remplace le terme « affaire » dans l'interface et le modèle cible.
- **Devis** : généré dans FINEA, versionné, soumis à un workflow interne jusqu'à validation finale, puis envoyé au client.
- **Bon de commande (BC) client** : reçu après acceptation du devis ; pièce justificative obligatoire pour créer le chantier.
- **Chantier** : unité d'exécution issue d'un BC, avec planning, déboursés et suivi des dépenses.
- **Déboursé** : coût prévisionnel de réalisation (matériaux, main-d'œuvre, matériel, sous-traitance...), distinct du montant vendu.

## Workflow principal
Client → Projet → Devis brouillon → visas internes configurables → devis approuvé → transmission client → acceptation → BC client enregistré/contrôlé → chantier autorisé → budget de déboursés → exécution → réception et clôture.

Un projet peut avoir plusieurs versions de devis, plusieurs BC et plusieurs chantiers selon les règles commerciales ; chaque chantier doit référencer son BC source. Les devis refusés, remplacés et révisés restent historisés.

## Sous-traitance
Une ou plusieurs lignes/tâches du chantier peuvent être exécutées en interne ou sous-traitées, totalement ou partiellement. La proposition du sous-traitant est versionnée et suit les visas **RH, Juridique, Technique, Logistique, HSE, DG**. Les validations sont individualisées, horodatées et liées à la version exacte de l'offre ; refus et demandes de correction sont tracés. L'ordre ou le parallélisme des visas est paramétrable, sans supprimer les validations obligatoires. Une offre entièrement approuvée devient un engagement et alimente le déboursé prévisionnel/actualisé, sans double comptabilisation de la ligne d'origine.

## Pilotage financier
- Chiffre d'affaires contractuel = montants HT des BC/marchés approuvés, ajustés des avenants (à distinguer du CA facturé et du CA reconnu comptablement).
- Déboursé initial, déboursé révisé, engagements, coûts constatés, décaissements payés et reste à réaliser sont suivis séparément.
- Marge prévisionnelle = CA contractuel HT - coût prévisionnel total ; marge à terminaison = CA contractuel HT actualisé - coût total estimé à terminaison.
- Le DG voit CA contractuel, facturé, encaissé, budget, engagements, paiements, marges et alertes par projet et chantier.

## Modèle de données cible
clients → btp_projets → btp_devis (versions et lignes) → btp_bons_commande_client → btp_chantiers → btp_debourses (versions et lignes) → btp_engagements / btp_depenses / FEB.
Sous-traitance : btp_sous_traitances → btp_offres_prestataires (versions) → btp_visas_offre (service, décision, auteur, horodatage) → btp_engagements.
Workflow transverse : instances, tâches, participants, décisions, délégations et événements immuables.

## Migration de l'existant en cours de développement
Le prototype `btp_affaires`, `AffaireRepository`, `AffaireService`, `AffaireController` et l'écran « Affaires » **ne constituent pas le modèle cible**. Ne pas exécuter la migration SQL manuelle précédente sur la base client. Remplacer ces éléments par le modèle `btp_projets` et les relations devis/BC/chantier avant promotion de branche. Aucun renommage destructif ni migration automatique des données existantes sans plan de conversion et sauvegarde.

## Tests indispensables
- Impossibilité de créer un chantier sans BC client valide et devis approuvé.
- Impossibilité de transmettre un devis non validé.
- Visas RH/Juridique/Technique/Logistique/HSE/DG tous requis sur la même version d'offre.
- Pas de double comptage des déboursés sous-traités ; gestion des sous-traitances partielles.
- ACL par rôle et acteur affecté ; audit des transitions et cohérence des montants.
