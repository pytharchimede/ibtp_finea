-- FINEA-IBTP / FEB. Migration additive a appliquer explicitement en recette.
-- mysql --database="$DB_DATABASE" < database/migrations/20261118_001_create_feb_module.sql
CREATE TABLE IF NOT EXISTS feb_workflows (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code VARCHAR(50) NOT NULL UNIQUE,
 label VARCHAR(120) NOT NULL, active TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS feb_workflow_steps (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, workflow_id BIGINT UNSIGNED NOT NULL,
 step_order INT UNSIGNED NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(120) NOT NULL,
 permission_action ENUM('view','create','update','delete') NOT NULL DEFAULT 'update',
 assigned_user_id INT NULL, active TINYINT(1) NOT NULL DEFAULT 1,
 UNIQUE KEY uq_feb_workflow_step (workflow_id,step_order),
 UNIQUE KEY uq_feb_workflow_code (workflow_id,code),
 FOREIGN KEY (workflow_id) REFERENCES feb_workflows(id) ON DELETE RESTRICT,
 FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS feb_chantier_budgets (
 chantier_id BIGINT UNSIGNED PRIMARY KEY, budget_id BIGINT UNSIGNED NOT NULL UNIQUE,
 assigned_by INT NOT NULL, assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (chantier_id) REFERENCES btp_chantier_starts(id) ON DELETE RESTRICT,
 FOREIGN KEY (budget_id) REFERENCES btp_prestart_budgets(id) ON DELETE RESTRICT,
 FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS feb_chantier_access (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, chantier_id BIGINT UNSIGNED NOT NULL,
 user_id INT NOT NULL, access_role ENUM('demander','valider','payer') NOT NULL,
 granted_by INT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_feb_chantier_access (chantier_id,user_id,access_role),
 KEY idx_feb_access_user_role (user_id,access_role,chantier_id),
 FOREIGN KEY (chantier_id) REFERENCES btp_chantier_starts(id) ON DELETE RESTRICT,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
 FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS feb_admin_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, actor_id INT NOT NULL,
 entity_type ENUM('workflow','workflow_step','chantier_access','chantier_budget') NOT NULL,
 entity_id BIGINT UNSIGNED NULL, action VARCHAR(40) NOT NULL,
 before_json JSON NULL, after_json JSON NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE RESTRICT,
 KEY idx_feb_admin_history_created (created_at,id),
 KEY idx_feb_admin_history_entity (entity_type,entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS feb_requests (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, reference VARCHAR(40) NULL UNIQUE,
 requester_id INT NOT NULL, chantier_id BIGINT UNSIGNED NOT NULL, workflow_id BIGINT UNSIGNED NOT NULL,
 current_step_id BIGINT UNSIGNED NULL, beneficiary_type ENUM('fournisseur','beneficiaire') NOT NULL,
 beneficiary_name VARCHAR(180) NOT NULL, beneficiary_details VARCHAR(500) NULL,
 purpose TEXT NOT NULL, needed_at DATE NOT NULL, status ENUM('brouillon','soumis','complement','approuve','refuse','annule','partiellement_paye','paye') NOT NULL DEFAULT 'brouillon',
 version INT UNSIGNED NOT NULL DEFAULT 1, submitted_at DATETIME NULL, approved_at DATETIME NULL,
 cancelled_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE RESTRICT,
 FOREIGN KEY (chantier_id) REFERENCES btp_chantier_starts(id) ON DELETE RESTRICT,
 FOREIGN KEY (workflow_id) REFERENCES feb_workflows(id) ON DELETE RESTRICT,
 FOREIGN KEY (current_step_id) REFERENCES feb_workflow_steps(id) ON DELETE RESTRICT,
 KEY idx_feb_status_step (status,current_step_id), KEY idx_feb_chantier (chantier_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS feb_request_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_id BIGINT UNSIGNED NOT NULL,
 budget_line_id BIGINT UNSIGNED NOT NULL, description VARCHAR(500) NOT NULL,
 quantity DECIMAL(15,3) NOT NULL DEFAULT 1, unit VARCHAR(30) NOT NULL DEFAULT 'u',
 unit_amount DECIMAL(18,2) NOT NULL, total_amount DECIMAL(18,2) NOT NULL,
 CHECK (quantity > 0), CHECK (unit_amount >= 0), CHECK (total_amount >= 0),
 FOREIGN KEY (request_id) REFERENCES feb_requests(id) ON DELETE RESTRICT,
 FOREIGN KEY (budget_line_id) REFERENCES btp_prestart_budget_lines(id) ON DELETE RESTRICT,
 KEY idx_feb_item_budget (budget_line_id), KEY idx_feb_item_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS feb_documents (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_id BIGINT UNSIGNED NOT NULL,
 category ENUM('justificatif','devis','facture','beneficiaire','paiement','autre') NOT NULL DEFAULT 'justificatif',
 original_name VARCHAR(255) NOT NULL, storage_name VARCHAR(100) NOT NULL UNIQUE,
 mime_type VARCHAR(120) NOT NULL, file_size BIGINT UNSIGNED NOT NULL, sha256 CHAR(64) NOT NULL,
 uploaded_by INT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (request_id) REFERENCES feb_requests(id) ON DELETE RESTRICT,
 FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS feb_decisions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_id BIGINT UNSIGNED NOT NULL,
 step_id BIGINT UNSIGNED NOT NULL, actor_id INT NOT NULL,
 decision ENUM('approuve','refuse','complement') NOT NULL, comment TEXT NULL,
 request_version INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_feb_decision_version (request_id,step_id,request_version),
 FOREIGN KEY (request_id) REFERENCES feb_requests(id) ON DELETE RESTRICT,
 FOREIGN KEY (step_id) REFERENCES feb_workflow_steps(id) ON DELETE RESTRICT,
 FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS feb_payments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_id BIGINT UNSIGNED NOT NULL,
 payment_reference VARCHAR(100) NOT NULL UNIQUE, amount DECIMAL(18,2) NOT NULL,
 method VARCHAR(50) NOT NULL, paid_at DATETIME NOT NULL, notes TEXT NULL,
 recorded_by INT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CHECK (amount > 0), FOREIGN KEY (request_id) REFERENCES feb_requests(id) ON DELETE RESTRICT,
 FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT, KEY idx_feb_payment_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS feb_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_id BIGINT UNSIGNED NOT NULL,
 actor_id INT NULL, event_type VARCHAR(60) NOT NULL, from_status VARCHAR(40) NULL,
 to_status VARCHAR(40) NULL, metadata_json JSON NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (request_id) REFERENCES feb_requests(id) ON DELETE RESTRICT,
 FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL, KEY idx_feb_history (request_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO feb_workflows(code,label) VALUES ('standard','Circuit FEB standard');
INSERT IGNORE INTO feb_workflow_steps(workflow_id,step_order,code,label)
 SELECT id,1,'responsable','Validation responsable' FROM feb_workflows WHERE code='standard';
INSERT IGNORE INTO feb_workflow_steps(workflow_id,step_order,code,label)
 SELECT id,2,'finance','Contrôle finance et budget' FROM feb_workflows WHERE code='standard';

