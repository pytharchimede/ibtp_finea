-- Prototype BTP v2 : exécuter sur une base de recette après sauvegarde.
CREATE TABLE IF NOT EXISTS btp_projets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 reference VARCHAR(40) NOT NULL UNIQUE,
 intitule VARCHAR(180) NOT NULL,
 client_nom VARCHAR(180) NOT NULL,
 statut ENUM('prospect','etude','soumission','commande','execution','clos','abandonne') NOT NULL DEFAULT 'prospect',
 created_by INT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS btp_devis (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 projet_id BIGINT UNSIGNED NOT NULL,
 reference VARCHAR(60) NOT NULL UNIQUE,
 version INT UNSIGNED NOT NULL DEFAULT 1,
 statut ENUM('brouillon','validation_interne','approuve','envoye','accepte','refuse') NOT NULL DEFAULT 'brouillon',
 montant_ht DECIMAL(18,2) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CHECK (montant_ht >= 0),
 FOREIGN KEY (projet_id) REFERENCES btp_projets(id) ON DELETE RESTRICT,
 KEY idx_btp_devis_projet (projet_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS btp_bons_commande_client (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 projet_id BIGINT UNSIGNED NOT NULL,
 devis_id BIGINT UNSIGNED NOT NULL,
 reference VARCHAR(80) NOT NULL,
 montant_ht DECIMAL(18,2) NOT NULL,
 date_reception DATE NOT NULL,
 justificatif VARCHAR(255) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CHECK (montant_ht >= 0),
 UNIQUE KEY uq_btp_bc_projet_reference (projet_id,reference),
 FOREIGN KEY (projet_id) REFERENCES btp_projets(id) ON DELETE RESTRICT,
 FOREIGN KEY (devis_id) REFERENCES btp_devis(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS btp_chantiers (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 projet_id BIGINT UNSIGNED NOT NULL,
 bon_commande_id BIGINT UNSIGNED NOT NULL,
 code VARCHAR(40) NOT NULL UNIQUE,
 nom VARCHAR(180) NOT NULL,
 statut ENUM('preparation','actif','suspendu','termine','archive') NOT NULL DEFAULT 'preparation',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (projet_id) REFERENCES btp_projets(id) ON DELETE RESTRICT,
 FOREIGN KEY (bon_commande_id) REFERENCES btp_bons_commande_client(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Le contrôle devis accepté + BC du même projet doit être réalisé dans une transaction métier.
