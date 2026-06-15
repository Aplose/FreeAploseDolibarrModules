<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr> */

/**
 * \file        carbon_factors.php
 * \ingroup     dolicarbon
 * \brief       Emission factors list and edit
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once __DIR__.'/class/dolicarbonfactor.class.php';

$langs->loadLangs(array('dolicarbon@dolicarbon'));

if (!isModEnabled('dolicarbon') || !$user->hasRight('dolicarbon', 'read')) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$id = GETPOSTINT('id');

$object = new DoliCarbonFactor($db);
if ($id > 0 && !in_array($action, array('add', 'importcsv'), true)) {
	$object->fetch($id);
}

if ($action == 'add' && $user->hasRight('dolicarbon', 'write') && !GETPOST('cancel', 'alpha')) {
	$object = new DoliCarbonFactor($db);
	$object->code = GETPOST('code', 'alphanohtml');
	$object->label = GETPOST('label', 'alphanohtml');
	$object->category = GETPOST('category', 'alphanohtml');
	$object->scope = GETPOSTINT('scope');
	$object->unit_input = GETPOST('unit_input', 'alphanohtml');
	$object->kgco2e_per_unit = GETPOSTFLOAT('kgco2e_per_unit', 'MT', 1);
	$object->source = GETPOST('source', 'alphanohtml');
	$object->year_ref = GETPOSTINT('year_ref');
	$object->active = GETPOSTINT('active') ? 1 : 0;
	$object->note = GETPOST('note', 'restricthtml');
	if ($object->create($user) > 0) {
		setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_factors.php', 1));
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
	$action = 'create';
}

if ($action == 'update' && $id > 0 && $user->hasRight('dolicarbon', 'write')) {
	$object->label = GETPOST('label', 'alphanohtml');
	$object->category = GETPOST('category', 'alphanohtml');
	$object->scope = GETPOSTINT('scope');
	$object->unit_input = GETPOST('unit_input', 'alphanohtml');
	$object->kgco2e_per_unit = GETPOSTFLOAT('kgco2e_per_unit', 'MT', 1);
	$object->source = GETPOST('source', 'alphanohtml');
	$object->year_ref = GETPOSTINT('year_ref');
	$object->active = GETPOSTINT('active') ? 1 : 0;
	$object->note = GETPOST('note', 'restricthtml');
	if ($object->update($user) > 0) {
		setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_factors.php', 1));
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
	$action = 'edit';
}

if ($action == 'confirm_delete' && $id > 0 && $user->hasRight('dolicarbon', 'delete')) {
	if ($object->delete($user) > 0) {
		setEventMessages($langs->trans('RecordDeleted'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_factors.php', 1));
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
}

if ($action == 'importcsv' && $user->hasRight('dolicarbon', 'write') && !empty($_FILES['csvfile']['tmp_name'])) {
	$tmp = $_FILES['csvfile']['tmp_name'];
	$n = $object->importFromCSV($tmp, $user);
	if ($n >= 0) {
		setEventMessages($langs->trans('DOLICARBON_ImportLinesOk', $n), null, 'mesgs');
	} else {
		setEventMessages($object->error, null, 'errors');
	}
	header('Location: '.dol_buildpath('/dolicarbon/carbon_factors.php', 1));
	exit;
}

$form = new Form($db);

llxHeader('', $langs->trans('CarbonFactors'), '', '', 0, 0, '', '', '', 'mod-dolicarbon page-factors');

print load_fiche_titre($langs->trans('CarbonFactors'), '', 'fa-percent');

if ($user->hasRight('dolicarbon', 'write')) {
	print '<div class="fichecenter"><div class="fichehalfleft">';
	print '<form method="POST" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="importcsv">';
	print '<table class="noborder centpercent"><tr class="liste_titre"><td colspan="2">'.$langs->trans('ImportCSV').'</td></tr>';
	print '<tr class="oddeven"><td><input type="file" name="csvfile" accept=".csv"></td>';
	print '<td><input type="submit" class="button" value="'.$langs->trans('Upload').'"></td></tr>';
	print '<tr class="oddeven"><td colspan="2"><span class="opacitymedium">'.$langs->trans('DOLICARBON_CSVFormat').'</span></td></tr>';
	print '</table></form>';
	print '</div></div><br>';
}

if (($action == 'create' || $action == 'edit') && $user->hasRight('dolicarbon', 'write')) {
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	if ($action == 'edit' && $id > 0) {
		print '<input type="hidden" name="id" value="'.$id.'">';
		print '<input type="hidden" name="action" value="update">';
	} else {
		print '<input type="hidden" name="action" value="add">';
	}
	print '<table class="border centpercent">';
	if ($action == 'create') {
		print '<tr><td class="titlefield fieldrequired">'.$langs->trans('Code').'</td><td><input name="code" class="minwidth200" value="'.dol_escape_htmltag(GETPOST('code', 'alphanohtml')).'"></td></tr>';
	} else {
		print '<tr><td class="titlefield">'.$langs->trans('Code').'</td><td>'.dol_escape_htmltag($object->code).'</td></tr>';
	}
	print '<tr><td class="fieldrequired">'.$langs->trans('Label').'</td><td><input name="label" class="minwidth300" value="'.dol_escape_htmltag($action == 'edit' ? $object->label : GETPOST('label', 'alphanohtml')).'"></td></tr>';
	print '<tr><td>'.$langs->trans('Category').'</td><td><input name="category" value="'.dol_escape_htmltag($action == 'edit' ? $object->category : GETPOST('category', 'alphanohtml')).'"></td></tr>';
	print '<tr><td>'.$langs->trans('Scope').'</td><td><input name="scope" class="width50" value="'.dol_escape_htmltag((string) ($action == 'edit' ? $object->scope : GETPOSTINT('scope'))).'"></td></tr>';
	print '<tr><td>'.$langs->trans('Unit').'</td><td><input name="unit_input" value="'.dol_escape_htmltag($action == 'edit' ? $object->unit_input : GETPOST('unit_input', 'alphanohtml')).'"></td></tr>';
	print '<tr><td class="fieldrequired">'.$langs->trans('KgCO2ePerUnit').'</td><td><input name="kgco2e_per_unit" value="'.dol_escape_htmltag((string) ($action == 'edit' ? $object->kgco2e_per_unit : GETPOST('kgco2e_per_unit', 'alpha'))).'"></td></tr>';
	print '<tr><td>'.$langs->trans('FactorSource').'</td><td><input name="source" class="minwidth300" value="'.dol_escape_htmltag($action == 'edit' ? $object->source : GETPOST('source', 'alphanohtml')).'"></td></tr>';
	print '<tr><td>'.$langs->trans('Year').'</td><td><input name="year_ref" class="width50" value="'.dol_escape_htmltag((string) ($action == 'edit' ? $object->year_ref : GETPOSTINT('year_ref'))).'"></td></tr>';
	$act = $action == 'edit' ? $object->active : 1;
	print '<tr><td>'.$langs->trans('Active').'</td><td><input type="checkbox" name="active" value="1"'.($act ? ' checked' : '').'></td></tr>';
	print '<tr><td class="tdtop">'.$langs->trans('Note').'</td><td><textarea name="note" class="quatrevingtpercent" rows="4">'.dol_escape_htmltag($action == 'edit' ? $object->note : GETPOST('note', 'restricthtml')).'</textarea></td></tr>';
	print '</table>';
	print '<div class="center"><input type="submit" class="button" value="'.$langs->trans('Save').'"></div>';
	print '</form>';
	if ($action == 'edit' && $user->hasRight('dolicarbon', 'delete')) {
		print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=confirm_delete&token='.newToken().'">'.$langs->trans('Delete').'</a>';
	}
	print '<br><br>';
}

if ($user->hasRight('dolicarbon', 'write') && $action != 'create' && $action != 'edit') {
	print dolGetButtonTitle($langs->trans('NewFactor'), '', 'fa fa-plus-circle', dol_buildpath('/dolicarbon/carbon_factors.php?action=create', 1), '', 1);
	print '<br><br>';
}

$sql = "SELECT rowid, code, label, category, scope, unit_input, kgco2e_per_unit, source, year_ref, active, entity";
$sql .= " FROM ".$db->prefix()."dolicarbon_factor";
$sql .= " WHERE entity IN (0, ".((int) $conf->entity).")";
$sql .= " ORDER BY scope, category, code";

$resql = $db->query($sql);
if ($resql) {
	print '<table class="noborder centpercent liste">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans('Code').'</td><td>'.$langs->trans('Label').'</td><td>'.$langs->trans('Category').'</td>';
	print '<td>'.$langs->trans('Scope').'</td><td>'.$langs->trans('Unit').'</td><td class="right">'.$langs->trans('KgCO2ePerUnit').'</td>';
	print '<td>'.$langs->trans('FactorSource').'</td><td>'.$langs->trans('Active').'</td><td></td>';
	print '</tr>';
	while ($obj = $db->fetch_object($resql)) {
		print '<tr class="oddeven">';
		print '<td>'.dol_escape_htmltag($obj->code).'</td>';
		print '<td>'.dol_escape_htmltag($obj->label).'</td>';
		print '<td>'.dol_escape_htmltag($obj->category).'</td>';
		print '<td>'.((int) $obj->scope).'</td>';
		print '<td>'.dol_escape_htmltag($obj->unit_input).'</td>';
		print '<td class="right">'.price2num($obj->kgco2e_per_unit, 'MT', 6).'</td>';
		print '<td>'.dol_escape_htmltag($obj->source).'</td>';
		print '<td>'.yn($obj->active).'</td>';
		print '<td class="nowrap">';
		if ($user->hasRight('dolicarbon', 'write')) {
			print '<a href="'.dol_buildpath('/dolicarbon/carbon_factors.php', 1).'?id='.((int) $obj->rowid).'&action=edit">'.$langs->trans('Modify').'</a>';
		}
		print '</td>';
		print '</tr>';
	}
	print '</table>';
}

llxFooter();
$db->close();
