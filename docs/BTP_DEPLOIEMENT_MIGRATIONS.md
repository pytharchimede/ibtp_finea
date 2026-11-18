# Déploiement et migrations FINEA BTP

Le fichier `bootstrap/app.php` appelle `MigrationRunner::run()` à chaque démarrage applicatif.
Ainsi, les migrations idempotentes sont automatiquement exécutées après mise à jour du code.
Pour un déploiement contrôlé, exécuter **avant de rendre le site accessible** :

```bash
php bin/migrate.php
```

Le script CLI charge le même bootstrap et vérifie le résultat. Arrêter le déploiement si le code de sortie est non nul. Sauvegarder la base avant toute migration ; l'utilisateur SQL doit posséder les droits ALTER, CREATE et INDEX. Les migrations DDL MySQL ne sont pas atomiques entre elles : prévoir une sauvegarde et un plan de retour arrière.

## Référence interne des projets

Format `PRJ-AAAA-NNNNNN`. Génération exclusivement côté serveur dans une transaction, après insertion et obtention de l'ID auto-incrémenté. Le numéro dérive de l'ID et reste unique même en création simultanée. La référence d'un projet historique n'est pas modifiée.

## Validation

```bash
php -l app/Repositories/Btp/ProjetRepository.php
php -l app/Services/Btp/ProjetService.php
php -l views/btp/projet_create.php
php bin/migrate.php
```
