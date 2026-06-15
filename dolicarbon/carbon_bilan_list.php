<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr> */

/**
 * \file        carbon_bilan_list.php
 * \ingroup     dolicarbon
 * \brief       List carbon bilans
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
require_once __DIR__.'/lib/dolicarbon.lib.php';

$langs->loadLangs(array('dolicarbon@dolicarbon'));

if (!isModEnabled('dolicarbon')) {
	accessforbidden('Module not enabled');
}
if (!$user->hasRight('dolicarbon', 'read')) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');

/*
 * Actions
 */
if ($action == 'setactive' && GETPOSTINT('id') && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$newid = GETPOSTINT('id');
	dolicarbon_set_session_bilan_id($newid);
	header('Location: '.dol_buildpath('/dolicarbon/carbon_index.php', 1));
	exit;
}

/*
 * View
 */

$sortfield = GETPOST('sortfield', 'aZ09dotcomma');
$sortorder = GETPOST('sortorder', 'aZ09');
if (empty($sortfield)) {
	$sortfield = 'b.year';
}
if (empty($sortorder)) {
	$sortorder = 'DESC';
}

$sql = "SELECT b.rowid, b.ref, b.label, b.year, b.status, b.total_tco2e, b.target_tco2e, b.entity";
$sql .= " FROM ".$db->prefix()."dolicarbon_bilan as b";
$sql .= " WHERE b.entity IN (".getEntity('societe').")";

$sql .= $db->order($sortfield, $sortorder);

$resql = $db->query($sql);
if (!$resql) {
	dol_print_error($db);
	exit;
}

$title = $langs->trans('BilanList');
llxHeader('', $title, '', '', 0, 0, '', '', '', 'mod-dolicarbon page-list');

print load_fiche_titre($title, dolGetButtonTitle($langs->trans('NewBilan'), '', 'fa fa-plus-circle', dol_buildpath('/dolicarbon/carbon_bilan_card.php?action=create', 1), '', $user->hasRight('dolicarbon', 'write') ? 1 : 0), '');

print '<table class="noborder centpercent liste">';
print '<tr class="liste_titre">';
print_liste_field_titre('Ref', $_SERVER['PHP_SELF'], 'b.ref', '', '', '', $sortfield, $sortorder);
print_liste_field_titre('Label', $_SERVER['PHP_SELF'], 'b.label', '', '', '', $sortfield, $sortorder);
print_liste_field_titre('Year', $_SERVER['PHP_SELF'], 'b.year', '', '', '', $sortfield, $sortorder);
print_liste_field_titre('Status', $_SERVER['PHP_SELF'], 'b.status', '', '', '', $sortfield, $sortorder);
print_liste_field_titre('TotalTco2e', $_SERVER['PHP_SELF'], 'b.total_tco2e', '', '', '', $sortfield, $sortorder);
print_liste_field_titre('TargetTco2e', $_SERVER['PHP_SELF'], 'b.target_tco2e', '', '', '', $sortfield, $sortorder);
print '<td class="liste_titre"></td>';
print '</tr>';

$bilan = new DoliCarbonBilan($db);
while ($obj = $db->fetch_object($resql)) {
	print '<tr class="oddeven">';
	print '<td class="nowrap">'.$bilan->getNomUrl(1, '', 0, '', 0, $obj->rowid, $obj->ref).'</td>';
	print '<td>'.dol_escape_htmltag($obj->label).'</td>';
	print '<td class="right">'.((int) $obj->year).'</td>';
	print '<td>'.$bilan->LibStatut((int) $obj->status, 0).'</td>';
	print '<td class="right">'.price2num($obj->total_tco2e, 'MT', 3).'</td>';
	print '<td class="right">'.($obj->target_tco2e !== null ? price2num($obj->target_tco2e, 'MT', 3) : '').'</td>';
	print '<td class="nowrap right">';
	if ($user->hasRight('dolicarbon', 'read')) {
		print '<form method="POST" action="'.dol_buildpath('/dolicarbon/carbon_bilan_list.php', 1).'" style="display:inline">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="setactive">';
		print '<input type="hidden" name="id" value="'.((int) $obj->rowid).'">';
		print '<button class="linkbutton reposition" type="submit">'.$langs->trans('SetAsActiveBilan').'</button>';
		print '</form>';
	}
	print '</td>';
	print '</tr>';
}
print '</table>';

llxFooter();
$db->close();
