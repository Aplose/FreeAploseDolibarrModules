<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       doliprospectform/public/form_professionnel.php
 * \ingroup    doliprospectform
 * \brief      Public form: company prospect + electricity/gas bills and KBIS (PDF)
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
dol_include_once('custom/doliprospectform/lib/doliprospectform_publicform.lib.php');

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Translate $langs
 * @var User $user
 * @var Societe $mysoc
 */

$langs->loadLangs(array('main', 'companies', 'doliprospectform@doliprospectform', 'other'));

if (!isModEnabled('doliprospectform')) {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicModuleDisabled'), 403, 1);
}
if (!isModEnabled('societe')) {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicSocieteModuleDisabled'), 403, 1);
}
if (!doliprospectform_publicform_is_public_professional_enabled()) {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicFormProfessionalDisabled'), 403, 1);
}

$token = GETPOST('t', 'aZ09', 0, null, null, 1);
$action = GETPOST('action', 'aZ09');
$hookmanager->initHooks(array('doliprospectformpublicprofessional', 'globalcard'));

$payload = doliprospectform_publicform_verify_token($db, $token);
if ($payload === false || empty($payload['f']) || $payload['f'] !== 'professional') {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicInvalidLink'), 403, 1);
}

if ((int) $payload['e'] !== (int) $entity) {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicInvalidLink'), 403, 1);
}

$socIdFromToken = (isset($payload['s']) && is_numeric($payload['s'])) ? (int) $payload['s'] : 0;
$linkedThirdparty = null;
if ($socIdFromToken > 0) {
	$linkedThirdparty = new Societe($db);
	if ($linkedThirdparty->fetch($socIdFromToken) <= 0) {
		httponly_accessforbidden($langs->trans('DoliProspectFormPublicThirdpartyNotFound'), 403, 1);
	}
	if ((int) $linkedThirdparty->entity !== (int) $entity) {
		httponly_accessforbidden($langs->trans('DoliProspectFormPublicInvalidLink'), 403, 1);
	}
}

$assignedCommercialId = isset($payload['u']) ? (int) $payload['u'] : 0;
$actorUser = doliprospectform_publicform_get_public_form_actor_user($db, $entity);
if (!is_object($actorUser) || empty($actorUser->id)) {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicActorUserNotConfigured'), 403, 1);
}

$user = $actorUser;
$captchaobj = doliprospectform_publicform_get_captcha_object($db, $langs, $user);

$franceCountryId = (int) dol_getIdFromCode($db, 'FR', 'c_country', 'code', 'rowid');

$errors = array();
$success = GETPOSTINT('submitted') === 1;

