<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr> */

/**
 * \file        carbon_actions.php
 * \ingroup     dolicarbon
 * \brief       Reduction actions for a bilan
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
require_once __DIR__.'/class/dolicarbonaction.class.php';
require_once __DIR__.'/lib/dolicarbon.lib.php';

$langs->loadLangs(array('dolicarbon@dolicarbon'));

if (!isModEnabled('dolicarbon') || !$user->hasRight('dolicarbon', 'read')) {
	accessforbidden();
}

$fk_bilan = GETPOSTINT('fk_bilan');
$action = GETPOST('action', 'aZ09');
$id = GETPOSTINT('id');

if ($fk_bilan <= 0) {
	accessforbidden();
}

$bilan = new DoliCarbonBilan($db);
if ($bilan->fetch($fk_bilan) <= 0) {
	accessforbidden();
}

$object = new DoliCarbonAction($db);
if ($id > 0) {
	$object->fetch($id);
	if ((int) $object->fk_bilan !== (int) $fk_bilan) {
		accessforbidden();
	}
}

if ($action == 'add' && $user->hasRight('dolicarbon', 'write')) {
	$object = new DoliCarbonAction($db);
	$object->fk_bilan = $fk_bilan;
	$object->label = GETPOST('label', 'alphanohtml');
	$object->description = GETPOST('description', 'restricthtml');
	$object->category = GETPOST('category', 'alphanohtml');
	$object->gain_tco2e_estimated = GETPOSTFLOAT('gain_tco2e_estimated', 'MT', 1);
	$object->gain_tco2e_actual = GETPOSTFLOAT('gain_tco2e_actual', 'MT', 1);
	$object->cost_eur = GETPOSTFLOAT('cost_eur', 'MT', 1);
	$object->fk_user_responsible = GETPOSTINT('fk_user_responsible');
	$object->date_deadline = dol_mktime(12, 0, 0, GETPOSTINT('date_deadlinemonth'), GETPOSTINT('date_deadlineday'), GETPOSTINT('date_deadlineyear'));
	$object->status = GETPOSTINT('status');
	if ($object->create($user) > 0) {
		setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_actions.php', 1).'?fk_bilan='.$fk_bilan);
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
	$action = 'create';
}

if ($action == 'update' && $id > 0 && $user->hasRight('dolicarbon', 'write')) {
	$object->label = GETPOST('label', 'alphanohtml');
	$object->description = GETPOST('description', 'restricthtml');
	$object->category = GETPOST('category', 'alphanohtml');
	$object->gain_tco2e_estimated = GETPOSTFLOAT('gain_tco2e_estimated', 'MT', 1);
	$object->gain_tco2e_actual = GETPOSTFLOAT('gain_tco2e_actual', 'MT', 1);
	$object->cost_eur = GETPOSTFLOAT('cost_eur', 'MT', 1);
	$object->fk_user_responsible = GETPOSTINT('fk_user_responsible');
	$object->date_deadline = dol_mktime(12, 0, 0, GETPOSTINT('date_deadlinemonth'), GETPOSTINT('date_deadlineday'), GETPOSTINT('date_deadlineyear'));
	$object->status = GETPOSTINT('status');
	if ((int) $object->status === DoliCarbonAction::STATUS_DONE) {
		$object->date_done = dol_mktime(12, 0, 0, GETPOSTINT('date_donemonth'), GETPOSTINT('date_doneday'), GETPOSTINT('date_doneyear'));
	}
	if ($object->update($user) > 0) {
		setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_actions.php', 1).'?fk_bilan='.$fk_bilan);
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
	$action = 'edit';
}

if ($action == 'confirm_delete' && $id > 0 && $user->hasRight('dolicarbon', 'delete')) {
	if ($object->delete($user) > 0) {
		setEventMessages($langs->trans('RecordDeleted'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_actions.php', 1).'?fk_bilan='.$fk_bilan);
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
}

$form = new Form($db);
$head = dolicarbon_bilan_prepare_head($bilan);

llxHeader('', $langs->trans('CarbonActions'), '', '', 0, 0, '', '', '', 'mod-dolicarbon page-actions');

print dol_get_fiche_head($head, 'actions', $bilan->ref, -1, $bilan->picto);

if (($action == 'create' || $action == 'edit') && $user->hasRight('dolicarbon', 'write')) {
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?fk_bilan='.$fk_bilan.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	if ($action == 'edit') {
		print '<input type="hidden" name="id" value="'.$id.'">';
		print '<input type="hidden" name="action" value="update">';
	} else {
		print '<input type="hidden" name="action" value="add">';
	}
	print '<table class="border centpercent">';
	print '<tr><td class="fieldrequired">'.$langs->trans('Label').'</td><td><input name="label" class="minwidth400" value="'.dol_escape_htmltag($action == 'edit' ? $object->label : GETPOST('label', 'alphanohtml')).'"></td></tr>';
	print '<tr><td class="tdtop">'.$langs->trans('Description').'</td><td><textarea name="description" class="quatrevingtpercent" rows="3">'.dol_escape_htmltag($action == 'edit' ? $object->description : GETPOST('description', 'restricthtml')).'</textarea></td></tr>';
	print '<tr><td>'.$langs->trans('Category').'</td><td><input name="category" value="'.dol_escape_htmltag($action == 'edit' ? $object->category : GETPOST('category', 'alphanohtml')).'"></td></tr>';
	print '<tr><td>'.$langs->trans('GainEstimated').'</td><td><input name="gain_tco2e_estimated" value="'.dol_escape_htmltag((string) ($action == 'edit' ? $object->gain_tco2e_estimated : GETPOST('gain_tco2e_estimated', 'alpha'))).'"></td></tr>';
	print '<tr><td>'.$langs->trans('GainActual').'</td><td><input name="gain_tco2e_actual" value="'.dol_escape_htmltag((string) ($action == 'edit' ? $object->gain_tco2e_actual : GETPOST('gain_tco2e_actual', 'alpha'))).'"></td></tr>';
	print '<tr><td>'.$langs->trans('CostEur').'</td><td><input name="cost_eur" value="'.dol_escape_htmltag((string) ($action == 'edit' ? $object->cost_eur : GETPOST('cost_eur', 'alpha'))).'"></td></tr>';
	print '<tr><td>'.$langs->trans('Responsible').'</td><td>';
	print $form->select_users(($action == 'edit' ? $object->fk_user_responsible : GETPOSTINT('fk_user_responsible')), 'fk_user_responsible', 1);
	print '</td></tr>';
	print '<tr><td>'.$langs->trans('Deadline').'</td><td>';
	print $form->selectDate($action == 'edit' ? $object->date_deadline : '', 'date_deadline', 0, 0, 1, '', 1, 0);
	print '</td></tr>';
	$st = $action == 'edit' ? (int) $object->status : DoliCarbonAction::STATUS_PLANNED;
	print '<tr><td>'.$langs->trans('Status').'</td><td>';
	print $form->selectarray('status', array(DoliCarbonAction::STATUS_PLANNED => $langs->trans('ActionPlanned'), DoliCarbonAction::STATUS_IN_PROGRESS => $langs->trans('ActionInProgress'), DoliCarbonAction::STATUS_DONE => $langs->trans('ActionDone')), $st, 0, 0, 0, '', 0, 0, '', '', 1);
	print '</td></tr>';
	if ($action == 'edit') {
		print '<tr><td>'.$langs->trans('DateDone').'</td><td>';
		print $form->selectDate($object->date_done, 'date_done', 0, 0, 1, '', 1, 0);
		print '</td></tr>';
	}
	print '</table>';
	print '<div class="center"><input type="submit" class="button" value="'.$langs->trans('Save').'"></div></form>';
	if ($action == 'edit') {
		print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?fk_bilan='.$fk_bilan.'&id='.$id.'&action=confirm_delete&token='.newToken().'">'.$langs->trans('Delete').'</a>';
	}
	print '<br><br>';
}

if ($user->hasRight('dolicarbon', 'write') && $action != 'create' && $action != 'edit') {
	print dolGetButtonTitle($langs->trans('NewAction'), '', 'fa fa-plus', dol_buildpath('/dolicarbon/carbon_actions.php', 1).'?fk_bilan='.$fk_bilan.'&action=create', '', 1);
	print '<br><br>';
}

$sql = "SELECT rowid, label, category, gain_tco2e_estimated, gain_tco2e_actual, cost_eur, status, date_deadline, date_done";
$sql .= " FROM ".$db->prefix()."dolicarbon_action WHERE fk_bilan = ".((int) $fk_bilan)." ORDER BY date_deadline, rowid";
$resql = $db->query($sql);
if ($resql) {
	print '<table class="noborder centpercent liste">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans('Label').'</td><td>'.$langs->trans('GainEstimated').'</td><td>'.$langs->trans('GainActual').'</td>';
	print '<td>'.$langs->trans('CostEur').'</td><td>'.$langs->trans('Status').'</td><td>'.$langs->trans('Deadline').'</td><td></td>';
	print '</tr>';
	$act = new DoliCarbonAction($db);
	while ($o = $db->fetch_object($resql)) {
		print '<tr class="oddeven">';
		print '<td>'.dol_escape_htmltag($o->label).'</td>';
		print '<td class="right">'.price2num($o->gain_tco2e_estimated, 'MT', 3).'</td>';
		print '<td class="right">'.price2num($o->gain_tco2e_actual, 'MT', 3).'</td>';
		print '<td class="right">'.price2num($o->cost_eur, 'MT', 2).'</td>';
		print '<td>'.$act->getLibStatut((int) $o->status).'</td>';
		print '<td>'.($o->date_deadline ? dol_print_date($db->jdate($o->date_deadline.' 12:00:00'), 'day') : '').'</td>';
		print '<td>';
		if ($user->hasRight('dolicarbon', 'write')) {
			print '<a href="'.dol_buildpath('/dolicarbon/carbon_actions.php', 1).'?fk_bilan='.$fk_bilan.'&id='.((int) $o->rowid).'&action=edit">'.$langs->trans('Modify').'</a>';
		}
		print '</td>';
		print '</tr>';
	}
	print '</table>';
}

print dol_get_fiche_end();

llxFooter();
$db->close();
