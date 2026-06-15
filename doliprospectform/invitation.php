<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       doliprospectform/invitation.php
 * \ingroup    doliprospectform
 * \brief      Create a prospect third party (provisional name) then open Dolibarr send-mail on its card
 */

$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include str_replace("..", "", $_SERVER["CONTEXT_DOCUMENT_ROOT"])."/main.inc.php";
}
if (!$res && file_exists(__DIR__.'/../../../main.inc.php')) {
	$res = @include __DIR__.'/../../../main.inc.php';
}
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

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Translate $langs
 * @var User $user
 * @var Societe $mysoc
 */

require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
dol_include_once('custom/doliprospectform/lib/doliprospectform.lib.php');
dol_include_once('custom/doliprospectform/lib/doliprospectform_publicform.lib.php');

$langs->loadLangs(array('doliprospectform@doliprospectform', 'companies'));

if (!isModEnabled('doliprospectform')) {
	accessforbidden('Module not enabled');
}
if (!empty($user->socid)) {
	accessforbidden();
}
if (!$user->hasRight('societe', 'creer')) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$errors = array();

if ($action === 'create_invite' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$prospectName = trim(GETPOST('prospect_name', 'restricthtml'));
	$inviteeEmail = trim(GETPOST('invitee_email', 'restricthtml'));

	if ($prospectName === '' || dol_strlen($prospectName) < 2) {
		$errors[] = $langs->trans('DoliProspectFormInvitationErrorName');
	}
	if ($inviteeEmail === '') {
		$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('DoliProspectFormInvitationEmail'));
	} elseif (!isValidEmail($inviteeEmail)) {
		$errors[] = $langs->trans('ErrorBadEMail', $inviteeEmail);
	}

	if (empty($errors)) {
		$thirdparty = new Societe($db);
		$thirdparty->particulier = 1;
		$thirdparty->name = $prospectName;
		$thirdparty->client = Societe::PROSPECT;
		$thirdparty->fournisseur = 0;
		$thirdparty->typent_id = (int) dol_getIdFromCode($db, 'TE_PRIVATE', 'c_typent', 'code', 'id');
		if ($thirdparty->typent_id <= 0) {
			$thirdparty->typent_id = 0;
		}
		$thirdparty->commercial_id = (int) $user->id;
		$thirdparty->ip = getUserRemoteIP();
		if (!empty($mysoc) && !empty($mysoc->country_id)) {
			$thirdparty->country_id = (int) $mysoc->country_id;
		}
		$thirdparty->email = $inviteeEmail;
		$note = $langs->trans('DoliProspectFormInvitationOriginNote', dol_print_date(dol_now(), 'dayhour', 'tzuser'), $thirdparty->ip);
		$thirdparty->note_private = $note;

		doliprospectform_publicform_assign_customer_code($thirdparty);

		$resCreate = $thirdparty->create($user);
		if ($resCreate < 0) {
			$errors[] = $thirdparty->error ? $thirdparty->error : implode(', ', $thirdparty->errors);
		} else {
			$back = dol_buildpath('/doliprospectform/invitation.php', 1);
			$url = DOL_URL_ROOT.'/societe/card.php?socid='.((int) $thirdparty->id).'&action=presend&mode=init';
			$tplId = doliprospectform_get_invitation_presend_template_id($db, $user);
			if ($tplId > 0) {
				$url .= '&modelmailselected='.$tplId;
			}
			$url .= '&backtopage='.urlencode($back);
			$url .= '#formmailbeforetitle';
			header('Location: '.$url);
			exit;
		}
	}
}

if (!empty($errors)) {
	setEventMessages($errors, null, 'errors');
}

llxHeader('', $langs->trans('DoliProspectFormMenuInvitation'), '', '', 0, 0, doliprospectform_backoffice_llx_js(), doliprospectform_backoffice_llx_css(), '', 'mod-doliprospectform page-invitation');

$head = doliprospectform_main_area_prepare_head('invitation');
print dol_get_fiche_head($head, 'invitation', $langs->trans('DoliProspectFormArea'), 0, 'doliprospectform@doliprospectform');

print '<div class="container-fluid px-2 px-md-3 pb-4">';
print '<div class="row justify-content-center">';
print '<div class="col-12 col-lg-10 col-xl-8 col-xxl-7">';

print '<p class="text-body-secondary mb-4">'.$langs->trans('DoliProspectFormInvitationHelp').'</p>';

print '<div class="card border shadow-sm">';
print '<div class="card-header py-3 fw-semibold">'.dol_escape_htmltag($langs->trans('DoliProspectFormInvitationFormTitle')).'</div>';
print '<div class="card-body p-3 p-md-4">';
print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="create_invite">';

print '<div class="mb-3">';
print '<label for="prospect_name" class="form-label">'.dol_escape_htmltag($langs->trans('DoliProspectFormInvitationProspectName')).' <span class="text-danger">*</span></label>';
print '<input type="text" name="prospect_name" id="prospect_name" class="form-control form-control-lg" maxlength="128" required autocomplete="organization" value="'.dol_escape_htmltag(GETPOST('prospect_name', 'restricthtml')).'">';
print '</div>';

print '<div class="mb-4">';
print '<label for="invitee_email" class="form-label">'.dol_escape_htmltag($langs->trans('DoliProspectFormInvitationEmail')).' <span class="text-danger">*</span></label>';
print '<input type="email" name="invitee_email" id="invitee_email" class="form-control form-control-lg" required autocomplete="email" value="'.dol_escape_htmltag(GETPOST('invitee_email', 'restricthtml')).'">';
print '</div>';

print '<div class="d-grid d-sm-flex gap-2 justify-content-sm-end">';
print '<input type="submit" class="btn btn-primary btn-lg px-4" value="'.dol_escape_htmltag($langs->transnoentitiesnoconv('DoliProspectFormInvitationSubmit')).'">';
print '</div>';

print '</form>';
print '</div></div>';

print '</div></div></div>';

llxFooter();
$db->close();
