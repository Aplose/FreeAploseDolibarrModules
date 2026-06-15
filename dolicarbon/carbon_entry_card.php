<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr> */

/**
 * \file        carbon_entry_card.php
 * \ingroup     dolicarbon
 * \brief       Create/edit carbon entry
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
require_once __DIR__.'/class/dolicarbonbilan.class.php';
require_once __DIR__.'/class/dolicarbonentry.class.php';
require_once __DIR__.'/class/dolicarbonfactor.class.php';
require_once __DIR__.'/lib/dolicarbon.lib.php';

$langs->loadLangs(array('dolicarbon@dolicarbon'));

if (!isModEnabled('dolicarbon')) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$id = GETPOSTINT('id');
$fk_bilan = GETPOSTINT('fk_bilan');

$object = new DoliCarbonEntry($db);
if ($id > 0 && $object->fetch($id) > 0) {
	$fk_bilan = (int) $object->fk_bilan;
}

if ($fk_bilan <= 0) {
	accessforbidden('Missing bilan');
}

$bilan = new DoliCarbonBilan($db);
if ($bilan->fetch($fk_bilan) <= 0) {
	accessforbidden();
}

if ((int) $bilan->status !== DoliCarbonBilan::STATUS_DRAFT && ($action == 'create' || $action == 'add' || $action == 'update')) {
	setEventMessages($langs->trans('ErrorOnlyDraftEditable'), null, 'errors');
	header('Location: '.dol_buildpath('/dolicarbon/carbon_entry_list.php', 1).'?fk_bilan='.$fk_bilan);
	exit;
}

/*
 * Actions
 */
