-- Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.

CREATE TABLE llx_doliprospectform_publicsubmission(
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
	entity integer DEFAULT 1 NOT NULL,
	ref varchar(128) NOT NULL,
	fk_soc integer NOT NULL,
	fk_contact integer,
	fk_user_commercial integer DEFAULT 0 NOT NULL,
	form_type varchar(32) NOT NULL,
	nb_documents integer NOT NULL DEFAULT 0,
	date_submission datetime NOT NULL,
	date_creation datetime NOT NULL,
	tms timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	fk_user_creat integer NOT NULL,
	fk_user_modif integer,
	import_key varchar(14),
	status integer NOT NULL DEFAULT 1,
	UNIQUE KEY uk_dpf_pubsub_ref (ref, entity),
	KEY idx_dpf_pubsub_entity (entity),
	KEY idx_dpf_pubsub_fk_soc (fk_soc),
	KEY idx_dpf_pubsub_commercial (fk_user_commercial, entity)
) ENGINE=innodb;
