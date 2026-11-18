# Démarrer un projet BTP
1. Connectez-vous comme administrateur ou avec les droits BTP (lecture et création).
2. Depuis le portail, ouvrez « Projets & Chantiers » ou directement /btp/projets.
3. Renseignez une référence unique, l'intitulé et choisissez un client CRM (ou renseignez son nom manuellement).
4. Cliquez « Créer ». Le projet apparaît dans la liste.
5. La création d'un chantier requiert ensuite un devis approuvé et un bon de commande client : l'interface de cette deuxième étape n'est pas encore disponible.

Le socle `btp_projets` est créé automatiquement par MigrationRunner sur une instance à jour. Ne pas appliquer une seconde fois le script manuel historique de création de cette table sur une base déjà migrée. Les droits BTP doivent être attribués depuis l'administration. La configuration de maintenance doit autoriser le module BTP.
