<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       doliprospectform/public/form_aiguillage.php
 * \ingroup    doliprospectform
 * \brief      Public hub: choose individual or professional form (no login)
 */

if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1);
}
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', 1);
}
if (!defined('NOIPCHECK')) {
	define('NOIPCHECK', '1');
}
if (!defined('NOBROWSERNOTIF')) {
	define('NOBROWSERNOTIF', '1');
}

$entity = 1;
if (!empty($_GET['e'])) {
	$entity = (int) $_GET['e'];
} elseif (!empty($_GET['entity'])) {
	$entity = (int) $_GET['entity'];
} elseif (!empty($_POST['e'])) {
	$entity = (int) $_POST['e'];
} elseif (!empty($_POST['entity'])) {
	$entity = (int) $_POST['entity'];
}
if ($entity <= 0) {
	$entity = 1;
}
define('DOLENTITY', $entity);

$res = 0;
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
	$res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/main.inc.php';
}
if (!$res && file_exists(__DIR__.'/../../../main.inc.php')) {
	$res = @include __DIR__.'/../../../main.inc.php';
}
if (!$res) {
	http_response_code(500);
	exit('Include of main fails');
}

require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
dol_include_once('custom/doliprospectform/lib/doliprospectform_publicform.lib.php');

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Translate $langs
 * @var Societe $mysoc
 * @var User $user
 */

$langs->loadLangs(array('main', 'doliprospectform@doliprospectform', 'other'));

if (!isModEnabled('doliprospectform')) {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicModuleDisabled'), 403, 1);
}

// Signed hub token: from GET (?t=) on first load; on POST the query string is not re-sent with a bare
// PHP_SELF action, so the same value is carried in POST as "t" (hidden) and/or "hub_t" (hidden).
$token = GETPOST('t', 'aZ09', 0, null, null, 1);
if ($token === '') {
	$token = GETPOST('hub_t', 'aZ09', 0, null, null, 1);
}
$action = GETPOST('action', 'aZ09');
$hookmanager->initHooks(array('doliprospectformpublichub', 'globalcard'));

$payload = doliprospectform_publicform_verify_token($db, $token);
if ($payload === false || empty($payload['f']) || $payload['f'] !== 'hub') {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicInvalidLink'), 403, 1);
}
if ((int) $payload['e'] !== (int) $entity) {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicInvalidLink'), 403, 1);
}

$socIdFromToken = (isset($payload['s']) && is_numeric($payload['s'])) ? (int) $payload['s'] : 0;
$assignedCommercialFromToken = (isset($payload['u']) && is_numeric($payload['u'])) ? (int) $payload['u'] : 0;
// Hub link with a sales user id in the token: no optional consultant e-mail field (only form type choices).
$hubShowConsultantEmailBlock = ($assignedCommercialFromToken <= 0);

$hubIndividualEnabled = doliprospectform_publicform_is_public_individual_enabled();
$hubProfessionalEnabled = doliprospectform_publicform_is_public_professional_enabled();
if (!$hubIndividualEnabled && !$hubProfessionalEnabled) {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicNoPublicFormEnabled'), 403, 1);
}

global $user;
if (!isset($user) || !is_object($user)) {
	$user = new User($db);
}

$errors = array();

if ($action === 'go' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$postedHubToken = GETPOST('hub_t', 'aZ09', 0, null, null, 1);
	if ($postedHubToken !== $token) {
		$errors[] = $langs->trans('DoliProspectFormPublicInvalidLink');
	} else {
		$target = GETPOST('target', 'aZ09');
		if ($target !== 'individual' && $target !== 'professional') {
			$errors[] = $langs->trans('DoliProspectFormPublicInvalidLink');
		} elseif ($target === 'individual' && !$hubIndividualEnabled) {
			$errors[] = $langs->trans('DoliProspectFormPublicFormIndividualDisabled');
		} elseif ($target === 'professional' && !$hubProfessionalEnabled) {
			$errors[] = $langs->trans('DoliProspectFormPublicFormProfessionalDisabled');
		} else {
			$consultantEmail = $hubShowConsultantEmailBlock ? trim(GETPOST('consultant_email', 'restricthtml')) : '';
			if ($hubShowConsultantEmailBlock && $consultantEmail !== '' && !isValidEmail($consultantEmail)) {
				$errors[] = $langs->trans('ErrorBadEMail', $consultantEmail);
			}
			if (empty($errors)) {
				$assigned = doliprospectform_publicform_hub_resolve_assigned_commercial_id($db, $entity, $payload, $consultantEmail);
				$formCode = ($target === 'individual') ? 'individual' : 'professional';
				$newTok = doliprospectform_publicform_build_token($db, $entity, $assigned, $formCode, $socIdFromToken);
				if ($newTok === false) {
					$errors[] = $langs->trans('DoliProspectFormPublicInvalidLink');
				} else {
					$path = ($target === 'individual') ? 'custom/doliprospectform/public/form_particulier.php' : 'custom/doliprospectform/public/form_professionnel.php';
					header('Location: '.dol_buildpath($path, 2).'?e='.((int) $entity).'&t='.urlencode($newTok));
					exit;
				}
			}
		}
	}
}

$hubTitle = doliprospectform_publicform_get_hub_title($langs);
$title = $hubTitle;
llxHeader('', $title, '', '', 0, 0, doliprospectform_public_llx_js(), doliprospectform_public_llx_css(), '', 'doliprospectform-public-body', '');

print '<div class="doliprospectform-public centpercent">';

