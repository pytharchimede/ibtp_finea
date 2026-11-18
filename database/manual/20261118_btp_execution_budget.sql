-- FINEA BTP : appliquer en recette après sauvegarde et les scripts projets/devis/BC.
-- Évolution additive, aucune destruction de données.
CREATE TABLE IF NOT EXISTS btp_devis_lignes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 devis_id BIGINT UNSIGNED NOT NULL,
 designation VARCHAR(255) NOT NULL,
 quantite DECIMAL(15,3) NOT NULL,
 prix_unitaire_ht DECIMAL(18,2) NOT NULL,
 montant_ht DECIMAL(18,2) GENERATED ALWAYS AS (ROUND(quantite * prix_unitaire_ht,2)) STORED,
 ordre INT NOT NULL DEFAULT 0,
 CHECK (quantite > 0), CHECK (prix_unitaire_ht >= 0),
 FOREIGN KEY (devis_id) REFERENCES btp_devis(id) ON DELETE RESTRICT,
 KEY idx_btp_lignes_devis (devis_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS btp_debourse_lignes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 chantier_id BIGINT UNSIGNED NOT NULL,
 devis_ligne_id BIGINT UNSIGNED NULL,
 categorie ENUM('materiaux','main_oeuvre','materiel','sous_traitance','autre') NOT NULL,
 libelle VARCHAR(255) NOT NULL,
 montant_prevu_ht DECIMAL(18,2) NOT NULL,
 montant_revise_ht DECIMAL(18,2) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CHECK (montant_prevu_ht >= 0), CHECK (montant_revise_ht >= 0),
 FOREIGN KEY (chantier_id) REFERENCES btp_chantiers(id) ON DELETE RESTRICT,
 FOREIGN KEY (devis_ligne_id) REFERENCES btp_devis_lignes(id) ON DELETE RESTRICT,
 KEY idx_btp_debourse_chantier (chantier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS btp_depenses (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 chantier_id BIGINT UNSIGNED NOT NULL,
 debourse_ligne_id BIGINT UNSIGNED NOT NULL,
 reference VARCHAR(80) NOT NULL,
 montant_ht DECIMAL(18,2) NOT NULL,
 statut ENUM('engage','constate','paye','annule') NOT NULL DEFAULT 'engage',
 created_by INT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CHECK (montant_ht >= 0),
 FOREIGN KEY (chantier_id) REFERENCES btp_chantiers(id) ON DELETE RESTRICT,
 FOREIGN KEY (debourse_ligne_id) REFERENCES btp_debourse_lignes(id) ON DELETE RESTRICT,
 FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
 UNIQUE KEY uq_btp_depense_reference (chantier_id,reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS btp_sous_traitances (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 chantier_id BIGINT UNSIGNED NOT NULL,
 debourse_ligne_id BIGINT UNSIGNED NOT NULL,
 prestataire VARCHAR(180) NOT NULL,
 reference_offre VARCHAR(80) NOT NULL,
 version INT UNSIGNED NOT NULL DEFAULT 1,
 montant_offre_ht DECIMAL(18,2) NOT NULL,
 statut ENUM('brouillon','validation','approuve','rejete') NOT NULL DEFAULT 'brouillon',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CHECK (montant_offre_ht >= 0),
 FOREIGN KEY (chantier_id) REFERENCES btp_chantiers(id) ON DELETE RESTRICT,
 FOREIGN KEY (debourse_ligne_id) REFERENCES btp_debourse_lignes(id) ON DELETE RESTRICT,
 UNIQUE KEY uq_btp_offre_reference (chantier_id,reference_offre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS btp_sous_traitance_visas (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 sous_traitance_id BIGINT UNSIGNED NOT NULL,
 version INT UNSIGNED NOT NULL,
 service ENUM('rh','juridique','technique','logistique','hse','dg') NOT NULL,
 acteur_id INT NOT NULL,
 decision ENUM('approuve','rejete','correction') NOT NULL,
 signature_method VARCHAR(40) NOT NULL,
 signature_proof VARCHAR(255) NOT NULL,
 document_sha256 CHAR(64) NOT NULL,
 commentaire TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (sous_traitance_id) REFERENCES btp_sous_traitances(id) ON DELETE RESTRICT,
 FOREIGN KEY (acteur_id) REFERENCES users(id) ON DELETE RESTRICT,
 UNIQUE KEY uq_btp_st_visa (sous_traitance_id,version,service)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