if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$postedToken = GETPOST('t', 'aZ09', 0, null, null, 1);
	if ($postedToken !== $token) {
		$errors[] = $langs->trans('DoliProspectFormPublicInvalidLink');
	} elseif (!doliprospectform_publicform_validate_captcha_submission($captchaobj, $langs, $errors)) {
		// ErrorBadValueForCode already appended
	} else {
		$companyPicked = GETPOSTINT('company_picked');
		$companyName = trim(GETPOST('company_name', 'restricthtml'));
		$companySiren = preg_replace('/\D/', '', GETPOST('company_siren', 'alphanohtml'));
		$companySiret = preg_replace('/\D/', '', GETPOST('company_siret', 'alphanohtml'));
		$companyNaf = trim(GETPOST('company_naf', 'alphanohtml'));
		$companyAddress = trim(GETPOST('company_address', 'alphanohtml'));
		$companyZip = trim(GETPOST('company_zip', 'alphanohtml'));
		$companyTown = trim(GETPOST('company_town', 'alphanohtml'));
		$companyCountryId = GETPOSTINT('company_country_id');

		$firstname = trim(GETPOST('firstname', 'alphanohtml'));
		$lastname = trim(GETPOST('lastname', 'alphanohtml'));
		$email = trim(GETPOST('email', 'restricthtml'));
		$phone = trim(GETPOST('phone', 'alphanohtml'));
		$civility = GETPOST('civility_code', 'aZ09');

		if ($companyPicked !== 1) {
			$errors[] = $langs->trans('DoliProspectFormCompanyPickRequired');
		}
		if ($companyName === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('DoliProspectFormCompanyName'));
		}
		if (!preg_match('/^\d{9}$/', $companySiren)) {
			$errors[] = $langs->trans('DoliProspectFormCompanySirenInvalid');
		}
		if (!preg_match('/^\d{14}$/', $companySiret)) {
			$errors[] = $langs->trans('DoliProspectFormCompanySiretInvalid');
		}
		if ($companyAddress === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Address'));
		}
		if ($companyZip === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Zip'));
		}
		if ($companyTown === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Town'));
		}
		if ($companyCountryId <= 0) {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Country'));
		}

		if ($lastname === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Lastname'));
		}
		if ($firstname === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Firstname'));
		}
		if ($email === '' || !isValidEmail($email)) {
			$errors[] = $langs->trans('ErrorBadEMail', $email);
		}

		$pdfField = doliprospectform_publicform_pdf_collection_field();
		doliprospectform_publicform_validate_pdf_upload_collection($pdfField, $langs, $errors);

		if (empty($errors)) {
			$nbPdfFiles = count(doliprospectform_publicform_normalize_uploaded_files($pdfField));
			$runContactAndUploadPdfs = static function (Societe $thirdparty) use ($db, $langs, $conf, $actorUser, $firstname, $lastname, $civility, $companyAddress, $companyZip, $companyTown, $companyCountryId, $email, $phone, &$errors, $entity, $token, $pdfField, $assignedCommercialId, $nbPdfFiles) {
				$contact = new Contact($db);
				$contact->socid = $thirdparty->id;
				$contact->firstname = $firstname;
				$contact->lastname = $lastname;
				$contact->civility_code = $civility;
				$contact->address = $companyAddress;
				$contact->zip = $companyZip;
				$contact->town = $companyTown;
				$contact->country_id = $companyCountryId;
				$contact->state_id = 0;
				$contact->email = $email;
				$contact->phone_pro = $phone;
				$contact->phone_mobile = $phone;
				$contact->statut = 1;
				$contact->status = 1;
				$contact->ip = $thirdparty->ip;

				$resContact = $contact->create($actorUser);
				if ($resContact < 0) {
					$errors[] = $contact->error ? $contact->error : $langs->trans('DoliProspectFormPublicContactCreateFailed');
					return false;
				}

				$upload_dir = $conf->societe->multidir_output[$thirdparty->entity].'/'.$thirdparty->id;
				if (!dol_mkdir($upload_dir)) {
					$errors[] = $langs->trans('DoliProspectFormPublicDirCreateFailed');
					$contact->delete($actorUser, 0);
					return false;
				}

				if (!doliprospectform_publicform_save_pdf_collection_to_dir($pdfField, $upload_dir, $thirdparty, $langs, $errors)) {
					$contact->delete($actorUser, 0);
					return false;
				}

				$subRowId = doliprospectform_publicform_register_submission($db, $actorUser, $entity, $thirdparty, (int) $contact->id, $assignedCommercialId, 'professional', $nbPdfFiles);
				if ($subRowId > 0) {
					doliprospectform_publicform_send_submission_notification($db, $langs, $actorUser, $entity, $thirdparty, $contact, $assignedCommercialId, 'professional', $nbPdfFiles, $subRowId);
				}

				header('Location: '.$_SERVER['PHP_SELF'].'?e='.((int) $entity).'&t='.urlencode($token).'&submitted=1');
				exit;
			};

			if ($socIdFromToken > 0) {
				$thirdparty = new Societe($db);
				if ($thirdparty->fetch($socIdFromToken) <= 0) {
					$errors[] = $langs->trans('DoliProspectFormPublicThirdpartyNotFound');
				} elseif ((int) $thirdparty->entity !== (int) $entity) {
					$errors[] = $langs->trans('DoliProspectFormPublicInvalidLink');
				} else {
					$thirdparty->particulier = 0;
					$thirdparty->name = $companyName;
					$thirdparty->address = $companyAddress;
					$thirdparty->zip = $companyZip;
					$thirdparty->town = $companyTown;
					$thirdparty->country_id = $companyCountryId;
					$thirdparty->state_id = 0;
					$thirdparty->idprof1 = $companySiren;
					$thirdparty->idprof2 = $companySiret;
					if ($franceCountryId > 0 && (int) $companyCountryId === $franceCountryId && $companyNaf !== '') {
						$thirdparty->idprof3 = $companyNaf;
					}
					$thirdparty->ip = getUserRemoteIP();
					$note = $langs->trans('DoliProspectFormPublicOriginNote', dol_print_date(dol_now(), 'dayhour', 'tzuser'), $thirdparty->ip);
					$thirdparty->note_private = ($thirdparty->note_private ? $thirdparty->note_private."\n\n" : '').$note;

					if ($assignedCommercialId > 0) {
						$thirdparty->commercial_id = $assignedCommercialId;
					}
					$resUpd = $thirdparty->update($thirdparty->id, $actorUser, 1, 0, 0);
					if ($resUpd < 0) {
						$errors[] = $thirdparty->error ? $thirdparty->error : implode(', ', $thirdparty->errors);
					} else {
						$runContactAndUploadPdfs($thirdparty);
					}
				}
			} else {
				$thirdparty = new Societe($db);
				$thirdparty->particulier = 0;
				$thirdparty->name = $companyName;
				$thirdparty->client = Societe::PROSPECT;
				$thirdparty->fournisseur = 0;
				$thirdparty->typent_id = (int) dol_getIdFromCode($db, 'TE_SMALL', 'c_typent', 'code', 'id');
				if ($thirdparty->typent_id <= 0) {
					$thirdparty->typent_id = 0;
				}
				$thirdparty->address = $companyAddress;
				$thirdparty->zip = $companyZip;
				$thirdparty->town = $companyTown;
				$thirdparty->country_id = $companyCountryId;
				$thirdparty->state_id = 0;
				$thirdparty->idprof1 = $companySiren;
				$thirdparty->idprof2 = $companySiret;
				if ($franceCountryId > 0 && (int) $companyCountryId === $franceCountryId && $companyNaf !== '') {
					$thirdparty->idprof3 = $companyNaf;
				}
				$thirdparty->commercial_id = $assignedCommercialId > 0 ? $assignedCommercialId : 0;
				$thirdparty->ip = getUserRemoteIP();
				$note = $langs->trans('DoliProspectFormPublicOriginNote', dol_print_date(dol_now(), 'dayhour', 'tzuser'), $thirdparty->ip);
				$thirdparty->note_private = ($thirdparty->note_private ? $thirdparty->note_private."\n\n" : '').$note;

				doliprospectform_publicform_assign_customer_code($thirdparty);

				$resCreate = $thirdparty->create($actorUser);
				if ($resCreate < 0) {
					$errors[] = $thirdparty->error ? $thirdparty->error : implode(', ', $thirdparty->errors);
				} else {
					if (!$runContactAndUploadPdfs($thirdparty)) {
						$thirdparty->delete($thirdparty->id, $actorUser, 0);
					}
				}
			}
		}
	}
}

