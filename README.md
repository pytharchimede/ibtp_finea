# FINEA Commercial — ERP configurable

Ce projet est une application PHP MVC légère avec authentification, vues personnalisées et portail de sélection (dashboard/module portal). Il est pensé pour être simple à comprendre, à démarrer et à enrichir.

## Objectif du projet

- déployer une instance ERP par entreprise avec une configuration isolée
- comprendre l’architecture backend et frontend
- utiliser un système de connexion simple
- avoir un portail de sélection moderne pour les modules ERP

## Prérequis

- PHP 8.x
- Apache avec mod_rewrite
- MySQL / MariaDB
- WAMP, XAMPP ou un environnement PHP local

## Démarrage rapide

1. Placer le projet dans le dossier web et démarrer PHP et MySQL.
2. Définir les variables DB_*, APP_URL, FINEA_BRAND_PROFILE et FINEA_MODULE_PROFILE.
3. Avant la première installation, définir FINEA_BOOTSTRAP_ADMIN_EMAIL et FINEA_BOOTSTRAP_ADMIN_PASSWORD avec un mot de passe fort (12 caractères minimum).
4. Une fois le compte créé, retirer ces deux variables du serveur et se connecter avec les identifiants choisis.
5. Consulter doc/architecture/branding-et-modularite.md pour personnaliser l’instance.

## Structure principale

- app/ : contrôleurs, middlewares, modèles, services, repositories
- bootstrap/ : initialisation de l’application
- config/ : configuration de l’application et de la base
- public/ : point d’entrée HTTP, assets CSS/JS/images
- routes/ : routes web et API
- views/ : templates de pages et layouts
- storage/ : logs et fichiers temporaires

## Point d’entrée

- index.php : entrée racine du projet
- public/index.php : point d’entrée HTTP principal

## Documentation pour débutants

La documentation de référence est disponible dans les dossiers suivants :

- [Créer un module MVC de A à Z](doc/architecture/creer-un-module.md)
- [Architecture et navigation du module RH](doc/modules/rh.md)
- [Architecture du module Administration](doc/modules/admin.md)
- [Architecture du module Espace employé](doc/modules/employee.md)
- [Architecture du module Site Internet](doc/modules/site.md)
- [Mode maintenance des modules](doc/modules/maintenance.md)
- [doc/backend/readmeGeneral.md](doc/backend/readmeGeneral.md)
- [doc/backend/readmeAuthProcess.md](doc/backend/readmeAuthProcess.md)
- [doc/backend/readmeModel.md](doc/backend/readmeModel.md)
- [doc/backend/readmeController.md](doc/backend/readmeController.md)
- [doc/backend/readmeCSS.md](doc/backend/readmeCSS.md)
- [doc/backend/readmeJS.md](doc/backend/readmeJS.md)
- [doc/frontend/readme.md](doc/frontend/readme.md)

## Parcours rapide de l’application

1. l’utilisateur arrive sur la page de connexion
2. le formulaire envoie les identifiants vers le contrôleur d’authentification
3. le service vérifie les informations et crée la session
4. l’utilisateur est redirigé vers le dashboard / portail
5. la vue du portail affiche les modules de sélection

## Conseils

- commencez par lire les documents backend avant de modifier les contrôleurs
- modifiez les vues dans views/ et les styles dans public/assets/css/
- si vous ajoutez une nouvelle page, ajoutez d’abord la route puis le contrôleur

## Dépannage rapide

- si la page ne charge pas, vérifiez Apache et mod_rewrite
- si la connexion échoue, vérifiez l’utilisateur admin et la base de données
- si vous voyez une erreur de session, vérifiez le démarrage de la session dans bootstrap/app.php
