# FINEA Commercial — branding et catalogue de modules

## Source et sécurité

Le dépôt privé banamur_planification sert de référence fonctionnelle et de
conception. Le dépôt ibtp_finea est une application commerciale distincte.
Ne jamais y transférer les secrets, données d'exploitation ou informations
confidentielles de Banamur. Toute reprise de code suppose une revue des droits
et de ses dépendances.

## Personnaliser FINEA sans modifier les contrôleurs

1. Sélectionner un profil par variable d'environnement serveur
   FINEA_BRAND_PROFILE=ibtp ou FINEA_BRAND_PROFILE=finea.
2. Adapter config/brands/ibtp.php : raison sociale, slogan, couleurs et logo.
3. Déposer le fichier logo dans public/assets/logos/ ou public/assets/images/.
   Renseigner le chemin relatif, par exemple logos/ibtp.svg.
4. Les layouts ERP chargent la feuille générée via /branding/theme.css.
5. Le logo doit être un actif local, non une URL extérieure.
6. Le site public hérité du transit doit être reconstruit avant activation IBTP.

## Activer ou désactiver les modules

1. Sélectionner FINEA_MODULE_PROFILE=ibtp (défaut) ou default.
2. Éditer config/module-profiles/ibtp.php.
3. Le portail masque un module désactivé et le middleware refuse aussi les
   accès directs. Une activation ne donne jamais de permission utilisateur.
4. Conserver les contrôles ACL, CSRF, audit et validation métier existants.

Le profil IBTP désactive site-admin, car le site public hérité contient encore
des fonctionnalités transit. Administration reste protégée par les ACL.

## Charte de programmation

Flux : Route → Controller → Service → Repository → PDO.
Les contrôleurs ne contiennent pas de SQL. Les vues échappent les sorties.
CSS et JavaScript sont séparés. Respecter CONTRIBUTING.md.

## Validation et livraison

- Contrôle syntaxique PHP, suite PHPUnit et smoke tests.
- Vérification manuelle du branding en bureau, mobile et impression.
- Vérification URL directe et permissions d'un module désactivé.
- Branche refactor vers test-update, puis develop, puis main après validation.

## Travaux à poursuivre

- Extraire et généraliser les workflows FEB, RH, achats, devis et chantiers.
- Cartographier puis retirer les dépendances transit dans code et base.
- Centraliser le branding des PDF, courriels, notifications et site public.
- Prévoir historique, droits et audit pour une future édition du branding
  depuis l'administration.
- Ajouter migrations versionnées et tests d'intégration des modules extraits.

## Sécurité d’installation

Les paramètres de base sont fournis par l'environnement du serveur ; aucun
mot de passe DB n'est embarqué. Le compte administrateur initial n'est créé
que si FINEA_BOOTSTRAP_ADMIN_EMAIL et FINEA_BOOTSTRAP_ADMIN_PASSWORD sont
fournis, avec un mot de passe de 12 caractères minimum. Retirer ces variables
après le provisionnement. Aucun compte existant n'est élevé automatiquement.