if ($action == 'add' && $user->hasRight('dolicarbon', 'write') && !GETPOST('apply_filter', 'alpha')) {
	$object->fk_bilan = $fk_bilan;
	$object->scope = GETPOSTINT('scope');
	if ($object->scope < 1 || $object->scope > 3) {
		$object->scope = 1;
	}
	$object->category = GETPOST('category', 'alphanohtml');
	$object->label = GETPOST('label', 'alphanohtml');
	$object->quantity = GETPOSTFLOAT('quantity');
	$object->fk_factor = GETPOSTINT('fk_factor');
	$object->unit = GETPOST('unit', 'alpha');
	$object->source_type = 'manual';

	if ($object->create($user) > 0) {
		setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_entry_list.php', 1).'?fk_bilan='.$fk_bilan);
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
	$action = 'create';
}

if ($action == 'update' && $id > 0 && $user->hasRight('dolicarbon', 'write') && !GETPOST('apply_filter', 'alpha')) {
	$object->scope = GETPOSTINT('scope');
	$object->category = GETPOST('category', 'alphanohtml');
	$object->label = GETPOST('label', 'alphanohtml');
	$object->quantity = GETPOSTFLOAT('quantity');
	$object->fk_factor = GETPOSTINT('fk_factor');
	$object->unit = GETPOST('unit', 'alpha');

	if ($object->update($user) > 0) {
		setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_entry_list.php', 1).'?fk_bilan='.$fk_bilan);
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
	$action = 'edit';
}

if ($action == 'confirm_delete' && $id > 0 && $user->hasRight('dolicarbon', 'write')) {
	if ($object->delete($user) > 0) {
		setEventMessages($langs->trans('RecordDeleted'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_entry_list.php', 1).'?fk_bilan='.$fk_bilan);
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
}

if (!$user->hasRight('dolicarbon', 'read')) {
	accessforbidden();
}

/*
 * View
 */
$form = new Form($db);
$catmap = dolicarbon_get_category_map();

$head = dolicarbon_bilan_prepare_head($bilan);
$title = ($action == 'create' || ($id <= 0 && $action != 'edit')) ? $langs->trans('NewEntry') : $langs->trans('EntryCard');

llxHeader('', $title, '', '', 0, 0, '', '', '', 'mod-dolicarbon page-entry-card');

print dol_get_fiche_head($head, 'entries', $bilan->ref, -1, $bilan->picto);

$scope = GETPOSTINT('scope');
if ($scope < 1 || $scope > 3) {
	$scope = $id > 0 ? (int) $object->scope : 1;
}
$category = GETPOST('category', 'alphanohtml');
if ($category === '' && $id > 0) {
	$category = $object->category;
}

$factorlist = array();
if ($scope >= 1 && $scope <= 3 && !empty($category)) {
	$tmpfac = new DoliCarbonFactor($db);
	$factors = $tmpfac->getByCategory($category, $scope);
	foreach ($factors as $f) {
		$factorlist[$f->id] = $f->label.' ('.$f->unit_input.' — '.price2num($f->kgco2e_per_unit, 'MT', 6).' kgCO2e)';
	}
}

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="fk_bilan" value="'.((int) $fk_bilan).'">';
if ($id > 0) {
	print '<input type="hidden" name="id" value="'.((int) $id).'">';
	print '<input type="hidden" name="action" value="update">';
} else {
	print '<input type="hidden" name="action" value="add">';
}

print '<table class="border centpercent">';

print '<tr><td class="titlefield fieldrequired">'.$langs->trans('Scope').'</td><td>';
$scopesel = array(1 => $langs->trans('Scope1'), 2 => $langs->trans('Scope2'), 3 => $langs->trans('Scope3'));
print $form->selectarray('scope', $scopesel, $scope, 0, 0, 0, '', 0, 0, '', '', 1);
print '</td></tr>';

print '<tr><td class="fieldrequired">'.$langs->trans('Category').'</td><td>';
$catopts = array();
if (isset($catmap[$scope])) {
	foreach ($catmap[$scope] as $code => $langkey) {
		$catopts[$code] = $langs->trans($langkey);
	}
}
print $form->selectarray('category', $catopts, $category, 1, 0, 0, '', 0, 0, '', '', 1);
print ' <input type="submit" class="button smallpaddingimp" name="apply_filter" value="'.$langs->trans('Refresh').'">';
print '</td></tr>';

print '<tr><td>'.$langs->trans('CarbonFactor').'</td><td>';
if (count($factorlist)) {
	print $form->selectarray('fk_factor', $factorlist, ($id > 0 ? (int) $object->fk_factor : ''), 1, 0, 0, '', 0, 0, '', '', 1);
} else {
	print '<span class="opacitymedium">'.$langs->trans('SelectCategoryFirst').'</span>';
}
print '</td></tr>';

print '<tr><td class="fieldrequired">'.$langs->trans('Quantity').'</td><td><input type="text" name="quantity" value="'.dol_escape_htmltag((string) ($id > 0 ? $object->quantity : GETPOST('quantity', 'alpha'))).'"></td></tr>';
print '<tr><td>'.$langs->trans('Unit').'</td><td><input type="text" name="unit" class="maxwidth150" value="'.dol_escape_htmltag((string) ($id > 0 ? $object->unit : GETPOST('unit', 'alpha'))).'"></td></tr>';
print '<tr><td>'.$langs->trans('Label').'</td><td><input type="text" name="label" class="minwidth400" value="'.dol_escape_htmltag((string) ($id > 0 ? $object->label : GETPOST('label', 'alphanohtml'))).'"></td></tr>';

if ($id > 0) {
	print '<tr><td>'.$langs->trans('TotalTco2e').'</td><td><strong>'.price2num($object->tco2e_computed, 'MT', 3).'</strong> '.$langs->trans('Tco2eShort').'</td></tr>';
}

print '</table>';

print '<div class="center">';
if ($id > 0) {
	print '<input type="submit" class="button" value="'.$langs->trans('Save').'">';
} else {
	print '<input type="submit" class="button" value="'.$langs->trans('Create').'">';
}
print '</div>';
print '</form>';

if ($id > 0 && $user->hasRight('dolicarbon', 'write')) {
	print '<div class="tabsAction">';
	print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&fk_bilan='.$fk_bilan.'&action=confirm_delete&token='.newToken().'">'.$langs->trans('Delete').'</a>';
	print '</div>';
}

print dol_get_fiche_end();

llxFooter();
$db->close();
