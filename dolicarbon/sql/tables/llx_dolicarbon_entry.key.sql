-- DoliCarbon - Indexes for entries

ALTER TABLE llx_dolicarbon_entry ADD INDEX idx_dolicarbon_entry_fk_bilan (fk_bilan);
ALTER TABLE llx_dolicarbon_entry ADD INDEX idx_dolicarbon_entry_scope (scope);
ALTER TABLE llx_dolicarbon_entry ADD INDEX idx_dolicarbon_entry_category (category);
ALTER TABLE llx_dolicarbon_entry ADD INDEX idx_dolicarbon_entry_date_creation (date_creation);
ALTER TABLE llx_dolicarbon_entry ADD INDEX idx_dolicarbon_entry_import_hash (import_hash);
ALTER TABLE llx_dolicarbon_entry ADD INDEX idx_dolicarbon_entry_bilan_scope_cat (fk_bilan, scope, category);
ALTER TABLE llx_dolicarbon_entry ADD INDEX idx_dolicarbon_entry_seed_batch (is_fictional, seed_batch);
