<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    dolicarbon/lib/dolicarbon.lib.php
 * \ingroup dolicarbon
 * \brief   Library helpers for DoliCarbon
 */

function dolicarbonAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load('dolicarbon@dolicarbon');

	$h = 0;
	$head = array();
	$head[$h][0] = dolBuildUrl(dol_buildpath('/dolicarbon/admin/setup.php', 1));
	$head[$h][1] = $langs->trans('Settings');
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dolBuildUrl(dol_buildpath('/dolicarbon/admin/about.php', 1));
	$head[$h][1] = $langs->trans('About');
	$head[$h][2] = 'about';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'dolicarbon@dolicarbon');
	complete_head_from_modules($conf, $langs, null, $head, $h, 'dolicarbon@dolicarbon', 'remove');

	return $head;
}

function dolicarbon_get_category_map()
{
	return array(
		1 => array(
			'transport_road' => 'DOLICARBON_CAT_transport_road',
			'energy_combustion' => 'DOLICARBON_CAT_energy_combustion',
			'process_emissions' => 'DOLICARBON_CAT_process_emissions',
			'fugitive_emissions' => 'DOLICARBON_CAT_fugitive_emissions',
		),
		2 => array(
			'electricity' => 'DOLICARBON_CAT_electricity',
			'heat_cold' => 'DOLICARBON_CAT_heat_cold',
		),
		3 => array(
			'purchases_goods' => 'DOLICARBON_CAT_purchases_goods',
			'purchases_services' => 'DOLICARBON_CAT_purchases_services',
			'transport_upstream' => 'DOLICARBON_CAT_transport_upstream',
			'transport_downstream' => 'DOLICARBON_CAT_transport_downstream',
			'transport_air' => 'DOLICARBON_CAT_transport_air',
			'transport_rail' => 'DOLICARBON_CAT_transport_rail',
			'waste' => 'DOLICARBON_CAT_waste',
			'digital' => 'DOLICARBON_CAT_digital',
			'immobilisation' => 'DOLICARBON_CAT_immobilisation',
		),
	);
}

function dolicarbon_get_session_bilan_id()
{
	return !empty($_SESSION['dolicarbon_active_bilan']) ? (int) $_SESSION['dolicarbon_active_bilan'] : 0;
}

function dolicarbon_set_session_bilan_id($id)
{
	$_SESSION['dolicarbon_active_bilan'] = (int) $id;
}

function dolicarbon_bilan_prepare_head(DoliCarbonBilan $object)
{
	global $langs, $conf;

	$langs->load('dolicarbon@dolicarbon');
	$head = array();
	$h = 0;

	$head[$h][0] = dolBuildUrl(dol_buildpath('/dolicarbon/carbon_bilan_card.php', 1).'?id='.((int) $object->id));
	$head[$h][1] = $langs->trans('Card');
	$head[$h][2] = 'card';
	$h++;

	$head[$h][0] = dolBuildUrl(dol_buildpath('/dolicarbon/carbon_entry_list.php', 1).'?fk_bilan='.((int) $object->id));
	$head[$h][1] = $langs->trans('CarbonEntries');
	$head[$h][2] = 'entries';
	$h++;

	$head[$h][0] = dolBuildUrl(dol_buildpath('/dolicarbon/carbon_actions.php', 1).'?fk_bilan='.((int) $object->id));
	$head[$h][1] = $langs->trans('CarbonActions');
	$head[$h][2] = 'actions';
	$h++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'dolicarbon_bilan@dolicarbon');
	return $head;
}

function dolicarbon_import_map_get_factor(DoliDB $db, $fk_soc, $category)
{
	global $conf;
	$sql = "SELECT fk_factor FROM ".$db->prefix()."dolicarbon_import_map";
	$sql .= " WHERE entity = ".((int) $conf->entity);
	$sql .= " AND fk_soc = ".((int) $fk_soc);
	$sql .= " AND category = '".$db->escape($category)."'";
	$resql = $db->query($sql);
	if ($resql && ($obj = $db->fetch_object($resql))) {
		return (int) $obj->fk_factor;
	}
	return 0;
}

function dolicarbon_import_map_save(DoliDB $db, $fk_soc, $category, $fk_factor)
{
	global $conf;
	$old = dolicarbon_import_map_get_factor($db, $fk_soc, $category);
	if ($old > 0) {
		$sql = "UPDATE ".$db->prefix()."dolicarbon_import_map SET fk_factor = ".((int) $fk_factor);
		$sql .= " WHERE entity = ".((int) $conf->entity);
		$sql .= " AND fk_soc = ".((int) $fk_soc);
		$sql .= " AND category = '".$db->escape($category)."'";
		return $db->query($sql) ? 1 : -1;
	}

	$sql = "INSERT INTO ".$db->prefix()."dolicarbon_import_map (entity, fk_soc, category, fk_factor)";
	$sql .= " VALUES (".((int) $conf->entity).", ".((int) $fk_soc).", '".$db->escape($category)."', ".((int) $fk_factor).")";
	return $db->query($sql) ? 1 : -1;
}