$usePostValues = ($_SERVER['REQUEST_METHOD'] === 'POST' && GETPOST('action', 'aZ09') === 'submit');
global $mysoc;
$defaultMainCompanyCountryId = doliprospectform_publicform_get_main_company_country_id($db, $mysoc);
if ($usePostValues) {
	$dispCompanyPicked = GETPOSTINT('company_picked');
	$dispCompanyName = GETPOST('company_name', 'restricthtml');
	$dispCompanySiren = GETPOST('company_siren', 'alphanohtml');
	$dispCompanySiret = GETPOST('company_siret', 'alphanohtml');
	$dispCompanyNaf = GETPOST('company_naf', 'alphanohtml');
	$dispCompanyAddress = GETPOST('company_address', 'alphanohtml');
	$dispCompanyZip = GETPOST('company_zip', 'alphanohtml');
	$dispCompanyTown = GETPOST('company_town', 'alphanohtml');
	$dispCompanyCountryId = GETPOSTINT('company_country_id');
	$dispCivility = GETPOST('civility_code', 'aZ09');
	$dispFirstname = GETPOST('firstname', 'alphanohtml');
	$dispLastname = GETPOST('lastname', 'alphanohtml');
	$dispEmail = GETPOST('email', 'restricthtml');
	$dispPhone = GETPOST('phone', 'alphanohtml');
} elseif ($linkedThirdparty) {
	$dispCompanyPicked = 1;
	$dispCompanyName = (string) $linkedThirdparty->name;
	$dispCompanySiren = preg_replace('/\D/', '', (string) $linkedThirdparty->idprof1);
	$dispCompanySiret = preg_replace('/\D/', '', (string) $linkedThirdparty->idprof2);
	$dispCompanyNaf = (string) $linkedThirdparty->idprof3;
	$dispCompanyAddress = (string) $linkedThirdparty->address;
	$dispCompanyZip = (string) $linkedThirdparty->zip;
	$dispCompanyTown = (string) $linkedThirdparty->town;
	$dispCompanyCountryId = (int) $linkedThirdparty->country_id;
	$dispCivility = '';
	$dispFirstname = '';
	$dispLastname = '';
	$dispEmail = '';
	$dispPhone = '';
} else {
	$dispCompanyPicked = 0;
	$dispCompanyName = '';
	$dispCompanySiren = '';
	$dispCompanySiret = '';
	$dispCompanyNaf = '';
	$dispCompanyAddress = '';
	$dispCompanyZip = '';
	$dispCompanyTown = '';
	$dispCompanyCountryId = $defaultMainCompanyCountryId;
	$dispCivility = '';
	$dispFirstname = '';
	$dispLastname = '';
	$dispEmail = '';
	$dispPhone = '';
}

