-- FINEA BTP — Lot 1. Appliquer après sauvegarde, sur base de recette uniquement.
CREATE TABLE IF NOT EXISTS btp_affaires (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 reference VARCHAR(40) NOT NULL,
 intitule VARCHAR(180) NOT NULL,
 client_nom VARCHAR(180) NOT NULL,
 statut ENUM('prospection','etude','en_cours','terminee','archivee') NOT NULL DEFAULT 'prospection',
 budget_previsionnel DECIMAL(18,2) NOT NULL DEFAULT 0.00,
 date_debut_prevue DATE NULL,
 date_fin_prevue DATE NULL,
 created_by INT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_btp_affaires_reference(reference),
 KEY idx_btp_affaires_statut(statut),
 CONSTRAINT fk_btp_affaires_createur FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
 CONSTRAINT chk_btp_affaires_budget CHECK(budget_previsionnel >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS btp_chantiers (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 affaire_id BIGINT UNSIGNED NOT NULL,
 code VARCHAR(40) NOT NULL,
 nom VARCHAR(180) NOT NULL,
 localisation VARCHAR(255) NULL,
 statut ENUM('preparation','actif','suspendu','termine','archive') NOT NULL DEFAULT 'preparation',
 date_debut DATE NULL,
 date_fin_prevue DATE NULL,
 budget_previsionnel DECIMAL(18,2) NOT NULL DEFAULT 0.00,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_btp_chantiers_code(code),
 KEY idx_btp_chantiers_affaire(affaire_id),
 KEY idx_btp_chantiers_statut(statut),
 CONSTRAINT fk_btp_chantiers_affaire FOREIGN KEY(affaire_id) REFERENCES btp_affaires(id) ON DELETE RESTRICT,
 CONSTRAINT chk_btp_chantiers_budget CHECK(budget_previsionnel >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS btp_chantier_evenements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 chantier_id BIGINT UNSIGNED NOT NULL,
 type_evenement VARCHAR(60) NOT NULL,
 commentaire TEXT NOT NULL,
 auteur_id INT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_btp_evenements_chantier_date(chantier_id,created_at),
 CONSTRAINT fk_btp_evenements_chantier FOREIGN KEY(chantier_id) REFERENCES btp_chantiers(id) ON DELETE RESTRICT,
 CONSTRAINT fk_btp_evenements_auteur FOREIGN KEY(auteur_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
