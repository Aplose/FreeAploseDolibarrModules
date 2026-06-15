<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr> */

/**
 * \file        carbon_bilan_card.php
 * \ingroup     dolicarbon
 * \brief       Carbon bilan card
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
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once __DIR__.'/class/dolicarbonbilan.class.php';
require_once __DIR__.'/lib/dolicarbon.lib.php';

$langs->loadLangs(array('dolicarbon@dolicarbon', 'companies'));

if (!isModEnabled('dolicarbon')) {
	accessforbidden('Module not enabled');
}

$action = GETPOST('action', 'aZ09');
$id = GETPOSTINT('id');
$ref = GETPOST('ref', 'alpha');

$object = new DoliCarbonBilan($db);
if ($id > 0) {
	$result = $object->fetch($id);
	if ($result <= 0) {
		accessforbidden('Record not found');
	}
} elseif ($ref) {
	$result = $object->fetch(0, $ref);
	if ($result <= 0) {
		accessforbidden('Record not found');
	}
	$id = (int) $object->id;
}

/*
 * Actions
 */
if ($action == 'create' && !$user->hasRight('dolicarbon', 'write')) {
	accessforbidden();
}

if ($action == 'add' && $user->hasRight('dolicarbon', 'write')) {
	$object->label = GETPOST('label', 'alphanohtml');
	$object->year = GETPOSTINT('year');
	$object->date_start = dol_mktime(12, 0, 0, GETPOSTINT('date_startmonth'), GETPOSTINT('date_startday'), GETPOSTINT('date_startyear'));
	$object->date_end = dol_mktime(12, 0, 0, GETPOSTINT('date_endmonth'), GETPOSTINT('date_endday'), GETPOSTINT('date_endyear'));
	$object->target_tco2e = GETPOSTFLOAT('target_tco2e');
	$object->fk_soc = GETPOSTINT('fk_soc') > 0 ? GETPOSTINT('fk_soc') : null;
	$object->note_public = GETPOST('note_public', 'restricthtml');
	$object->note_private = GETPOST('note_private', 'restricthtml');
	$object->ref = GETPOST('ref', 'alpha');

	$res = $object->create($user);
	if ($res > 0) {
		setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_bilan_card.php', 1).'?id='.$object->id);
		exit;
	} else {
		setEventMessages($object->error, $object->errors, 'errors');
		$action = 'create';
	}
}

