-- Copyright (C) 2025		Florian TOCCO <ftocco@aplose.fr>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE IF NOT EXISTS llx_dolilinks_societe_link(
	rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY, 
    fk_parent integer NOT NULL, 
    fk_child integer NOT NULL, 
    entity integer NOT NULL DEFAULT 1,
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	import_key varchar(14), 
    fk_link_type integer,

	CONSTRAINT fk_societelink_parent_societe FOREIGN KEY(fk_parent) REFERENCES llx_societe(rowid) ON DELETE CASCADE,
	CONSTRAINT fk_societelink_child_societe FOREIGN KEY(fk_child) REFERENCES llx_societe(rowid) ON DELETE CASCADE,
	CONSTRAINT fk_societelink_linktype FOREIGN KEY(fk_link_type) REFERENCES llx_c_dolilinks_link_type(rowid) ON DELETE SET NULL

) ENGINE=InnoDB;