<?php
declare(strict_types=1);
namespace App\Database;
use PDO;

/** Non-destructive initialisation du module BTP. Aucune donnée de démonstration. */
final class BtpMigrationRunner
{
    public function __construct(private PDO $pdo) {}
    public function run(): void
    {
        $statements = [
            "CREATE TABLE IF NOT EXISTS btp_projets (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                reference VARCHAR(40) NOT NULL UNIQUE,
                client_id INT UNSIGNED NOT NULL,
                intitule VARCHAR(180) NOT NULL,
                ao_reference VARCHAR(80) NULL,
                statut ENUM('prospect','etude','soumission','commande','execution','clos','abandonne') NOT NULL DEFAULT 'prospect',
                created_by INT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_btp_projets_client(client_id),
                CONSTRAINT fk_btp_projets_client FOREIGN KEY(client_id) REFERENCES crm_clients(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_projets_author FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_devis (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                projet_id BIGINT UNSIGNED NOT NULL,
                reference VARCHAR(60) NOT NULL UNIQUE,
                titre VARCHAR(180) NOT NULL,
                version INT UNSIGNED NOT NULL DEFAULT 1,
                statut ENUM('brouillon','validation_interne','approuve','envoye','accepte','refuse') NOT NULL DEFAULT 'brouillon',
                montant_ht DECIMAL(18,2) NOT NULL DEFAULT 0,
                document_hash CHAR(64) NULL,
                created_by INT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_btp_devis_projet(projet_id),
                CONSTRAINT fk_btp_devis_projet FOREIGN KEY(projet_id) REFERENCES btp_projets(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_devis_author FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_devis_lignes (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                devis_id BIGINT UNSIGNED NOT NULL,
                designation VARCHAR(255) NOT NULL,
                quantite DECIMAL(14,3) NOT NULL,
                prix_unitaire DECIMAL(18,2) NOT NULL,
                ordre INT UNSIGNED NOT NULL DEFAULT 0,
                CHECK(quantite > 0),
                CHECK(prix_unitaire >= 0),
                KEY idx_btp_lignes_devis(devis_id),
                CONSTRAINT fk_btp_lignes_devis FOREIGN KEY(devis_id) REFERENCES btp_devis(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_devis_versions (
                devis_id BIGINT UNSIGNED NOT NULL,
                version INT UNSIGNED NOT NULL,
                contenu_json LONGTEXT NOT NULL,
                document_sha256 CHAR(64) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY(devis_id,version),
                CONSTRAINT fk_btp_versions_devis FOREIGN KEY(devis_id) REFERENCES btp_devis(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_devis_workflow_etapes (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(50) NOT NULL UNIQUE,
                libelle VARCHAR(120) NOT NULL,
                ordre INT UNSIGNED NOT NULL UNIQUE,
                actif TINYINT(1) NOT NULL DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_devis_workflow_affectations (
                etape_id BIGINT UNSIGNED NOT NULL,
                user_id INT NOT NULL,
                PRIMARY KEY(etape_id,user_id),
                CONSTRAINT fk_btp_affect_etape FOREIGN KEY(etape_id) REFERENCES btp_devis_workflow_etapes(id) ON DELETE CASCADE,
                CONSTRAINT fk_btp_affect_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_devis_visas (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                devis_id BIGINT UNSIGNED NOT NULL,
                version INT UNSIGNED NOT NULL,
                etape_id BIGINT UNSIGNED NOT NULL,
                decision ENUM('approuve','rejete','correction') NOT NULL,
                commentaire TEXT NULL,
                acteur_id INT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_btp_visa_devis_version_etape(devis_id,version,etape_id),
                CONSTRAINT fk_btp_visas_devis FOREIGN KEY(devis_id) REFERENCES btp_devis(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_visas_etape FOREIGN KEY(etape_id) REFERENCES btp_devis_workflow_etapes(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_visas_acteur FOREIGN KEY(acteur_id) REFERENCES users(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_devis_signatures (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                visa_id BIGINT UNSIGNED NOT NULL UNIQUE,
                devis_id BIGINT UNSIGNED NOT NULL,
                devis_version INT UNSIGNED NOT NULL,
                signataire_id INT NOT NULL,
                methode ENUM('password_confirmation') NOT NULL,
                document_sha256 CHAR(64) NOT NULL,
                preuve_reference VARCHAR(255) NOT NULL UNIQUE,
                signature_hmac CHAR(64) NOT NULL,
                signe_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_btp_sign_visa FOREIGN KEY(visa_id) REFERENCES btp_devis_visas(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_sign_devis FOREIGN KEY(devis_id) REFERENCES btp_devis(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_sign_user FOREIGN KEY(signataire_id) REFERENCES users(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_bons_commande_client (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                projet_id BIGINT UNSIGNED NOT NULL,
                devis_id BIGINT UNSIGNED NOT NULL,
                reference VARCHAR(80) NOT NULL,
                montant_ht DECIMAL(18,2) NOT NULL,
                date_reception DATE NOT NULL,
                justificatif VARCHAR(255) NOT NULL,
                created_by INT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_btp_bc_reference(projet_id,reference),
                CONSTRAINT fk_btp_bc_projet FOREIGN KEY(projet_id) REFERENCES btp_projets(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_bc_devis FOREIGN KEY(devis_id) REFERENCES btp_devis(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_bc_user FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_chantiers (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                projet_id BIGINT UNSIGNED NOT NULL,
                bon_commande_id BIGINT UNSIGNED NOT NULL UNIQUE,
                code VARCHAR(40) NOT NULL UNIQUE,
                nom VARCHAR(180) NOT NULL,
                statut ENUM('preparation','actif','suspendu','termine','archive') NOT NULL DEFAULT 'preparation',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_btp_chantier_projet FOREIGN KEY(projet_id) REFERENCES btp_projets(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_chantier_bc FOREIGN KEY(bon_commande_id) REFERENCES btp_bons_commande_client(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_sous_traitances (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                chantier_id BIGINT UNSIGNED NOT NULL,
                devis_ligne_id BIGINT UNSIGNED NOT NULL,
                sous_traitant VARCHAR(180) NOT NULL,
                reference VARCHAR(60) NOT NULL UNIQUE,
                quantite DECIMAL(14,3) NOT NULL,
                montant_ht DECIMAL(18,2) NOT NULL,
                statut ENUM('soumise','approuvee','rejetee','correction') NOT NULL DEFAULT 'soumise',
                version INT UNSIGNED NOT NULL DEFAULT 1,
                document_hash CHAR(64) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CHECK(quantite > 0),
                CHECK(montant_ht >= 0),
                CONSTRAINT fk_btp_st_chantier FOREIGN KEY(chantier_id) REFERENCES btp_chantiers(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_st_ligne FOREIGN KEY(devis_ligne_id) REFERENCES btp_devis_lignes(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_sous_traitance_affectations (
                service_code VARCHAR(30) NOT NULL,
                user_id INT NOT NULL,
                PRIMARY KEY(service_code,user_id),
                CONSTRAINT fk_btp_st_affect_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_sous_traitance_visas (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sous_traitance_id BIGINT UNSIGNED NOT NULL,
                version INT UNSIGNED NOT NULL,
                service_code VARCHAR(30) NOT NULL,
                decision ENUM('approuve','rejete','correction') NOT NULL,
                commentaire TEXT NULL,
                acteur_id INT NOT NULL,
                document_sha256 CHAR(64) NOT NULL,
                preuve_reference CHAR(32) NOT NULL UNIQUE,
                signature_hmac CHAR(64) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_btp_st_visa_service(sous_traitance_id,version,service_code),
                CONSTRAINT fk_btp_st_visas_offer FOREIGN KEY(sous_traitance_id) REFERENCES btp_sous_traitances(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_st_visas_actor FOREIGN KEY(acteur_id) REFERENCES users(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_debourses (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                chantier_id BIGINT UNSIGNED NOT NULL,
                devis_ligne_id BIGINT UNSIGNED NULL,
                sous_traitance_id BIGINT UNSIGNED NULL UNIQUE,
                nature ENUM('interne','sous_traitance') NOT NULL,
                libelle VARCHAR(255) NOT NULL,
                budget_ht DECIMAL(18,2) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CHECK(budget_ht >= 0),
                CONSTRAINT fk_btp_budget_chantier FOREIGN KEY(chantier_id) REFERENCES btp_chantiers(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_budget_ligne FOREIGN KEY(devis_ligne_id) REFERENCES btp_devis_lignes(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_budget_st FOREIGN KEY(sous_traitance_id) REFERENCES btp_sous_traitances(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_depenses (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                debourse_id BIGINT UNSIGNED NOT NULL,
                categorie ENUM('engagement','constate','paiement') NOT NULL,
                montant_ht DECIMAL(18,2) NOT NULL,
                libelle VARCHAR(255) NOT NULL,
                created_by INT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CHECK(montant_ht > 0),
                CONSTRAINT fk_btp_depense_budget FOREIGN KEY(debourse_id) REFERENCES btp_debourses(id) ON DELETE RESTRICT,
                CONSTRAINT fk_btp_depense_user FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_planning (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                chantier_id BIGINT UNSIGNED NOT NULL,
                libelle VARCHAR(180) NOT NULL,
                debut_prevu DATE NOT NULL,
                fin_prevue DATE NOT NULL,
                statut ENUM('prevu','en_cours','termine') NOT NULL DEFAULT 'prevu',
                CONSTRAINT fk_btp_planning_chantier FOREIGN KEY(chantier_id) REFERENCES btp_chantiers(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS btp_audit (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                objet VARCHAR(50) NOT NULL,
                objet_id BIGINT UNSIGNED NOT NULL,
                action VARCHAR(60) NOT NULL,
                auteur_id INT NOT NULL,
                details_json LONGTEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_btp_audit_objet(objet,objet_id,created_at),
                CONSTRAINT fk_btp_audit_actor FOREIGN KEY(auteur_id) REFERENCES users(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];
        foreach ($statements as $sql) {
            $this->pdo->exec($sql);
        }
        $this->pdo->exec("INSERT INTO btp_devis_workflow_etapes(code,libelle,ordre)
            VALUES ('technique','Validation technique',10),('dg','Approbation DG',20)
            ON DUPLICATE KEY UPDATE libelle=VALUES(libelle)");
        // Éviter de continuer silencieusement avec une ancienne table prototype.
        $cols = $this->pdo->query("SHOW COLUMNS FROM btp_projets")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('client_id', $cols, true)) {
            throw new \RuntimeException('Ancien schéma BTP détecté : sauvegarder puis exécuter un plan de migration des projets.');
        }
    }
}