if ((int) $dispCompanyCountryId <= 0) {
	$dispCompanyCountryId = $defaultMainCompanyCountryId;
}

$ajaxSearchUrl = dol_buildpath('/custom/doliprospectform/ajax/recherche_entreprises.php', 2);

$title = doliprospectform_publicform_get_form_title($langs, 'professional');
llxHeader('', dol_escape_htmltag($title), '', '', 0, 0, doliprospectform_public_llx_js(), doliprospectform_public_llx_css(), '', 'doliprospectform-public-body', '');

print '<div class="doliprospectform-public centpercent">';

if ($success) {
	print '<div class="dpf-card p-4 p-md-5">';
	print '<div class="dpf-alert dpf-alert-success d-flex align-items-start gap-3" role="status">';
	print '<i class="bi bi-check-circle-fill fs-4" style="color:#059669" aria-hidden="true"></i>';
	print '<div class="fw-semibold">'.$langs->trans('DoliProspectFormPublicThankYou').'</div>';
	print '</div></div></div>';
	llxFooter();
	$db->close();
	exit;
}

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
print '<span class="dpf-hero-heading-text">'.dol_escape_htmltag($title).'</span></h1>';
print '<p class="dpf-hero-lead mb-0">'.doliprospectform_publicform_get_resolved_intro_html($db, $langs, $entity, $assignedCommercialId, 'professional').'</p>';
print '</div></div>';

$form = new Form($db);
$formcompany = new FormCompany($db);

print '<div class="dpf-card p-3 p-md-4">';
print '<form method="post" enctype="multipart/form-data" action="'.dol_escape_htmltag($_SERVER['PHP_SELF']).'" class="doliprospectform-public-form" id="dpf-pro-form">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="submit">';
print '<input type="hidden" name="e" value="'.((int) $entity).'">';
print '<input type="hidden" name="t" value="'.dol_escape_htmltag($token).'">';
print '<input type="hidden" name="company_picked" id="company_picked" value="'.((int) $dispCompanyPicked).'">';
print '<input type="hidden" name="company_naf" id="company_naf" value="'.dol_escape_htmltag($dispCompanyNaf).'">';

