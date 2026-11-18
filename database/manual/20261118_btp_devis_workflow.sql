-- À exécuter après 20261118_btp_projets_devis_bc.sql sur une base de recette.
-- Le circuit est configurable par étapes; le modèle initial exige un visa Technique puis DG.
CREATE TABLE IF NOT EXISTS btp_devis_workflow_etapes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(50) NOT NULL UNIQUE,
 libelle VARCHAR(120) NOT NULL,
 ordre INT UNSIGNED NOT NULL UNIQUE,
 actif TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO btp_devis_workflow_etapes(code,libelle,ordre) VALUES
 ('technique','Validation technique',10),('dg','Approbation direction générale',20)
ON DUPLICATE KEY UPDATE libelle=VALUES(libelle);
CREATE TABLE IF NOT EXISTS btp_devis_visas (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 devis_id BIGINT UNSIGNED NOT NULL,
 version INT UNSIGNED NOT NULL,
 etape_id BIGINT UNSIGNED NOT NULL,
 decision ENUM('approuve','rejete','correction') NOT NULL,
 commentaire TEXT NULL,
 acteur_id INT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_btp_visas_devis_version (devis_id,version,created_at),
 FOREIGN KEY (devis_id) REFERENCES btp_devis(id) ON DELETE RESTRICT,
 FOREIGN KEY (etape_id) REFERENCES btp_devis_workflow_etapes(id) ON DELETE RESTRICT,
 FOREIGN KEY (acteur_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS btp_devis_workflow_affectations (
 etape_id BIGINT UNSIGNED NOT NULL,
 user_id INT NOT NULL,
 PRIMARY KEY(etape_id,user_id),
 FOREIGN KEY(etape_id) REFERENCES btp_devis_workflow_etapes(id) ON DELETE CASCADE,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