if ($action == 'update' && $id > 0 && $user->hasRight('dolicarbon', 'write')) {
	if ((int) $object->status !== DoliCarbonBilan::STATUS_DRAFT) {
		setEventMessages($langs->trans('ErrorOnlyDraftEditable'), null, 'errors');
	} else {
		$object->label = GETPOST('label', 'alphanohtml');
		$object->year = GETPOSTINT('year');
		$object->date_start = dol_mktime(12, 0, 0, GETPOSTINT('date_startmonth'), GETPOSTINT('date_startday'), GETPOSTINT('date_startyear'));
		$object->date_end = dol_mktime(12, 0, 0, GETPOSTINT('date_endmonth'), GETPOSTINT('date_endday'), GETPOSTINT('date_endyear'));
		$object->target_tco2e = GETPOSTFLOAT('target_tco2e');
		$object->fk_soc = GETPOSTINT('fk_soc') > 0 ? GETPOSTINT('fk_soc') : null;
		$object->note_public = GETPOST('note_public', 'restricthtml');
		$object->note_private = GETPOST('note_private', 'restricthtml');

		if ($object->update($user) > 0) {
			setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
			header('Location: '.dol_buildpath('/dolicarbon/carbon_bilan_card.php', 1).'?id='.$id);
			exit;
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
}

if ($action == 'confirm_validate' && $id > 0 && $user->hasRight('dolicarbon', 'write')) {
	if ($object->validateBilan($user) > 0) {
		setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_bilan_card.php', 1).'?id='.$id);
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
}

if ($action == 'confirm_archive' && $id > 0 && $user->hasRight('dolicarbon', 'write')) {
	if ($object->archiveBilan($user) > 0) {
		setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_bilan_card.php', 1).'?id='.$id);
		exit;
	}
	setEventMessages($object->error, $object->errors, 'errors');
}

if ($action == 'confirm_delete' && $id > 0 && $user->hasRight('dolicarbon', 'delete')) {
	if ($object->delete($user) > 0) {
		setEventMessages($langs->trans('RecordDeleted'), null, 'mesgs');
		header('Location: '.dol_buildpath('/dolicarbon/carbon_bilan_list.php', 1));
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

$title = $langs->trans('BilanCard');
if ($action == 'create') {
	$title = $langs->trans('NewBilan');
}

llxHeader('', $title, '', '', 0, 0, '', '', '', 'mod-dolicarbon page-card');

if ($action == 'create') {
	print load_fiche_titre($langs->trans('NewBilan'), '', 'fa-leaf');

	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';

	print '<table class="border centpercent">';

	print '<tr><td class="titlefield">'.$langs->trans('Ref').'</td><td><input type="text" name="ref" class="minwidth200" value="'.dol_escape_htmltag(GETPOST('ref', 'alpha')).'" placeholder="'.$langs->trans('Auto').'"></td></tr>';
	print '<tr><td>'.$langs->trans('Label').'</td><td><input type="text" name="label" class="minwidth300" value="'.dol_escape_htmltag(GETPOST('label', 'alphanohtml')).'"></td></tr>';
	print '<tr><td class="fieldrequired">'.$langs->trans('Year').'</td><td><input type="text" name="year" class="width50" value="'.dol_escape_htmltag((string) (GETPOSTISSET('year') ? GETPOSTINT('year') : dol_print_date(dol_now(), '%Y'))).'"></td></tr>';

	print '<tr><td>'.$langs->trans('DateStart').'</td><td>';
	print $form->selectDate(dol_now(), 'date_start', 0, 0, 0, 'addform', 1, 0);
	print '</td></tr>';
	print '<tr><td>'.$langs->trans('DateEnd').'</td><td>';
	print $form->selectDate(dol_now(), 'date_end', 0, 0, 0, 'addform', 1, 0);
	print '</td></tr>';

	print '<tr><td>'.$langs->trans('TargetTco2e').'</td><td><input type="text" name="target_tco2e" value="'.dol_escape_htmltag(GETPOST('target_tco2e', 'alpha')).'"></td></tr>';

	if (isModEnabled('societe')) {
		print '<tr><td>'.$langs->trans('ThirdParty').'</td><td>';
		print $form->select_company(GETPOSTINT('fk_soc'), 'fk_soc', '', 1, 0, 0, array(), 0, 'minwidth300');
		print '</td></tr>';
	}

	print '<tr><td class="tdtop">'.$langs->trans('NotePublic').'</td><td>';
	$doleditor = new DolEditor('note_public', GETPOST('note_public', 'restricthtml'), '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_4, '90%');
	print $doleditor->Create();
	print '</td></tr>';

	print '<tr><td class="tdtop">'.$langs->trans('NotePrivate').'</td><td>';
	$doleditor = new DolEditor('note_private', GETPOST('note_private', 'restricthtml'), '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_4, '90%');
	print $doleditor->Create();
	print '</td></tr>';

	print '</table>';

	print '<div class="center">';
	print '<input type="submit" class="button button-save" value="'.$langs->trans('Create').'">';
	print '</div>';
	print '</form>';
} elseif ($id > 0) {
	$head = dolicarbon_bilan_prepare_head($object);
	print dol_get_fiche_head($head, 'card', $object->ref, -1, $object->picto);

	$canedit = $user->hasRight('dolicarbon', 'write') && ((int) $object->status === DoliCarbonBilan::STATUS_DRAFT);

	if ($canedit && $action == 'edit') {
		print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$id.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="update">';

		print '<table class="border centpercent">';
		print '<tr><td class="titlefield">'.$langs->trans('Ref').'</td><td>'.dol_escape_htmltag($object->ref).'</td></tr>';
		print '<tr><td>'.$langs->trans('Label').'</td><td><input type="text" name="label" class="minwidth300" value="'.dol_escape_htmltag($object->label).'"></td></tr>';
		print '<tr><td>'.$langs->trans('Year').'</td><td><input type="text" name="year" class="width50" value="'.((int) $object->year).'"></td></tr>';
		print '<tr><td>'.$langs->trans('DateStart').'</td><td>';
		print $form->selectDate($object->date_start, 'date_start', 0, 0, 0, 'editform', 1, 0);
		print '</td></tr>';
		print '<tr><td>'.$langs->trans('DateEnd').'</td><td>';
		print $form->selectDate($object->date_end, 'date_end', 0, 0, 0, 'editform', 1, 0);
		print '</td></tr>';
		print '<tr><td>'.$langs->trans('TargetTco2e').'</td><td><input type="text" name="target_tco2e" value="'.dol_escape_htmltag((string) $object->target_tco2e).'"></td></tr>';
		if (isModEnabled('societe')) {
			print '<tr><td>'.$langs->trans('ThirdParty').'</td><td>';
			print $form->select_company($object->fk_soc, 'fk_soc', '', 1, 0, 0, array(), 0, 'minwidth300');
			print '</td></tr>';
		}
		print '<tr><td class="tdtop">'.$langs->trans('NotePublic').'</td><td>';
		$doleditor = new DolEditor('note_public', $object->note_public, '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_4, '90%');
		print $doleditor->Create();
		print '</td></tr>';
		print '<tr><td class="tdtop">'.$langs->trans('NotePrivate').'</td><td>';
		$doleditor = new DolEditor('note_private', $object->note_private, '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_4, '90%');
		print $doleditor->Create();
		print '</td></tr>';
		print '</table>';
		print '<div class="center"><input type="submit" class="button" value="'.$langs->trans('Save').'"></div>';
		print '</form>';
	} else {
		print '<table class="border centpercent">';
		print '<tr><td class="titlefield">'.$langs->trans('Ref').'</td><td>'.dol_escape_htmltag($object->ref).'</td></tr>';
		print '<tr><td>'.$langs->trans('Label').'</td><td>'.dol_escape_htmltag($object->label).'</td></tr>';
		print '<tr><td>'.$langs->trans('Year').'</td><td>'.((int) $object->year).'</td></tr>';
		print '<tr><td>'.$langs->trans('DateStart').'</td><td>'.($object->date_start ? dol_print_date($object->date_start, 'day') : '').'</td></tr>';
		print '<tr><td>'.$langs->trans('DateEnd').'</td><td>'.($object->date_end ? dol_print_date($object->date_end, 'day') : '').'</td></tr>';
		print '<tr><td>'.$langs->trans('Status').'</td><td>'.$object->LibStatut((int) $object->status, 0).'</td></tr>';
		print '<tr><td>'.$langs->trans('TotalTco2e').'</td><td class="right">'.price2num($object->total_tco2e, 'MT', 3).'</td></tr>';
		print '<tr><td>'.$langs->trans('TargetTco2e').'</td><td class="right">'.($object->target_tco2e !== null ? price2num($object->target_tco2e, 'MT', 3) : '').'</td></tr>';
		print '</table>';

		print '<div class="tabsAction">';
		if ($canedit) {
			print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=edit&token='.newToken().'">'.$langs->trans('Modify').'</a>';
		}
		if ($user->hasRight('dolicarbon', 'write') && (int) $object->status === DoliCarbonBilan::STATUS_DRAFT) {
			print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=confirm_validate&token='.newToken().'">'.$langs->trans('Validate').'</a>';
		}
		if ($user->hasRight('dolicarbon', 'write') && (int) $object->status === DoliCarbonBilan::STATUS_VALIDATED) {
			print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=confirm_archive&token='.newToken().'">'.$langs->trans('Archive').'</a>';
		}
		if ($user->hasRight('dolicarbon', 'delete')) {
			print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=confirm_delete&token='.newToken().'">'.$langs->trans('Delete').'</a>';
		}
		print '</div>';
	}

	print dol_get_fiche_end();
} else {
	print load_fiche_titre($langs->trans('NewBilan'), '', '');
	print '<div class="warning">'.$langs->trans('ErrorRecordNotFound').'</div>';
}

llxFooter();
$db->close();