print '<h2 class="dpf-section-title"><i class="bi bi-building me-2" aria-hidden="true"></i>'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSectionCompany')).'</h2>';
print '<p class="dpf-hint mb-3">'.dol_escape_htmltag($langs->trans('DoliProspectFormCompanySearchHelp')).'</p>';
print '<div class="row g-3 mb-3">';
print '<div class="col-12 col-md-8"><label class="dpf-label" for="company_search_q">'.$langs->trans('DoliProspectFormCompanySearchQuery').'</label>';
print '<input class="dpf-form-control" type="text" id="company_search_q" autocomplete="organization" placeholder="'.dol_escape_htmltag($langs->trans('DoliProspectFormCompanySearchPlaceholder')).'">';
print '</div>';
print '<div class="col-12 col-md-4 d-flex align-items-end"><button type="button" class="dpf-btn-submit dpf-btn-secondary w-100" id="company_search_btn">'.$langs->trans('DoliProspectFormCompanySearchButton').'</button></div>';
print '</div>';
print '<div id="company_search_results" class="dpf-company-results mb-4" hidden></div>';
print '<div id="company_search_err" class="dpf-alert dpf-alert-danger mb-3" style="display:none" role="alert"></div>';

print '<div class="row g-3 mb-4">';
print '<div class="col-md-6"><label class="dpf-label" for="company_siren">'.$langs->trans('DoliProspectFormCompanySiren').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="company_siren" name="company_siren" inputmode="numeric" pattern="\\d{9}" maxlength="11" required value="'.dol_escape_htmltag($dispCompanySiren).'"></div>';
print '<div class="col-md-6"><label class="dpf-label" for="company_siret">'.$langs->trans('DoliProspectFormCompanySiret').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="company_siret" name="company_siret" inputmode="numeric" pattern="\\d{14}" maxlength="20" required value="'.dol_escape_htmltag($dispCompanySiret).'"></div>';
print '<div class="col-12"><label class="dpf-label" for="company_name">'.$langs->trans('DoliProspectFormCompanyName').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="company_name" name="company_name" autocomplete="organization" required value="'.dol_escape_htmltag($dispCompanyName).'"></div>';
print '</div>';

print '<h2 class="dpf-section-title"><i class="bi bi-geo-alt me-2" aria-hidden="true"></i>'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSectionCompanyAddress')).'</h2>';
print '<div class="row g-3 mb-4">';
print '<div class="col-12"><label class="dpf-label" for="company_address">'.$langs->trans('Address').' <span class="text-danger">*</span></label>';
print '<textarea class="dpf-form-control" id="company_address" name="company_address" rows="3" autocomplete="street-address" required>'.dol_escape_htmltag($dispCompanyAddress).'</textarea></div>';
print '<div class="col-12 col-md-4"><label class="dpf-label" for="company_zip">'.$langs->trans('Zip').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="company_zip" name="company_zip" autocomplete="postal-code" required value="'.dol_escape_htmltag($dispCompanyZip).'"></div>';
print '<div class="col-12 col-md-4"><label class="dpf-label" for="company_town">'.$langs->trans('Town').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="company_town" name="company_town" autocomplete="address-level2" required value="'.dol_escape_htmltag($dispCompanyTown).'"></div>';
print '<div class="col-12 col-md-4"><label class="dpf-label" for="selectcompany_country_id">'.$langs->trans('Country').' <span class="text-danger">*</span></label>';
print $form->select_country($dispCompanyCountryId, 'company_country_id', '', 0, 'dpf-form-select w-100', '', 1, 0, 0, array(), 0, 1);
print '</div>';
print '</div>';

