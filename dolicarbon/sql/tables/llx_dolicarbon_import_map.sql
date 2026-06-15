-- DoliCarbon - Import mapping (third party + category -> factor)

CREATE TABLE llx_dolicarbon_import_map (
	rowid integer AUTO_INCREMENT PRIMARY KEY,
	entity integer DEFAULT 1 NOT NULL,
	source_type varchar(30) NOT NULL DEFAULT 'supplier_invoice',
	fk_soc integer NOT NULL,
	category varchar(100) NOT NULL,
	fk_factor integer NOT NULL,
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY uk_dolicarbon_import_map_v2 (entity, source_type, fk_soc, category),
	KEY idx_dolicarbon_import_map_factor (fk_factor)
) ENGINE=innodb;
