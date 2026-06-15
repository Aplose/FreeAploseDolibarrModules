-- DoliCarbon - Calculation engine rules versions

CREATE TABLE llx_dolicarbon_calc_version (
	rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
	code varchar(32) NOT NULL,
	label varchar(255),
	rules_json text,
	entity integer NOT NULL DEFAULT 0,
	date_start date,
	active tinyint NOT NULL DEFAULT 1,
	tms timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY uk_dolicarbon_calc_code_ent (code, entity)
) ENGINE=innodb;