if (!empty($errors)) {
	print '<div class="dpf-card p-3 p-md-4 mb-4">';
	print '<div class="dpf-alert dpf-alert-danger d-flex align-items-start gap-3" role="alert">';
	print '<i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0" aria-hidden="true"></i>';
	print '<div><strong class="d-block mb-1">'.$langs->trans('Errors').'</strong><ul class="mb-0 ps-3">';
	foreach ($errors as $msg) {
		print '<li>'.dol_escape_htmltag($msg).'</li>';
	}
	print '</ul></div></div></div>';
}

global $mysoc;
$heroMycompanyLogoUrl = '';
if (is_object($mysoc) && !empty($conf->mycompany->dir_output)) {
	$dirMyCo = $conf->mycompany->dir_output;
	if (!empty($mysoc->logo_squarred_mini) && is_readable($dirMyCo.'/logos/thumbs/'.$mysoc->logo_squarred_mini)) {
		$heroMycompanyLogoUrl = dolBuildUrl(DOL_URL_ROOT.'/viewimage.php', array('cache' => 1, 'modulepart' => 'mycompany', 'entity' => (int) $conf->entity, 'file' => 'logos/thumbs/'.$mysoc->logo_squarred_mini));
	} elseif (!empty($mysoc->logo_squarred_small) && is_readable($dirMyCo.'/logos/thumbs/'.$mysoc->logo_squarred_small)) {
		$heroMycompanyLogoUrl = dolBuildUrl(DOL_URL_ROOT.'/viewimage.php', array('cache' => 1, 'modulepart' => 'mycompany', 'entity' => (int) $conf->entity, 'file' => 'logos/thumbs/'.$mysoc->logo_squarred_small));
	} elseif (!empty($mysoc->logo_squarred) && is_readable($dirMyCo.'/logos/'.$mysoc->logo_squarred)) {
		$heroMycompanyLogoUrl = dolBuildUrl(DOL_URL_ROOT.'/viewimage.php', array('cache' => 1, 'modulepart' => 'mycompany', 'entity' => (int) $conf->entity, 'file' => 'logos/'.$mysoc->logo_squarred));
	}
}

print '<div class="dpf-hero mb-4">';
print '<div class="p-4 p-md-4">';
print '<h1 class="h4 mb-2 d-flex align-items-center gap-3 dpf-hero-heading">';
if ($heroMycompanyLogoUrl !== '') {
	$logoAlt = !empty($mysoc->name) ? $mysoc->name : $title;
	print '<img class="dpf-hero-mysoc-logo" src="'.dol_escape_htmltag($heroMycompanyLogoUrl).'" alt="'.dol_escape_htmltag($logoAlt).'">';
}
print '<span class="dpf-hero-heading-text">'.dol_escape_htmltag($hubTitle).'</span></h1>';
print '<p class="dpf-hero-lead mb-0">'.doliprospectform_publicform_get_resolved_hub_intro_html($db, $langs, $entity, $assignedCommercialFromToken).'</p>';
print '</div></div>';

print '<div class="dpf-card p-3 p-md-4">';
print '<form method="post" action="'.dol_escape_htmltag($_SERVER['PHP_SELF']).'" class="doliprospectform-public-form">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="go">';
print '<input type="hidden" name="e" value="'.((int) $entity).'">';
print '<input type="hidden" name="t" value="'.dol_escape_htmltag($token).'">';
print '<input type="hidden" name="hub_t" value="'.dol_escape_htmltag($token).'">';

if ($hubShowConsultantEmailBlock) {
	print '<div class="mb-4">';
	print '<label class="dpf-label" for="consultant_email">'.dol_escape_htmltag(doliprospectform_publicform_get_hub_consultant_block_title($langs)).'</label>';
	print '<p class="dpf-hint mb-2">'.doliprospectform_publicform_get_hub_consultant_block_subtitle_html($db, $langs, $entity, $assignedCommercialFromToken).'</p>';
	print '<input class="dpf-form-control" type="email" id="consultant_email" name="consultant_email" autocomplete="email" placeholder="'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicHubConsultantEmailPlaceholder')).'">';
	print '</div>';
}

$hubRowClass = 'dpf-hub-choices-row';
if ($hubIndividualEnabled && $hubProfessionalEnabled) {
	$hubRowClass .= ' dpf-hub-choices-row--split';
}
print '<div class="'.$hubRowClass.'">';
if ($hubIndividualEnabled) {
	print '<div class="dpf-hub-choice-wrap">';
	print '<button type="submit" name="target" value="individual" class="dpf-hub-choice w-100">';
	print '<span class="dpf-hub-choice-title"><i class="bi bi-person-fill me-2" aria-hidden="true"></i>'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicHubIndividual')).'</span>';
	print '<span class="dpf-hub-choice-desc">'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicHubIndividualDesc')).'</span>';
	print '</button></div>';
}
if ($hubProfessionalEnabled) {
	print '<div class="dpf-hub-choice-wrap">';
	print '<button type="submit" name="target" value="professional" class="dpf-hub-choice w-100">';
	print '<span class="dpf-hub-choice-title"><i class="bi bi-building-fill me-2" aria-hidden="true"></i>'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicHubProfessional')).'</span>';
	print '<span class="dpf-hub-choice-desc">'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicHubProfessionalDesc')).'</span>';
	print '</button></div>';
}
print '</div>';

print '</form>';
print '</div>';
print '</div>';

llxFooter();
$db->close();