print '<h2 class="dpf-section-title"><i class="bi bi-person-badge me-2" aria-hidden="true"></i>'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSectionContactPerson')).'</h2>';
print '<div class="row g-3 mb-4">';
print '<div class="col-12"><label class="dpf-label" for="civility_code">'.$langs->trans('Civility').'</label>';
print $formcompany->select_civility($dispCivility, 'civility_code', 'dpf-form-select w-100', 0);
print '</div>';
print '<div class="col-md-6"><label class="dpf-label" for="firstname">'.$langs->trans('Firstname').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="firstname" name="firstname" autocomplete="given-name" required value="'.dol_escape_htmltag($dispFirstname).'"></div>';
print '<div class="col-md-6"><label class="dpf-label" for="lastname">'.$langs->trans('Lastname').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="lastname" name="lastname" autocomplete="family-name" required value="'.dol_escape_htmltag($dispLastname).'"></div>';
print '<div class="col-md-6"><label class="dpf-label" for="email">'.$langs->trans('Email').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="email" type="email" name="email" autocomplete="email" required value="'.dol_escape_htmltag($dispEmail).'"></div>';
print '<div class="col-md-6"><label class="dpf-label" for="phone">'.$langs->trans('Phone').'</label>';
print '<input class="dpf-form-control" id="phone" type="tel" name="phone" autocomplete="tel" value="'.dol_escape_htmltag($dispPhone).'"></div>';
print '</div>';

print '<h2 class="dpf-section-title"><i class="bi bi-file-earmark-pdf me-2" aria-hidden="true"></i>'.dol_escape_htmltag(doliprospectform_publicform_get_doc_section_title($langs, 'professional')).'</h2>';
print '<p class="dpf-hint mb-2">'.dol_escape_htmltag(doliprospectform_publicform_get_doc_section_hint($langs, 'professional')).'</p>';
print '<div class="dpf-file-zone mb-2">';
print '<label class="dpf-label d-flex align-items-center gap-2" for="dpf_pdf_first"><i class="bi bi-cloud-arrow-up text-primary" aria-hidden="true"></i> '.$langs->trans('DoliProspectFormPublicDocumentsPdf').' <span class="text-danger">*</span></label>';
print '<div id="dpf-pdf-wrap" class="dpf-pdf-inputs">';
print '<div class="dpf-file-row mb-2">';
print '<input class="dpf-form-control" id="dpf_pdf_first" type="file" name="doliprospectform_pdfs[]" multiple accept="application/pdf,.pdf">';
print '</div></div></div>';
print '<button type="button" class="dpf-btn-add-pdf mb-4" id="dpf_add_pdf_input">'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicAddPdfField')).'</button>';

doliprospectform_publicform_print_captcha_block($captchaobj, doliprospectform_publicform_get_captcha_reload_url($entity, $token), false);

print '<button type="submit" class="dpf-btn-submit"><i class="bi bi-send-fill" aria-hidden="true"></i> '.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSubmit')).'</button>';

print '</form>';
print '</div>';
print '</div>';

