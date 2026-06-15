-- DoliCarbon - Audit trail

CREATE TABLE llx_dolicarbon_audit_log (
	rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
	element_type varchar(40) NOT NULL,
	fk_element integer NOT NULL,
	action varchar(40) NOT NULL,
	fk_user integer,
	date_event datetime,
	detail_json text,
	entity integer NOT NULL DEFAULT 1,
	KEY idx_dolicarbon_audit_elt (element_type, fk_element),
	KEY idx_dolicarbon_audit_date (date_event)
) ENGINE=innodb;
