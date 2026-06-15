<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       doliprospectform/public/form_particulier.php
 * \ingroup    doliprospectform
 * \brief      Public form: individual prospect + energy bill PDF
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
if (!doliprospectform_publicform_is_public_individual_enabled()) {
	httponly_accessforbidden($langs->trans('DoliProspectFormPublicFormIndividualDisabled'), 403, 1);
}

$token = GETPOST('t', 'aZ09', 0, null, null, 1);
$action = GETPOST('action', 'aZ09');
$hookmanager->initHooks(array('doliprospectformpublicindividual', 'globalcard'));

$payload = doliprospectform_publicform_verify_token($db, $token);
if ($payload === false || empty($payload['f']) || $payload['f'] !== 'individual') {
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

$errors = array();
$success = GETPOSTINT('submitted') === 1;

if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$postedToken = GETPOST('t', 'aZ09', 0, null, null, 1);
	if ($postedToken !== $token) {
		$errors[] = $langs->trans('DoliProspectFormPublicInvalidLink');
	} elseif (!doliprospectform_publicform_validate_captcha_submission($captchaobj, $langs, $errors)) {
		// ErrorBadValueForCode already appended
	} else {
		$birthdayTs = null;
		$firstname = trim(GETPOST('firstname', 'alphanohtml'));
		$lastname = trim(GETPOST('lastname', 'alphanohtml'));
		$email = trim(GETPOST('email', 'restricthtml'));
		$phone = trim(GETPOST('phone', 'alphanohtml'));
		$address = trim(GETPOST('address', 'alphanohtml'));
		$zip = trim(GETPOST('zipcode', 'alphanohtml'));
		$town = trim(GETPOST('town', 'alphanohtml'));
		$countryId = GETPOSTINT('country_id');
		$civility = GETPOST('civility_code', 'aZ09');

		if ($lastname === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Lastname'));
		}
		if ($firstname === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Firstname'));
		}
		if ($email === '' || !isValidEmail($email)) {
			$errors[] = $langs->trans('ErrorBadEMail', $email);
		}
		$birthdayStr = trim(GETPOST('birthday', 'alphanohtml'));
		if ($birthdayStr === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('DateOfBirth'));
		} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdayStr)) {
			$errors[] = $langs->trans('DoliProspectFormPublicBirthDateInvalid');
		} else {
			$bdParts = explode('-', $birthdayStr);
			$by = (int) $bdParts[0];
			$bm = (int) $bdParts[1];
			$bd = (int) $bdParts[2];
			if (!checkdate($bm, $bd, $by)) {
				$errors[] = $langs->trans('DoliProspectFormPublicBirthDateInvalid');
			} else {
				$tmpBirth = dol_mktime(0, 0, 0, $bm, $bd, $by);
				$nowCal = dol_getdate(dol_now());
				if ($tmpBirth === false) {
					$errors[] = $langs->trans('DoliProspectFormPublicBirthDateInvalid');
				} elseif ($tmpBirth > dol_now()) {
					$errors[] = $langs->trans('DoliProspectFormPublicBirthDateFuture');
				} elseif ($by < ($nowCal['year'] - 120)) {
					$errors[] = $langs->trans('DoliProspectFormPublicBirthDateUnrealistic');
				} else {
					$birthdayTs = $tmpBirth;
				}
			}
		}
		if ($address === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Address'));
		}
		if ($zip === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Zip'));
		}
		if ($town === '') {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Town'));
		}
		if ($countryId <= 0) {
			$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Country'));
		}
		$pdfField = doliprospectform_publicform_pdf_collection_field();
		doliprospectform_publicform_validate_pdf_upload_collection($pdfField, $langs, $errors);

		if (empty($errors)) {
			$nbPdfFiles = count(doliprospectform_publicform_normalize_uploaded_files($pdfField));
			$runContactAndUpload = static function (Societe $thirdparty) use ($db, $langs, $conf, $actorUser, $firstname, $lastname, $civility, $address, $zip, $town, $countryId, $email, $phone, $birthdayTs, &$errors, $entity, $token, $pdfField, $assignedCommercialId, $nbPdfFiles) {
				$contact = new Contact($db);
				$contact->socid = $thirdparty->id;
				$contact->firstname = $firstname;
				$contact->lastname = $lastname;
				$contact->civility_code = $civility;
				$contact->address = $address;
				$contact->zip = $zip;
				$contact->town = $town;
				$contact->country_id = $countryId;
				$contact->state_id = 0;
				$contact->email = $email;
				$contact->phone_pro = $phone;
				$contact->phone_mobile = $phone;
				$contact->statut = 1;
				$contact->status = 1;
				$contact->ip = $thirdparty->ip;
				if ($birthdayTs !== null) {
					$contact->birthday = $birthdayTs;
				}

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

				$subRowId = doliprospectform_publicform_register_submission($db, $actorUser, $entity, $thirdparty, (int) $contact->id, $assignedCommercialId, 'individual', $nbPdfFiles);
				if ($subRowId > 0) {
					doliprospectform_publicform_send_submission_notification($db, $langs, $actorUser, $entity, $thirdparty, $contact, $assignedCommercialId, 'individual', $nbPdfFiles, $subRowId);
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
					$thirdparty->particulier = 1;
					$thirdparty->name = dolGetFirstLastname($firstname, $lastname);
					$thirdparty->address = $address;
					$thirdparty->zip = $zip;
					$thirdparty->town = $town;
					$thirdparty->country_id = $countryId;
					$thirdparty->state_id = 0;
					$thirdparty->email = $email;
					$thirdparty->phone = $phone;
					$thirdparty->phone_mobile = $phone;
					$thirdparty->ip = getUserRemoteIP();
					if ($birthdayTs !== null) {
						$thirdparty->birth = $birthdayTs;
					}
					$note = $langs->trans('DoliProspectFormPublicOriginNote', dol_print_date(dol_now(), 'dayhour', 'tzuser'), $thirdparty->ip);
					$thirdparty->note_private = ($thirdparty->note_private ? $thirdparty->note_private."\n\n" : '').$note;

					if ($assignedCommercialId > 0) {
						$thirdparty->commercial_id = $assignedCommercialId;
					}
					$resUpd = $thirdparty->update($thirdparty->id, $actorUser, 1, 0, 0);
					if ($resUpd < 0) {
						$errors[] = $thirdparty->error ? $thirdparty->error : implode(', ', $thirdparty->errors);
					} else {
						$runContactAndUpload($thirdparty);
					}
				}
			} else {
				$thirdparty = new Societe($db);
				$thirdparty->particulier = 1;
				$thirdparty->name = dolGetFirstLastname($firstname, $lastname);
				$thirdparty->client = Societe::PROSPECT;
				$thirdparty->fournisseur = 0;
				$thirdparty->typent_id = (int) dol_getIdFromCode($db, 'TE_PRIVATE', 'c_typent', 'code', 'id');
				if ($thirdparty->typent_id <= 0) {
					$thirdparty->typent_id = 0;
				}
				$thirdparty->address = $address;
				$thirdparty->zip = $zip;
				$thirdparty->town = $town;
				$thirdparty->country_id = $countryId;
				$thirdparty->state_id = 0;
				$thirdparty->email = $email;
				$thirdparty->phone = $phone;
				$thirdparty->phone_mobile = $phone;
				$thirdparty->commercial_id = $assignedCommercialId > 0 ? $assignedCommercialId : 0;
				$thirdparty->ip = getUserRemoteIP();
				$note = $langs->trans('DoliProspectFormPublicOriginNote', dol_print_date(dol_now(), 'dayhour', 'tzuser'), $thirdparty->ip);
				$thirdparty->note_private = ($thirdparty->note_private ? $thirdparty->note_private."\n\n" : '').$note;

				doliprospectform_publicform_assign_customer_code($thirdparty);

				$resCreate = $thirdparty->create($actorUser);
				if ($resCreate < 0) {
					$errors[] = $thirdparty->error ? $thirdparty->error : implode(', ', $thirdparty->errors);
				} else {
					if (!$runContactAndUpload($thirdparty)) {
						$thirdparty->delete($thirdparty->id, $actorUser, 0);
					}
				}
			}
		}
	}
}

