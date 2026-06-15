-- DoliCarbon - Indexes for factors

ALTER TABLE llx_dolicarbon_factor ADD INDEX idx_dolicarbon_factor_entity (entity);
ALTER TABLE llx_dolicarbon_factor ADD INDEX idx_dolicarbon_factor_scope (scope);
ALTER TABLE llx_dolicarbon_factor ADD INDEX idx_dolicarbon_factor_category (category);
ALTER TABLE llx_dolicarbon_factor ADD UNIQUE INDEX uk_dolicarbon_factor_code_entity (code, entity);
