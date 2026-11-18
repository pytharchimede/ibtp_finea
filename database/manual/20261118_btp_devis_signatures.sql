-- Après les migrations devis et workflow, uniquement sur une base de recette.
-- Signature probante : ce registre conserve les preuves, pas les secrets ni les clés privées.
CREATE TABLE IF NOT EXISTS btp_devis_signatures (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 visa_id BIGINT UNSIGNED NOT NULL,
 devis_id BIGINT UNSIGNED NOT NULL,
 devis_version INT UNSIGNED NOT NULL,
 signataire_id INT NOT NULL,
 methode ENUM('passkey','signature_electronique') NOT NULL,
 document_sha256 CHAR(64) NOT NULL,
 preuve_reference VARCHAR(255) NOT NULL,
 signe_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_btp_signature_visa (visa_id),
 KEY idx_btp_signature_document (devis_id,devis_version),
 CONSTRAINT fk_btp_signature_visa FOREIGN KEY (visa_id) REFERENCES btp_devis_visas(id) ON DELETE RESTRICT,
 CONSTRAINT fk_btp_signature_devis FOREIGN KEY (devis_id) REFERENCES btp_devis(id) ON DELETE RESTRICT,
 CONSTRAINT fk_btp_signature_user FOREIGN KEY (signataire_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