$usePostValues = ($_SERVER['REQUEST_METHOD'] === 'POST' && GETPOST('action', 'aZ09') === 'submit');
if ($usePostValues) {
	$dispCivility = GETPOST('civility_code', 'aZ09');
	$dispFirstname = GETPOST('firstname', 'alphanohtml');
	$dispLastname = GETPOST('lastname', 'alphanohtml');
	$dispEmail = GETPOST('email', 'restricthtml');
	$dispPhone = GETPOST('phone', 'alphanohtml');
	$dispAddress = GETPOST('address', 'alphanohtml');
	$dispZip = GETPOST('zipcode', 'alphanohtml');
	$dispTown = GETPOST('town', 'alphanohtml');
	$dispCountryId = GETPOSTINT('country_id');
	$dispBirthday = GETPOST('birthday', 'alphanohtml');
} elseif ($linkedThirdparty) {
	list($dispFirstname, $dispLastname) = doliprospectform_publicform_split_display_name((string) $linkedThirdparty->name);
	$dispCivility = !empty($linkedThirdparty->civility_code) ? (string) $linkedThirdparty->civility_code : '';
	$dispEmail = (string) $linkedThirdparty->email;
	$dispPhone = (string) ($linkedThirdparty->phone ?: $linkedThirdparty->phone_mobile);
	$dispAddress = (string) $linkedThirdparty->address;
	$dispZip = (string) $linkedThirdparty->zip;
	$dispTown = (string) $linkedThirdparty->town;
	$dispCountryId = (int) $linkedThirdparty->country_id;
	$dispBirthday = (!empty($linkedThirdparty->birth)) ? dol_print_date($linkedThirdparty->birth, '%Y-%m-%d', 'tzserver') : '';
} else {
	$dispCivility = '';
	$dispFirstname = '';
	$dispLastname = '';
	$dispEmail = '';
	$dispPhone = '';
	$dispAddress = '';
	$dispZip = '';
	$dispTown = '';
	$dispCountryId = 0;
	$dispBirthday = '';
}

