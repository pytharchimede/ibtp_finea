-- FEB IBTP : affectation bureau (service RH -> ligne budgetisee) et hierarchie chantier.
-- APPLIQUER UNIQUEMENT APRES SAUVEGARDE ET EN RECETTE, MariaDB 10.11+.
-- Ne cree aucun budget fictif; les allocations sont creees explicitement par l'administrateur.
CREATE TABLE IF NOT EXISTS feb_office_budget_allocations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 service_id INT NOT NULL,
 fiscal_year SMALLINT UNSIGNED NOT NULL,
 code VARCHAR(50) NOT NULL,
 label VARCHAR(180) NOT NULL,
 budget_amount DECIMAL(18,2) NOT NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_by INT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CHECK (budget_amount >= 0),
 UNIQUE KEY uq_feb_office_allocation (service_id,fiscal_year,code),
 KEY idx_feb_office_service (service_id,fiscal_year,active),
 FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE feb_requests
 ADD COLUMN IF NOT EXISTS scope ENUM('chantier','bureau') NOT NULL DEFAULT 'chantier',
 ADD COLUMN IF NOT EXISTS service_id INT NULL,
 ADD COLUMN IF NOT EXISTS office_allocation_id BIGINT UNSIGNED NULL;
ALTER TABLE feb_requests MODIFY COLUMN chantier_id BIGINT UNSIGNED NULL;
ALTER TABLE feb_request_items
 ADD COLUMN IF NOT EXISTS office_allocation_id BIGINT UNSIGNED NULL;
ALTER TABLE feb_request_items MODIFY COLUMN budget_line_id BIGINT UNSIGNED NULL;
ALTER TABLE feb_admin_history MODIFY COLUMN entity_type ENUM('workflow','workflow_step','chantier_access','chantier_budget','office_budget') NOT NULL;
-- Pour rester compatible avec les donnees historiques, les contraintes ci-dessus
-- restent optionnelles au niveau schema; FebService controle les correspondances.
-- Verification apres migration:
-- SHOW COLUMNS FROM feb_requests;
-- SHOW COLUMNS FROM feb_request_items;
-- SELECT * FROM feb_office_budget_allocations;
