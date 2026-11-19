-- Exigences documentaires paramétrables par nature juridique.
CREATE TABLE IF NOT EXISTS procurement_supplier_document_requirements (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 person_type ENUM('physique','morale') NOT NULL,
 document_type VARCHAR(70) NOT NULL,
 label VARCHAR(160) NOT NULL,
 required TINYINT(1) NOT NULL DEFAULT 1,
 active TINYINT(1) NOT NULL DEFAULT 1,
 sort_order INT NOT NULL DEFAULT 0,
 UNIQUE KEY uq_supplier_requirement(person_type,document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO procurement_supplier_document_requirements(person_type,document_type,label,required,sort_order) VALUES
('physique','identity','Pièce d’identité valide',1,10),
('physique','tax','Attestation fiscale / NCC (si applicable)',0,20),
('physique','trade_register','RCCM (si applicable)',0,30),
('physique','bank','RIB ou attestation bancaire',1,40),
('morale','trade_register','Registre du commerce (RCCM)',1,10),
('morale','tax','Identifiant fiscal / NCC',1,20),
('morale','representative_identity','Pièce d’identité du représentant',1,30),
('morale','bank','RIB ou attestation bancaire',1,40);