global $mysoc;
if ((int) $dispCountryId <= 0) {
	$dispCountryId = doliprospectform_publicform_get_main_company_country_id($db, $mysoc);
}

$title = doliprospectform_publicform_get_form_title($langs, 'individual');
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
print '<p class="dpf-hero-lead mb-0">'.doliprospectform_publicform_get_resolved_intro_html($db, $langs, $entity, $assignedCommercialId, 'individual').'</p>';
print '</div></div>';

$form = new Form($db);
$formcompany = new FormCompany($db);

print '<div class="dpf-card p-3 p-md-4">';
print '<form method="post" enctype="multipart/form-data" action="'.dol_escape_htmltag($_SERVER['PHP_SELF']).'" class="doliprospectform-public-form">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="submit">';
print '<input type="hidden" name="e" value="'.((int) $entity).'">';
print '<input type="hidden" name="t" value="'.dol_escape_htmltag($token).'">';

print '<h2 class="dpf-section-title"><i class="bi bi-person-badge me-2" aria-hidden="true"></i>'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSectionIdentity')).'</h2>';
print '<div class="row g-3 mb-4">';
print '<div class="col-12 col-md-6"><label class="dpf-label" for="civility_code">'.$langs->trans('Civility').' <span class="text-danger">*</span></label>';
print $formcompany->select_civility($dispCivility, 'civility_code', 'dpf-form-select w-100 dpf-civility-select', 0);
print '</div>';
print '<div class="col-12 col-md-6"><label class="dpf-label" for="birthday">'.$langs->trans('DateOfBirth').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="birthday" type="date" name="birthday" required value="'.dol_escape_htmltag($dispBirthday).'"></div>';
print '<div class="col-12 col-md-6"><label class="dpf-label" for="firstname">'.$langs->trans('Firstname').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="firstname" name="firstname" autocomplete="given-name" required value="'.dol_escape_htmltag($dispFirstname).'"></div>';
print '<div class="col-12 col-md-6"><label class="dpf-label" for="lastname">'.$langs->trans('Lastname').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="lastname" name="lastname" autocomplete="family-name" required value="'.dol_escape_htmltag($dispLastname).'"></div>';
print '<div class="col-12 col-md-6"><label class="dpf-label" for="email">'.$langs->trans('Email').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="email" type="email" name="email" autocomplete="email" required value="'.dol_escape_htmltag($dispEmail).'"></div>';
print '<div class="col-12 col-md-6"><label class="dpf-label" for="phone">'.$langs->trans('Phone').'</label>';
print '<input class="dpf-form-control" id="phone" type="tel" name="phone" autocomplete="tel" value="'.dol_escape_htmltag($dispPhone).'"></div>';
print '</div>';

