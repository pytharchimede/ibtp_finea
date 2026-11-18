-- À exécuter une seule fois en recette, après sauvegarde, avant d'activer la nouvelle interface.
-- Pré-requis : btp_projets, crm_opportunities et crm_ao_notices existent.
ALTER TABLE btp_projets
 ADD COLUMN crm_opportunity_id BIGINT UNSIGNED NULL AFTER client_id,
 ADD UNIQUE KEY uq_btp_projets_crm_opportunity (crm_opportunity_id),
 ADD CONSTRAINT fk_btp_projets_crm_opportunity FOREIGN KEY (crm_opportunity_id) REFERENCES crm_opportunities(id) ON DELETE RESTRICT;
-- Les projets historiques restent NULL pour préserver les données ; toutes les nouvelles créations passent par une opportunité.
