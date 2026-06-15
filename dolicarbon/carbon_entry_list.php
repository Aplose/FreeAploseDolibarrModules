<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr> */

/**
 * \file        carbon_entry_list.php
 * \ingroup     dolicarbon
 * \brief       List entries for a bilan
 */

$res = 0;
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once __DIR__.'/class/dolicarbonbilan.class.php';
require_once __DIR__.'/class/dolicarbonentry.class.php';
require_once __DIR__.'/lib/dolicarbon.lib.php';

$langs->loadLangs(array('dolicarbon@dolicarbon'));

if (!isModEnabled('dolicarbon') || !$user->hasRight('dolicarbon', 'read')) {
	accessforbidden();
}

$fk_bilan = GETPOSTINT('fk_bilan');
if ($fk_bilan <= 0) {
	accessforbidden('Missing fk_bilan');
}

$bilan = new DoliCarbonBilan($db);
if ($bilan->fetch($fk_bilan) <= 0) {
	accessforbidden();
}

$head = dolicarbon_bilan_prepare_head($bilan);

$title = $langs->trans('CarbonEntries');
llxHeader('', $title, '', '', 0, 0, '', '', '', 'mod-dolicarbon page-entry-list');

print dol_get_fiche_head($head, 'entries', $bilan->ref, -1, $bilan->picto);

$sql = "SELECT e.rowid, e.scope, e.category, e.label, e.quantity, e.unit, e.tco2e_computed, e.source_type, e.source_ref";
$sql .= " FROM ".$db->prefix()."dolicarbon_entry as e";
$sql .= " WHERE e.fk_bilan = ".((int) $fk_bilan);
$sql .= " ORDER BY e.scope, e.category, e.rowid";

$resql = $db->query($sql);
if (!$resql) {
	dol_print_error($db);
	llxFooter();
	$db->close();
	exit;
}

$catmap = dolicarbon_get_category_map();

print '<div class="tabsAction">';
if ($user->hasRight('dolicarbon', 'write') && (int) $bilan->status === DoliCarbonBilan::STATUS_DRAFT) {
	print dolGetButtonTitle($langs->trans('NewEntry'), '', 'fa fa-plus-circle', dol_buildpath('/dolicarbon/carbon_entry_card.php?action=create&fk_bilan='.$fk_bilan, 1), '', 1);
}
print '</div>';

print '<table class="noborder centpercent liste">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans('Scope').'</td>';
print '<td>'.$langs->trans('Category').'</td>';
print '<td>'.$langs->trans('Label').'</td>';
print '<td class="right">'.$langs->trans('Quantity').'</td>';
print '<td>'.$langs->trans('Unit').'</td>';
print '<td class="right">'.$langs->trans('TotalTco2e').'</td>';
print '<td>'.$langs->trans('Source').'</td>';
print '<td></td>';
print '</tr>';

while ($obj = $db->fetch_object($resql)) {
	$catlabel = $obj->category;
	if (isset($catmap[(int) $obj->scope][$obj->category])) {
		$catlabel = $langs->trans($catmap[(int) $obj->scope][$obj->category]);
	}
	print '<tr class="oddeven">';
	print '<td>'.((int) $obj->scope).'</td>';
	print '<td>'.dol_escape_htmltag($catlabel).'</td>';
	print '<td>'.dol_escape_htmltag($obj->label).'</td>';
	print '<td class="right">'.price2num($obj->quantity, 'MT', 3).'</td>';
	print '<td>'.dol_escape_htmltag($obj->unit).'</td>';
	print '<td class="right">'.price2num($obj->tco2e_computed, 'MT', 3).'</td>';
	print '<td>'.dol_escape_htmltag($obj->source_type).($obj->source_ref ? ' ('.dol_escape_htmltag($obj->source_ref).')' : '').'</td>';
	print '<td class="nowrap">';
	if ($user->hasRight('dolicarbon', 'write') && (int) $bilan->status === DoliCarbonBilan::STATUS_DRAFT) {
		print '<a href="'.dol_buildpath('/dolicarbon/carbon_entry_card.php', 1).'?id='.((int) $obj->rowid).'&fk_bilan='.$fk_bilan.'">'.$langs->trans('Modify').'</a>';
	}
	print '</td>';
	print '</tr>';
}
print '</table>';

print dol_get_fiche_end();

llxFooter();
$db->close();
