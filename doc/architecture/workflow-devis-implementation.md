# Workflow de devis — première implémentation (service métier)
- Circuit initial configurable : Technique (ordre 10), DG (ordre 20). **Ordre provisoire à confirmer avec IBTP**.
- Aucun utilisateur n'est automatiquement habilité : renseigner explicitement `btp_devis_workflow_affectations` après contrôle des droits.
- Un devis brouillon est soumis, puis visé étape par étape sur sa version courante ; les décisions sont horodatées, liées à un acteur et à une étape.
- Refus ou correction renvoie au brouillon ; toute nouvelle soumission doit créer une nouvelle version de devis ou invalider les visas précédents. **Cette invalidation n'est pas encore implémentée : ne pas exposer le service en production.**
- L'envoi client n'est possible qu'après approbation complète.
- Ce lot ne contient pas encore l'interface, les permissions applicatives, la gestion des versions, ni les tests d'intégration. Ne pas fusionner avant résolution.
