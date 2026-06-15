-- DoliCarbon - Emission factors

CREATE TABLE llx_dolicarbon_factor (
	rowid integer AUTO_INCREMENT PRIMARY KEY,
	code varchar(50) NOT NULL,
	label varchar(255) NOT NULL,
	category varchar(100),
	scope tinyint,
	unit_input varchar(30),
	kgco2e_per_unit double NOT NULL,
	source varchar(100) DEFAULT 'ADEME Base Carbone',
	year_ref integer,
	active tinyint DEFAULT 1,
	entity integer DEFAULT 1 NOT NULL,
	note text,
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	version_label varchar(32) DEFAULT '1.0',
	valid_from date,
	valid_to date,
	governance_status varchar(20) DEFAULT 'validated',
	replacement_note text,
	priority_rank integer DEFAULT 0
) ENGINE=innodb;