$jsMsgs = array(
	'empty' => $langs->trans('DoliProspectFormCompanySearchEmpty'),
	'fail' => $langs->trans('DoliProspectFormCompanySearchFailed'),
	'noresult' => $langs->trans('DoliProspectFormCompanySearchNoResult'),
);
$jsTableHeaders = array(
	$langs->transnoentitiesnoconv('DoliProspectFormCompanySiren'),
	$langs->transnoentitiesnoconv('DoliProspectFormCompanySiret'),
	$langs->transnoentitiesnoconv('DoliProspectFormCompanyName'),
	$langs->transnoentitiesnoconv('Town'),
);
print '<script>
(function(){
  var ajaxUrl = '.json_encode($ajaxSearchUrl).';
  var signedToken = '.json_encode($token).';
  var entity = '.((int) $entity).';
  var msgs = '.json_encode($jsMsgs).';
  var headers = '.json_encode($jsTableHeaders).';
  var box = document.getElementById("company_search_results");
  var err = document.getElementById("company_search_err");
  var q = document.getElementById("company_search_q");
  var btn = document.getElementById("company_search_btn");
  function showErr(t){ err.textContent = t; err.style.display = "block"; }
  function hideErr(){ err.style.display = "none"; err.textContent = ""; }
  function setPicked(v){ document.getElementById("company_picked").value = v ? "1" : "0"; }
  function fillRow(r){
    document.getElementById("company_siren").value = (r.siren||"").replace(/\\D/g,"");
    document.getElementById("company_siret").value = (r.siret||"").replace(/\\D/g,"");
    document.getElementById("company_name").value = r.nom_raison_sociale||"";
    document.getElementById("company_address").value = r.adresse_complete||"";
    document.getElementById("company_zip").value = r.code_postal||"";
    document.getElementById("company_town").value = r.commune||"";
    document.getElementById("company_naf").value = r.activite_principale||"";
    setPicked(true);
    box.hidden = true;
    hideErr();
  }
  btn.addEventListener("click", function(){
    hideErr();
    var query = (q.value||"").trim();
    if(!query){ showErr(msgs.empty); return; }
    btn.disabled = true;
    var url = ajaxUrl + "?e=" + encodeURIComponent(String(entity)) + "&t=" + encodeURIComponent(signedToken) + "&q=" + encodeURIComponent(query);
    fetch(url, { credentials: "same-origin", headers: { "Accept": "application/json" } })
      .then(function(res){ return res.json().then(function(j){ return { ok: res.ok, j: j }; }); })
      .then(function(x){
        btn.disabled = false;
        if(!x.ok || !x.j || !x.j.ok){ showErr((x.j && x.j.message) ? x.j.message : msgs.fail); box.hidden = true; return; }
        var rows = x.j.results || [];
        if(!rows.length){ box.innerHTML = "<p class=\\"dpf-hint mb-0\\">"+msgs.noresult+"</p>"; box.hidden = false; return; }
        var tb = document.createElement("table"); tb.className = "dpf-company-table";
        var thead = document.createElement("thead"); var hr = document.createElement("tr");
        headers.forEach(function(h){
          var th = document.createElement("th"); th.textContent = h; hr.appendChild(th);
        });
        thead.appendChild(hr); tb.appendChild(thead);
        var tbody = document.createElement("tbody");
        rows.forEach(function(r){
          var tr = document.createElement("tr"); tr.className = "dpf-company-row"; tr.tabIndex = 0; tr.setAttribute("role","button");
          [r.siren||"", r.siret||"", r.nom_raison_sociale||"", r.commune||""].forEach(function(cell){
            var td = document.createElement("td"); td.textContent = cell; tr.appendChild(td);
          });
          tr.addEventListener("click", function(){ fillRow(r); });
          tr.addEventListener("keydown", function(ev){ if(ev.key==="Enter"||ev.key===" "){ ev.preventDefault(); fillRow(r);} });
          tbody.appendChild(tr);
        });
        tb.appendChild(tbody); box.innerHTML = ""; box.appendChild(tb); box.hidden = false;
      })
      .catch(function(){ btn.disabled = false; showErr(msgs.fail); });
  });
  ["company_siren","company_siret","company_name","company_address","company_zip","company_town"].forEach(function(id){
    var el = document.getElementById(id);
    if(el){ el.addEventListener("input", function(){ setPicked(false); }); }
  });
  var selCo = document.getElementById("selectcompany_country_id");
  if(selCo){ selCo.addEventListener("change", function(){ setPicked(false); }); }
  var addPdfBtn = document.getElementById("dpf_add_pdf_input");
  if(addPdfBtn){
    addPdfBtn.addEventListener("click", function(){
      var w = document.getElementById("dpf-pdf-wrap");
      if(!w){ return; }
      var d = document.createElement("div");
      d.className = "dpf-file-row mb-2";
      var inp = document.createElement("input");
      inp.type = "file";
      inp.name = "doliprospectform_pdfs[]";
      inp.accept = "application/pdf,.pdf";
      inp.className = "dpf-form-control";
      d.appendChild(inp);
      w.appendChild(d);
    });
  }
})();
</script>';

llxFooter();
$db->close();
