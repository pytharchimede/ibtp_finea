-- Circuit d'agrément fournisseur configurable, versionné par dossier soumis.
CREATE TABLE IF NOT EXISTS procurement_supplier_workflows (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(60) NOT NULL UNIQUE,
 label VARCHAR(180) NOT NULL,
 active TINYINT(1) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS procurement_supplier_workflow_steps (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 workflow_id INT NOT NULL,
 step_order INT NOT NULL,
 label VARCHAR(180) NOT NULL,
 assigned_user_id INT NOT NULL,
 required TINYINT(1) NOT NULL DEFAULT 1,
 UNIQUE KEY uq_supplier_step_order(workflow_id,step_order),
 FOREIGN KEY(workflow_id) REFERENCES procurement_supplier_workflows(id),
 FOREIGN KEY(assigned_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS procurement_supplier_approval_runs (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 supplier_id BIGINT UNSIGNED NOT NULL,
 workflow_id INT NOT NULL,
 status ENUM('en_cours','approuve','refuse','complement_requis') NOT NULL DEFAULT 'en_cours',
 submitted_by INT NOT NULL,
 submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 completed_at DATETIME NULL,
 KEY idx_supplier_approval_run(supplier_id,status),
 FOREIGN KEY(supplier_id) REFERENCES procurement_suppliers(id),
 FOREIGN KEY(workflow_id) REFERENCES procurement_supplier_workflows(id),
 FOREIGN KEY(submitted_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS procurement_supplier_approval_run_steps (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 run_id BIGINT UNSIGNED NOT NULL,
 step_order INT NOT NULL,
 label VARCHAR(180) NOT NULL,
 assigned_user_id INT NOT NULL,
 status ENUM('en_attente','a_traiter','approuve','refuse','complement_requis') NOT NULL DEFAULT 'en_attente',
 decided_by INT NULL,
 decided_at DATETIME NULL,
 comment TEXT NULL,
 UNIQUE KEY uq_supplier_run_step(run_id,step_order),
 FOREIGN KEY(run_id) REFERENCES procurement_supplier_approval_runs(id),
 FOREIGN KEY(assigned_user_id) REFERENCES users(id),
 FOREIGN KEY(decided_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
