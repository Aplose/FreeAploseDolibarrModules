-- DoliCarbon - Workflow comments on bilans / entries

CREATE TABLE llx_dolicarbon_workflow_comment (
	rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
	fk_bilan integer NOT NULL,
	fk_entry integer,
	message text NOT NULL,
	workflow_status varchar(32),
	fk_user integer,
	date_creation datetime,
	entity integer NOT NULL DEFAULT 1,
	KEY idx_dolicarbon_wfc_bilan (fk_bilan)
) ENGINE=innodb;
