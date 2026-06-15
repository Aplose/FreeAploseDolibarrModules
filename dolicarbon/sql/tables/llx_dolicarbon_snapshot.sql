-- DoliCarbon - Frozen report snapshots

CREATE TABLE llx_dolicarbon_snapshot (
	rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
	fk_bilan integer NOT NULL,
	label varchar(255),
	content_json longtext,
	content_hash varchar(64),
	fk_user_creat integer,
	date_creation datetime,
	entity integer NOT NULL DEFAULT 1,
	KEY idx_dolicarbon_snapshot_bilan (fk_bilan)
) ENGINE=innodb;
