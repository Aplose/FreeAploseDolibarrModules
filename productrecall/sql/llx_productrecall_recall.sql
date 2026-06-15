-- Copyright (C) 2024 SuperAdmin
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


CREATE TABLE llx_productrecall_recall(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
	entity integer DEFAULT 1 NOT NULL,
	date_creation datetime NOT NULL,
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	fk_user_creat integer NOT NULL,
	fk_user_modif integer,
	import_key varchar(14), 
	referencefiche varchar(128), 
	naturejuridiquedurappel varchar(255), 
	catgoriedeproduit varchar(255), 
	souscatgoriedeproduit varchar(255), 
	nomdelamarqueduproduit varchar(255), 
	nomsdesmodelesoureferences varchar(255), 
	identificationdesproduits varchar(255), 
	conditionnements varchar(255), 
	datedebutfindecommercialisa varchar(255), 
	temperaturedeconservation varchar(255), 
	marquedesalubrite varchar(255), 
	informationscomplementaires varchar(255), 
	zonegeographiquedevente varchar(255), 
	distributeurs varchar(255), 
	motifdurappel varchar(255), 
	risquesencourusparleconsomm varchar(255), 
	preconisationssanitaires text, 
	descriptioncomplementairedu varchar(255), 
	conduitesatenirparleconsomm varchar(255), 
	numerodecontact varchar(20), 
	modalitesdecompensation varchar(255), 
	datedefindelaprocedurederap varchar(128), 
	liensverslesimages varchar(255), 
	lienverslalistedesproduits varchar(255), 
	lienverslalistedesdistribut varchar(255), 
	lienversaffichettepdf varchar(255), 
	lienverslaficherappel varchar(255), 
	rappelguid varchar(64), 
	datedepublication date, 
	informationscomppubliques varchar(255)
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
