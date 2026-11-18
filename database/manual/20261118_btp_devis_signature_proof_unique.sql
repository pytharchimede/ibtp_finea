-- Après 20261118_btp_devis_signatures.sql, sur base de recette.
-- Empêche la réutilisation d'une même référence de preuve cryptographique.
ALTER TABLE btp_devis_signatures ADD UNIQUE KEY uq_btp_signature_preuve (preuve_reference);
