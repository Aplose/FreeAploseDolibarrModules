-- DoliCarbon - GHG inventory period (bilan)
-- Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr>

CREATE TABLE llx_dolicarbon_bilan (
	rowid integer AUTO_INCREMENT PRIMARY KEY,
	ref varchar(30) NOT NULL,
	label varchar(255),
	year integer NOT NULL,
	date_start date,
	date_end date,
	entity integer DEFAULT 1 NOT NULL,
	fk_soc integer,
	fk_user_creat integer,
	fk_user_modif integer,
	date_creation datetime,
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	status smallint DEFAULT 0,
	total_tco2e double DEFAULT 0,
	target_tco2e double,
	note_public text,
	note_private text,
	is_fictional tinyint NOT NULL DEFAULT 0,
	seed_batch varchar(64) NULL
) ENGINE=innodb;
