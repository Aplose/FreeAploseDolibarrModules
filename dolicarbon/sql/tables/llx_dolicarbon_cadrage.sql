-- DoliCarbon - Methodological framing (one row per bilan)

CREATE TABLE llx_dolicarbon_cadrage (
	rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
	fk_bilan integer NOT NULL,
	entity integer NOT NULL DEFAULT 1,
	org_perimeter text,
	op_perimeter text,
	exclusions text,
	materiality_pct double DEFAULT 0,
	ref_year integer,
	reporting_year integer,
	completeness_note text,
	collection_checklists_json text,
	method_version integer NOT NULL DEFAULT 1,
	locked tinyint NOT NULL DEFAULT 0,
	note_method text,
	fk_user_creat integer,
	date_creation datetime,
	tms timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY uk_dolicarbon_cadrage_bilan (fk_bilan),
	KEY idx_dolicarbon_cadrage_entity (entity)
) ENGINE=innodb;