print '<h2 class="dpf-section-title"><i class="bi bi-geo-alt me-2" aria-hidden="true"></i>'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSectionAddress')).'</h2>';
print '<div class="row g-3 mb-4">';
print '<div class="col-12"><label class="dpf-label" for="address">'.$langs->trans('Address').' <span class="text-danger">*</span></label>';
print '<textarea class="dpf-form-control" id="address" name="address" rows="3" autocomplete="street-address" required>'.dol_escape_htmltag($dispAddress).'</textarea></div>';
print '<div class="col-12 col-md-4"><label class="dpf-label" for="zipcode">'.$langs->trans('Zip').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="zipcode" name="zipcode" autocomplete="postal-code" required value="'.dol_escape_htmltag($dispZip).'"></div>';
print '<div class="col-12 col-md-4"><label class="dpf-label" for="town">'.$langs->trans('Town').' <span class="text-danger">*</span></label>';
print '<input class="dpf-form-control" id="town" name="town" autocomplete="address-level2" required value="'.dol_escape_htmltag($dispTown).'"></div>';
print '<div class="col-12 col-md-4"><label class="dpf-label" for="selectcountry_id">'.$langs->trans('Country').' <span class="text-danger">*</span></label>';
print $form->select_country($dispCountryId, 'country_id', '', 0, 'dpf-form-select w-100', '', 1, 0, 0, array(), 0, 1);
print '</div>';
print '</div>';

print '<h2 class="dpf-section-title"><i class="bi bi-file-earmark-pdf me-2" aria-hidden="true"></i>'.dol_escape_htmltag(doliprospectform_publicform_get_doc_section_title($langs, 'individual')).'</h2>';
print '<p class="dpf-hint mb-2">'.dol_escape_htmltag(doliprospectform_publicform_get_doc_section_hint($langs, 'individual')).'</p>';
print '<div class="dpf-file-zone mb-2">';
print '<label class="dpf-label d-flex align-items-center gap-2" for="dpf_pdf_first"><i class="bi bi-cloud-arrow-up text-primary" aria-hidden="true"></i> '.$langs->trans('DoliProspectFormPublicDocumentsPdf').' <span class="text-danger">*</span></label>';
print '<div id="dpf-pdf-wrap" class="dpf-pdf-inputs">';
print '<div class="dpf-file-row mb-2">';
print '<input class="dpf-form-control" id="dpf_pdf_first" type="file" name="doliprospectform_pdfs[]" multiple accept="application/pdf,.pdf">';
print '</div></div></div>';
print '<button type="button" class="dpf-btn-add-pdf mb-4" id="dpf_add_pdf_input">'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicAddPdfField')).'</button>';
print '<script>(function(){var b=document.getElementById("dpf_add_pdf_input");if(!b)return;b.addEventListener("click",function(){var w=document.getElementById("dpf-pdf-wrap");if(!w)return;var d=document.createElement("div");d.className="dpf-file-row mb-2";var i=document.createElement("input");i.type="file";i.name="doliprospectform_pdfs[]";i.accept="application/pdf,.pdf";i.className="dpf-form-control";d.appendChild(i);w.appendChild(d);});})();</script>';

doliprospectform_publicform_print_captcha_block($captchaobj, doliprospectform_publicform_get_captcha_reload_url($entity, $token), false);

print '<button type="submit" class="dpf-btn-submit"><i class="bi bi-send-fill" aria-hidden="true"></i> '.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSubmit')).'</button>';

print '</form>';
print '</div>';
print '</div>';

llxFooter();
$db->close();
