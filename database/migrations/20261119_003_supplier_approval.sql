-- Référentiel fournisseurs IBTP : personnes physiques / morales et agrément préalable.
CREATE TABLE IF NOT EXISTS procurement_suppliers (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 supplier_code VARCHAR(40) NOT NULL,
 person_type ENUM('physique','morale') NOT NULL,
 legal_name VARCHAR(200) NOT NULL,
 trade_name VARCHAR(200) NULL,
 contact_name VARCHAR(180) NULL,
 email VARCHAR(190) NULL,
 phone VARCHAR(40) NULL,
 address VARCHAR(500) NULL,
 country_code CHAR(2) NOT NULL DEFAULT 'CI',
 tax_identifier VARCHAR(100) NULL,
 trade_register VARCHAR(100) NULL,
 bank_name VARCHAR(150) NULL,
 bank_iban VARCHAR(120) NULL,
 status ENUM('brouillon','soumis','complement_requis','approuve','refuse','suspendu') NOT NULL DEFAULT 'brouillon',
 submitted_at DATETIME NULL,
 reviewed_at DATETIME NULL,
 reviewed_by INT NULL,
 review_comment TEXT NULL,
 created_by INT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_procurement_supplier_code(supplier_code),
 KEY idx_procurement_supplier_eligibility(status,legal_name),
 KEY idx_procurement_supplier_tax(tax_identifier),
 CONSTRAINT fk_procurement_supplier_creator FOREIGN KEY(created_by) REFERENCES users(id),
 CONSTRAINT fk_procurement_supplier_reviewer FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS procurement_supplier_documents (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 supplier_id BIGINT UNSIGNED NOT NULL,
 document_type VARCHAR(70) NOT NULL,
 original_name VARCHAR(255) NOT NULL,
 storage_name VARCHAR(255) NOT NULL,
 mime_type VARCHAR(120) NOT NULL,
 file_size BIGINT UNSIGNED NOT NULL,
 sha256 CHAR(64) NOT NULL,
 uploaded_by INT NOT NULL,
 uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_supplier_docs(supplier_id,document_type),
 FOREIGN KEY(supplier_id) REFERENCES procurement_suppliers(id) ON DELETE CASCADE,
 FOREIGN KEY(uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS procurement_supplier_reviews (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 supplier_id BIGINT UNSIGNED NOT NULL,
 previous_status VARCHAR(30) NULL,
 new_status VARCHAR(30) NOT NULL,
 comment TEXT NULL,
 actor_user_id INT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_supplier_reviews(supplier_id,created_at),
 FOREIGN KEY(supplier_id) REFERENCES procurement_suppliers(id),
 FOREIGN KEY(actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE feb_requests
 ADD COLUMN IF NOT EXISTS supplier_id BIGINT UNSIGNED NULL;
-- FK ajoutée dans une migration ultérieure après validation des données historiques.
