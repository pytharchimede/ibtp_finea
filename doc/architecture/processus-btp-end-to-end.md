# FINEA BTP — Processus de bout en bout (référence métier)

## Principe directeur
FINEA BTP est un ERP de processus multi-acteurs, et non une collection d'écrans CRUD. Une affaire conserve sa traçabilité depuis l'opportunité / l'appel d'offres jusqu'à la réception, la facturation et la clôture. La source FINEA Banamur sert de référence fonctionnelle, sans copie de données ni de secrets.

## Chaîne métier
1. **Veille AO et opportunités** : publication, pièces DCE, critères, échéances, responsable, décision go/no-go.
2. **Études et chiffrage** : métrés, lots, postes de devis, quantités, prix unitaires, déboursés prévisionnels, marge, versions et visas.
3. **Soumission** : dossier administratif, technique et financier, contrôles de complétude, validations, dépôt et preuve de dépôt.
4. **Attribution** : offres concurrentes si disponibles, négociation, devis retenu/version figée, notification, contrat, montant de marché.
5. **Préparation** : chantier(s), budget de référence, planning, ressources, approvisionnement, sous-traitance par ligne/quantité, autorisation de démarrage.
6. **Exécution** : avancement physique, engagements achats, réceptions, FEB et décaissements, coûts réels, TS/avenants, alertes dérive.
7. **Réception** : situations de travaux, factures, retenues, réserves, levée des réserves, clôture financière et bilan de marge.

## Workflow et acteurs
- Rôles indicatifs : commercial, études, technique, achats, conducteur de travaux, RH, finance, comptabilité, DG, sous-traitant, client/maître d'ouvrage.
- Chaque dossier porte un responsable, des participants, des échéances, des tâches, des décisions signées/horodatées et des pièces justificatives.
- Les transitions sont explicites et conditionnelles (soumis, retenu, rejeté, à corriger, abandonné, suspendu). Un refus n'efface jamais l'historique.
- Séparer la permission d'agir de la personne affectée : ACL + affectation + état + règles métier, contrôlées côté serveur.
- Le moteur de workflow doit être configurable par profil commercial, versionné et auditable ; aucune transition directe arbitraire par simple modification de statut.

## Traçabilité financière par ligne
- AO → affaire → version de devis → lot → ligne → allocation budget → commande/sous-traitance → réception/prestation → FEB → paiement → coût réel.
- Conserver l'identifiant de la ligne source, les quantités prévues, sous-traitées, exécutées et facturées ; permettre la sous-traitance partielle à plusieurs prestataires sans double engagement.
- Distinguer prix de vente, déboursé prévisionnel, budget approuvé, engagé, réceptionné, payé et reste à engager ; aucun calcul de marge uniquement sur les décaissements.
- Les TS/avenants ont leur propre approbation et version budgétaire, sans écraser la base du marché.
- Toute modification du devis retenu doit passer par une révision/avenant traçable.

## Agrégats et schéma à concevoir
- ao_opportunities, ao_documents, bid_decisions, bid_submissions, bid_versions
- btp_affaires, btp_marches, btp_chantiers, btp_lots, btp_devis, btp_devis_versions, btp_devis_lignes
- btp_budget_versions, btp_budget_lignes, btp_planning_tasks, btp_progress_records
- btp_subcontracts, btp_subcontract_lines, purchase_commitments, cost_entries, disbursement_links
- workflow_instances, workflow_tasks, workflow_decisions, workflow_events, dossier_attachments

## Implications sur le lot initial
Le formulaire actuel de création d'affaire est un prototype, **pas** le point d'entrée définitif du processus. Avant fusion, rattacher l'affaire à une opportunité ou documenter explicitement une création hors AO, définir les permissions, les transitions et la migration automatisée contrôlée. Les futures lignes de devis retenues seront la source du budget chantier.

## Critères d'acceptation
- Une opportunité peut être refusée, soumise, attribuée ou perdue sans rupture d'historique.
- Aucun marché n'est démarré sans devis retenu et budget approuvé, sauf dérogation explicitement validée et auditée.
- Une ligne peut être sous-traitée partiellement ; les allocations cumulées ne dépassent pas les quantités autorisées sans avenant.
- Les engagements et paiements ne sont pas confondus ; rapprochement traçable avec chaque ligne de budget.
- Les rôles et délégations sont contrôlés côté serveur ; tests de transitions, concurrence, ACL et montants.

## Livraison
feature/* → develop → test-update → main après lint, tests, migrations sur base de recette et validation. Ne jamais toucher la base de production Banamur.
