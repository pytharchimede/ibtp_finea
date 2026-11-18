# Contrat fonctionnel de livraison — département technique BTP

## Source et séparation des responsabilités
- FINEA Banamur (`pytharchimede/banamur_planification`) : référence fonctionnelle, **aucune modification du dépôt d'origine**.
- Vérifié dans Banamur : `controllers/DevisOfferProjectController.php` regroupe les devis par Offre/Projet ; `debourse/repositories/DebourseRepository.php` suit devis, déboursés, sous-lignes et dépassements ; `decaissement/model/ProjectPlanningImport.php` gère des états d'avancement et l'archivage chantier.
- IBTP : CRM transmet opportunité et DCE ; le département technique pilote le dossier sans navigation CRM. Les données source restent liées par identifiants et permissions.

## Workflow cible et gardes métier
1. **Réception & qualification** : opportunité, AO, DCE, maître d'ouvrage, date limite, go/no-go, responsable étude et liste de pièces. Toute décision horodatée.
2. **Études** : visite de site, métrés, bordereau des prix, DQE/BPU, lots, hypothèses, risques, variantes, devis fournisseurs et sous-traitants. Les métrés et hypothèses sont versionnés.
3. **Chiffrage** : devis avec lignes, unités, quantités, PU, TVA, remises, HT/TTC ; plusieurs offres par projet, versions immuables après soumission. Déboursé sec et frais indirects séparés de la marge commerciale.
4. **Contrôle interne** : vérification technique, finance, DG selon seuils ; aucune soumission client d'une version non validée.
5. **Soumissions** : paquet de pièces administratives, techniques et financières ; chaque lot possède numéro, destinataire, canal, échéance, horodatage, manifeste des versions, preuve de transmission. Aucun remplacement silencieux d'une pièce envoyée.
6. **Demandes client** : RFI / demande de complément, clarification, négociation, non-conformité, réunion, réponse, action attribuée, échéance et statut. Les demandes sont reliées au paquet et au devis concernés.
7. **Attribution** : devis/version retenu, preuve d'acceptation, BC ou contrat, avenants. Une seule version contractuelle active par périmètre ; les versions antérieures demeurent consultables.
8. **Préparation** : budget initial dérivé des lignes du devis retenu, déboursé prévisionnel détaillé, jalons, Gantt, chemin critique, ressources, matériel, achats, sous-traitance, HSE et ordre de service.
9. **Exécution** : avancement physique par poste, journal chantier, rapports, photos, pointages, consommations, engagements, réceptions, FEB, paiements, écarts et alertes. Les dépenses sont rattachées aux lignes budgétaires.
10. **Modifications** : travaux supplémentaires et avenants chiffrés, approuvés et historisés avant mise à jour de la base budgétaire et du planning.
11. **Réception & clôture** : situations, attachements contradictoires, PV de réception provisoire/définitive, réserves, retenues de garantie, facturation, encaissements, bilan de marge et archivage.

## Modèle métier et invariants
- `btp_projets` est le dossier parent ; `crm_opportunity_id` conserve la provenance sans exposer le CRM.
- `btp_dossier_events` est actuellement un journal simple : **pas** un module de transmission de fichiers ou un moteur de workflow complet.
- `btp_dossier_quotes` est actuellement un historique de montants : **pas** encore un devis détaillé avec lignes, TVA, approbations et PDF.
- À créer/compléter : pièces documentaires privées avec versions et empreintes SHA-256 ; paquets de soumission et manifestes ; demandes client et tâches ; comptes rendus avec participants/décisions ; devis, lignes et révisions ; acceptation/BC ; déboursés par poste ; planning et dépendances ; chantier, avancement, coûts et réceptions.
- Les statuts ne sont pas des champs librement modifiables : transitions serveur, RBAC, audit et pièces justificatives obligatoires.
- Une pièce déjà soumise reste consultable, même si une nouvelle version est ajoutée ; ne jamais écraser son contenu.
- Un budget approuvé ne change que par une nouvelle version et un avenant validé.
- Distinguer prix de vente, déboursé sec, frais de chantier, frais généraux, marge, engagé, réceptionné, facturé, encaissé et payé.
- Prévoir plusieurs devis, lots, BC et chantiers par projet selon le marché ; ne pas supposer une relation un-à-un.

## Navigation technique cible
Pilotage (tableau de bord, alertes) ; Projets (portefeuille, dossiers, équipes) ; Études & AO (consultations, DCE, métrés) ; Soumissions (paquets, échanges, demandes, CR) ; Devis (chiffrage, versions, visas, attribution) ; Préparation (déboursés, achats, ressources, planning) ; Chantiers (avancement, journal, dépenses, TS) ; Réception (situations, PV, réserves, bilan).
Ne rendre cliquables que les écrans réellement implémentés.

## Ordre de livraison et critères de recette
1. Dossier et documents : upload privé, téléchargement autorisé, versionnement, recherche et tests ACL/CSRF/MIME/limites.
2. Soumissions et demandes : manifeste figé, preuve d'envoi, RFI, CR, relances, audit, tests de concurrence.
3. Devis : lignes et calculs, versions, PDF, visas, transmission et acceptation ; tests montants et verrouillage.
4. Déboursés, budgets et planning : quantités, postes, ressources, dépendances et baseline ; tests d'écarts et d'avenants.
5. Chantier et clôture : BC, OS, dépenses, avancement, rapports, situations, réceptions et marges ; tests de bout en bout.
6. Qualité : tests PHPUnit + parcours navigateur + migrations sur base vierge et existante + RBAC + sécurité fichiers + vérification mobile.
7. Intégration : feature → develop → test-update → main, sauvegarde et migration avant ouverture, sans toucher à Banamur.

**Définition de terminé** : toutes les étapes précédentes sont utilisables avec données persistées, contrôles serveur, tests exécutés et résultats vérifiés sur environnement de recette. Un écran ou une migration seuls ne suffisent pas.
