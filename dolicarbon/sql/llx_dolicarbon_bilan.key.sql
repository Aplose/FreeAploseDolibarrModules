-- DoliCarbon - Indexes for bilan

ALTER TABLE llx_dolicarbon_bilan ADD INDEX idx_dolicarbon_bilan_entity (entity);
ALTER TABLE llx_dolicarbon_bilan ADD INDEX idx_dolicarbon_bilan_year (year);
ALTER TABLE llx_dolicarbon_bilan ADD INDEX idx_dolicarbon_bilan_status (status);
ALTER TABLE llx_dolicarbon_bilan ADD UNIQUE INDEX uk_dolicarbon_bilan_ref_entity (ref, entity);
