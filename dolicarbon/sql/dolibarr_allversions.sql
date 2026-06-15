--
-- DoliCarbon — optional idempotent migrations for upgrades from older installs.
-- Full schema is created by llx_*.sql under sql/ and sql/tables/ at module activation.
-- This file is NOT executed automatically by Dolibarr _load_tables (only llx_*.sql, *.key.sql, data*.sql).
-- Run manually if you need to align an old database; or rely on module reactivation after replacing SQL files.
--

SET @db := DATABASE();

-- Add import hash to avoid duplicate imports
SET @t := 'llx_dolicarbon_entry';
SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'import_hash') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_entry ADD COLUMN import_hash varchar(128) NULL'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND INDEX_NAME = 'idx_dolicarbon_entry_import_hash') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_entry ADD INDEX idx_dolicarbon_entry_import_hash (import_hash)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'import_batch') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_entry ADD COLUMN import_batch varchar(64) NULL'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'is_fictional') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_entry ADD COLUMN is_fictional tinyint NOT NULL DEFAULT 0'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'seed_batch') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_entry ADD COLUMN seed_batch varchar(64) NULL'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND INDEX_NAME = 'idx_dolicarbon_entry_bilan_scope_cat') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_entry ADD INDEX idx_dolicarbon_entry_bilan_scope_cat (fk_bilan, scope, category)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND INDEX_NAME = 'idx_dolicarbon_entry_seed_batch') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_entry ADD INDEX idx_dolicarbon_entry_seed_batch (is_fictional, seed_batch)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Fictional batch support on bilans/actions
SET @t := 'llx_dolicarbon_bilan';
SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'is_fictional') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_bilan ADD COLUMN is_fictional tinyint NOT NULL DEFAULT 0'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'seed_batch') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_bilan ADD COLUMN seed_batch varchar(64) NULL'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @t := 'llx_dolicarbon_action';
SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'is_fictional') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_action ADD COLUMN is_fictional tinyint NOT NULL DEFAULT 0'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'seed_batch') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_action ADD COLUMN seed_batch varchar(64) NULL'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Import map source type dimension
SET @t := 'llx_dolicarbon_import_map';
SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'source_type') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_import_map ADD COLUMN source_type varchar(30) NOT NULL DEFAULT ''supplier_invoice'''
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s := (SELECT IF(
	(SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND INDEX_NAME = 'uk_dolicarbon_import_map_v2') > 0,
	'SELECT 1',
	'ALTER TABLE llx_dolicarbon_import_map ADD UNIQUE INDEX uk_dolicarbon_import_map_v2 (entity, source_type, fk_soc, category)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --- Bilan Carbone expert: entry quality / workflow / calculation trace ---
SET @t := 'llx_dolicarbon_entry';
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'quality_grade') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_entry ADD COLUMN quality_grade varchar(2) NOT NULL DEFAULT ''B'''));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'uncertainty_pct_low') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_entry ADD COLUMN uncertainty_pct_low double NOT NULL DEFAULT 10'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'uncertainty_pct_high') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_entry ADD COLUMN uncertainty_pct_high double NOT NULL DEFAULT 20'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'workflow_status') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_entry ADD COLUMN workflow_status varchar(32) NOT NULL DEFAULT ''draft'''));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'evidence_ref') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_entry ADD COLUMN evidence_ref varchar(255) NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'factor_kgco2e_snapshot') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_entry ADD COLUMN factor_kgco2e_snapshot double NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'calculation_formula') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_entry ADD COLUMN calculation_formula varchar(255) NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'calculation_fingerprint') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_entry ADD COLUMN calculation_fingerprint varchar(128) NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Factor versioning / governance
SET @t := 'llx_dolicarbon_factor';
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'version_label') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_factor ADD COLUMN version_label varchar(32) NOT NULL DEFAULT ''1.0'''));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'valid_from') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_factor ADD COLUMN valid_from date NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'valid_to') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_factor ADD COLUMN valid_to date NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'governance_status') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_factor ADD COLUMN governance_status varchar(20) NOT NULL DEFAULT ''validated'''));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'replacement_note') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_factor ADD COLUMN replacement_note text NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'priority_rank') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_factor ADD COLUMN priority_rank integer NOT NULL DEFAULT 0'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Transition / plan expert (actions)
SET @t := 'llx_dolicarbon_action';
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'baseline_tco2e') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN baseline_tco2e double NOT NULL DEFAULT 0'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'target_tco2e') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN target_tco2e double NOT NULL DEFAULT 0'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'capex_eur') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN capex_eur double NOT NULL DEFAULT 0'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'opex_eur') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN opex_eur double NOT NULL DEFAULT 0'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'feasibility_score') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN feasibility_score tinyint NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'impact_score') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN impact_score tinyint NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'uncertainty_gain_low') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN uncertainty_gain_low double NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'uncertainty_gain_high') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN uncertainty_gain_high double NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'milestone_date') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN milestone_date date NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'roadmap_quarter') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN roadmap_quarter varchar(15) NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'dependencies') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN dependencies text NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @t AND COLUMN_NAME = 'evidence_done') > 0, 'SELECT 1', 'ALTER TABLE llx_dolicarbon_action ADD COLUMN evidence_done text NULL'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- New table: methodological framing (cadrage)
SET @tname := 'llx_dolicarbon_cadrage';
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @tname) > 0, 'SELECT 1', 'CREATE TABLE llx_dolicarbon_cadrage (rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY, fk_bilan integer NOT NULL, entity integer NOT NULL DEFAULT 1, org_perimeter text, op_perimeter text, exclusions text, materiality_pct double DEFAULT 0, ref_year integer, reporting_year integer, completeness_note text, collection_checklists_json text, method_version integer NOT NULL DEFAULT 1, locked tinyint NOT NULL DEFAULT 0, note_method text, fk_user_creat integer, date_creation datetime, tms timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uk_dolicarbon_cadrage_bilan (fk_bilan), KEY idx_dolicarbon_cadrage_entity (entity)) ENGINE=innodb'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Calculation rules version
SET @tname := 'llx_dolicarbon_calc_version';
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @tname) > 0, 'SELECT 1', 'CREATE TABLE llx_dolicarbon_calc_version (rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY, code varchar(32) NOT NULL, label varchar(255), rules_json text, entity integer NOT NULL DEFAULT 0, date_start date, active tinyint NOT NULL DEFAULT 1, tms timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uk_dolicarbon_calc_code_ent (code, entity)) ENGINE=innodb'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Audit trail
SET @tname := 'llx_dolicarbon_audit_log';
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @tname) > 0, 'SELECT 1', 'CREATE TABLE llx_dolicarbon_audit_log (rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY, element_type varchar(40) NOT NULL, fk_element integer NOT NULL, action varchar(40) NOT NULL, fk_user integer, date_event datetime, detail_json text, entity integer NOT NULL DEFAULT 1, KEY idx_dolicarbon_audit_elt (element_type, fk_element), KEY idx_dolicarbon_audit_date (date_event)) ENGINE=innodb'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Report snapshots
SET @tname := 'llx_dolicarbon_snapshot';
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @tname) > 0, 'SELECT 1', 'CREATE TABLE llx_dolicarbon_snapshot (rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY, fk_bilan integer NOT NULL, label varchar(255), content_json longtext, content_hash varchar(64), fk_user_creat integer, date_creation datetime, entity integer NOT NULL DEFAULT 1, KEY idx_dolicarbon_snapshot_bilan (fk_bilan)) ENGINE=innodb'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Workflow comments
SET @tname := 'llx_dolicarbon_workflow_comment';
SET @s := (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = @tname) > 0, 'SELECT 1', 'CREATE TABLE llx_dolicarbon_workflow_comment (rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY, fk_bilan integer NOT NULL, fk_entry integer, message text NOT NULL, workflow_status varchar(32), fk_user integer, date_creation datetime, entity integer NOT NULL DEFAULT 1, KEY idx_dolicarbon_wfc_bilan (fk_bilan)) ENGINE=innodb'));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

