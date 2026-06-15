<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 * Copyright (C) 2025       Frédéric France         <frederic.france@free.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    doliprospectform/lib/doliprospectform.lib.php
 * \ingroup doliprospectform
 * \brief   Library files with common functions for DoliProspectForm
 */

/**
 * Prepare admin pages header
 *
 * @return array<array{string,string,string}>
 */
function doliprospectformAdminPrepareHead()
{
	global $langs, $conf;

	// global $db;
	// $extrafields = new ExtraFields($db);
	// $extrafields->fetch_name_optionals_label('myobject');

	$langs->load("doliprospectform@doliprospectform");

	$h = 0;
	$head = array();

	$head[$h][0] = dolBuildUrl(dol_buildpath("/doliprospectform/admin/setup.php", 1));
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dolBuildUrl(dol_buildpath("/doliprospectform/admin/setup_public_form.php", 1)).'?tab=hub';
	$head[$h][1] = $langs->trans("DoliProspectFormSetupTabLandingTitle");
	$head[$h][2] = 'public_form_hub';
	$h++;

	$head[$h][0] = dolBuildUrl(dol_buildpath("/doliprospectform/admin/setup_public_form.php", 1)).'?tab=individual';
	$head[$h][1] = $langs->trans("DoliProspectFormSetupTabIndividual");
	$head[$h][2] = 'public_form_individual';
	$h++;

	$head[$h][0] = dolBuildUrl(dol_buildpath("/doliprospectform/admin/setup_public_form.php", 1)).'?tab=professional';
	$head[$h][1] = $langs->trans("DoliProspectFormSetupTabProfessional");
	$head[$h][2] = 'public_form_professional';
	$h++;

	/*
	$head[$h][0] = dolBuildUrl(dol_buildpath("/doliprospectform/admin/myobject_extrafields.php", 1));
	$head[$h][1] = $langs->trans("ExtraFields");
	$nbExtrafields = (isset($extrafields->attributes['myobject']['label']) && is_countable($extrafields->attributes['myobject']['label'])) ? count($extrafields->attributes['myobject']['label']) : 0;
	if ($nbExtrafields > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">' . $nbExtrafields . '</span>';
	}
	$head[$h][2] = 'myobject_extrafields';
	$h++;

	$head[$h][0] = dolBuildUrl(dol_buildpath("/doliprospectform/admin/myobjectline_extrafields.php", 1));
	$head[$h][1] = $langs->trans("ExtraFieldsLines");
	$nbExtrafields = (isset($extrafields->attributes['myobjectline']['label']) && is_countable($extrafields->attributes['myobjectline']['label'])) ? count($extrafields->attributes['myobject']['label']) : 0;
	if ($nbExtrafields > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">' . $nbExtrafields . '</span>';
	}
	$head[$h][2] = 'myobject_extrafieldsline';
	$h++;
	*/

	$head[$h][0] = dolBuildUrl(dol_buildpath("/doliprospectform/admin/about.php", 1));
	$head[$h][1] = $langs->trans("DoliProspectFormDocumentationTab");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@doliprospectform:/doliprospectform/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@doliprospectform:/doliprospectform/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'doliprospectform@doliprospectform');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'doliprospectform@doliprospectform', 'remove');

	return $head;
}

/**
 * Resolve localized About tab HTML file (same candidate order as Dolibarr README for external modules).
 *
 * @param Translate $langs Language object
 * @return string             Absolute filesystem path if a file exists, empty string otherwise
 */
function doliprospectform_admin_find_localized_about_html($langs)
{
	$candidates = array();
	$add = function ($name) use (&$candidates) {
		if ($name !== '' && !in_array($name, $candidates, true)) {
			$candidates[] = $name;
		}
	};

	$langdefault = (string) $langs->defaultlang;
	if ($langdefault !== '') {
		$add('about-'.$langdefault.'.html');
		$tmp = explode('_', $langdefault);
		if (!empty($tmp[0])) {
			$add('about-'.$tmp[0].'.html');
		}
	}
	$add('about-en_US.html');
	$add('about-fr_FR.html');
	$add('about.html');

	foreach ($candidates as $fname) {
		$path = dol_buildpath('/doliprospectform/doc/'.$fname, 0);
		if ($path && is_readable($path)) {
			return $path;
		}
	}

	return '';
}

/**
 * Valid rowid in llx_c_email_templates for presend on third party card (from DOLIPROSPECTFORM_INVITATION_EMAIL_TEMPLATE_ID).
 *
 * @param DoliDB $db    Database handler
 * @param User   $user Current user (private templates)
 * @return int          Template rowid or 0 if unset / not applicable
 */
function doliprospectform_get_invitation_presend_template_id(DoliDB $db, User $user)
{
	$id = getDolGlobalInt('DOLIPROSPECTFORM_INVITATION_EMAIL_TEMPLATE_ID', 0);
	if ($id <= 0) {
		return 0;
	}
	$sql = "SELECT rowid FROM ".$db->prefix()."c_email_templates";
	$sql .= " WHERE rowid = ".((int) $id);
	$sql .= " AND entity IN (".getEntity('c_email_templates').")";
	$sql .= " AND (type_template IN ('thirdparty', 'thirdparty_send') OR type_template = 'all')";
	$sql .= " AND active = 1";
	$sql .= " AND (private = 0 OR fk_user = ".((int) $user->id).")";
	$sql .= " ".$db->plimit(1);
	$resql = $db->query($sql);
	if (!$resql) {
		return 0;
	}
	$obj = $db->fetch_object($resql);
	$db->free($resql);
	return ($obj && (int) $obj->rowid > 0) ? (int) $obj->rowid : 0;
}

/**
 * Build select options for DOLIPROSPECTFORM_PUBLIC_DEFAULT_USER (technical user for public forms).
 * Key "0" keeps legacy behaviour: first active user of the entity when the constant is not set to a specific id.
 *
 * @param DoliDB    $db    Database handler
 * @param Translate $langs Output language
 * @return array<string,string> option value => label (firstname/lastname per MAIN_FIRSTNAME_NAME_POSITION)
 */
function doliprospectform_get_public_default_user_select_options(DoliDB $db, Translate $langs)
{
	require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';

	$options = array('0' => $langs->trans('DoliProspectFormTechnicalUserAutomatic'));

	$sql = "SELECT u.rowid, u.firstname, u.lastname, u.login FROM ".$db->prefix()."user AS u";
	$sql .= " WHERE u.entity IN (".getEntity('user').")";
	$sql .= " AND u.statut = ".((int) User::STATUS_ENABLED);
	if (!getDolGlobalString('MAIN_FIRSTNAME_NAME_POSITION')) {
		$sql .= " ORDER BY u.firstname ASC, u.lastname ASC, u.login ASC";
	} else {
		$sql .= " ORDER BY u.lastname ASC, u.firstname ASC, u.login ASC";
	}

	$resql = $db->query($sql);
	if (!$resql) {
		return $options;
	}

	$userstatic = new User($db);
	$fullNameMode = getDolGlobalString('MAIN_FIRSTNAME_NAME_POSITION') ? 0 : 1;

	while ($obj = $db->fetch_object($resql)) {
		$userstatic->id = (int) $obj->rowid;
		$userstatic->firstname = $obj->firstname;
		$userstatic->lastname = $obj->lastname;
		$label = $userstatic->getFullName($langs, $fullNameMode, -1, 0);
		if ($label === '') {
			$label = $obj->login;
		}
		$options[(string) ((int) $obj->rowid)] = $label;
	}
	$db->free($resql);

	return $options;
}

/**
 * Horizontal tabs for module main area (Dashboard / Invitation / Submissions list).
 *
 * @param string $active Tab id: dashboard|invitation|submissions
 * @return array<int,array{0:string,1:string,2:string}>
 */
function doliprospectform_main_area_prepare_head($active)
{
	global $langs;

	$langs->load('doliprospectform@doliprospectform');

	$h = 0;
	$head = array();

	$head[$h][0] = dolBuildUrl(dol_buildpath('/doliprospectform/doliprospectformindex.php', 1));
	$head[$h][1] = $langs->trans('DoliProspectFormMenuDashboard');
	$head[$h][2] = 'dashboard';
	$h++;

	$head[$h][0] = dolBuildUrl(dol_buildpath('/doliprospectform/invitation.php', 1));
	$head[$h][1] = $langs->trans('DoliProspectFormMenuInvitation');
	$head[$h][2] = 'invitation';
	$h++;

	$head[$h][0] = dolBuildUrl(dol_buildpath('/doliprospectform/submissions_list.php', 1));
	$head[$h][1] = $langs->trans('DoliProspectFormMenuSubmissions');
	$head[$h][2] = 'submissions';
	$h++;

	return $head;
}

/**
 * CSS URLs for module backoffice pages (Bootstrap + module tweaks).
 *
 * @return array<int,string>
 */
function doliprospectform_backoffice_llx_css()
{
	return array(
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
		'/custom/doliprospectform/css/doliprospectform_backoffice.css',
	);
}

/**
 * JS entries for llxHeader on module backoffice / admin pages (Bootstrap bundle).
 *
 * @return array<int,string>
 */
function doliprospectform_backoffice_llx_js()
{
	return array(
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
	);
}

/**
 * SQL WHERE fragment for public submissions visible to the user (entity + commercial filter).
 *
 * @param User $user Current user
 * @return string SQL starting with " AND ..." or empty for admin (no commercial filter)
 */
function doliprospectform_submissions_sql_where_user(DoliDB $db, User $user)
{
	$sql = " AND s.entity IN (".getEntity('societe').")";
	if (empty($user->admin)) {
		$sql .= " AND s.fk_user_commercial = ".((int) $user->id);
	}
	return $sql;
}

/**
 * Print substitution tags table and resolved public URLs (current user, no preset third party).
 *
 * @param DoliDB    $db    Database handler
 * @param Translate $langs Lang
 * @param User      $user  User
 * @param Conf      $conf  Conf
 * @return void
 */
function doliprospectform_print_substitution_urls_table(DoliDB $db, Translate $langs, User $user, Conf $conf)
{
	if (!function_exists('doliprospectform_publicform_url_hub')) {
		dol_include_once('custom/doliprospectform/lib/doliprospectform_publicform.lib.php');
	}

	$entityCur = (int) $conf->entity;
	$commercialId = (int) $user->id;
	$enableInd = doliprospectform_publicform_is_public_individual_enabled();
	$enablePro = doliprospectform_publicform_is_public_professional_enabled();
	$urlHub = ($enableInd || $enablePro) ? doliprospectform_publicform_url_hub($db, $entityCur, $commercialId, 0) : '';
	$urlInd = ($commercialId > 0 && $enableInd) ? doliprospectform_publicform_url_individual($db, $entityCur, $commercialId, 0) : '';
	$urlPro = ($commercialId > 0 && $enablePro) ? doliprospectform_publicform_url_professional($db, $entityCur, $commercialId, 0) : '';
	$salesRepPreview = doliprospectform_publicform_replace_sales_rep_placeholder($db, $langs, $entityCur, $commercialId, '__DOLIPROSPECTFORM_PUBLIC_SALES_REP__');

	print '<div class="doliprospectform-bo mb-4">';
	print '<div class="table-responsive border rounded shadow-sm">';
	print '<table class="table table-striped table-sm align-middle mb-0">';
	print '<thead class="table-light"><tr><th colspan="2" class="py-2">'.dol_escape_htmltag($langs->trans('DoliProspectFormHomeSubstitutionBlockTitle')).'</th></tr></thead>';
	print '<tbody>';
	print '<tr><td colspan="2" class="text-body-secondary small">'.$langs->trans('DoliProspectFormHomeSubstitutionBlockIntro', $entityCur).'</td></tr>';
	print '<tr class="table-secondary"><th class="text-nowrap small">'.dol_escape_htmltag($langs->trans('DoliProspectFormHomeSubstitutionTag')).'</th><th class="small">'.dol_escape_htmltag($langs->trans('DoliProspectFormHomeSubstitutionUrl')).'</th></tr>';

	$printSubRow = function ($tagsLabel, $url) use ($langs) {
		print '<tr><td class="wordbreak small"><code class="text-secondary">'.dol_escape_htmltag($tagsLabel).'</code></td><td class="wordbreak small">';
		if ($url !== '') {
			print '<a href="'.dol_escape_htmltag($url).'" target="_blank" rel="noopener noreferrer">'.dol_escape_htmltag($url).'</a>';
		} else {
			print '<span class="text-body-secondary">'.$langs->trans('DoliProspectFormHomeSubstitutionDisabled').'</span>';
		}
		print '</td></tr>';
	};

	$printSubRow('__DOLIPROSPECTFORM_PUBLIC_LINK_HUB__', (string) $urlHub);
	$printSubRow('__DOLIPROSPECTFORM_PUBLIC_LINK_PARTICULIER__ / __DOLIPROSPECTFORM_PUBLIC_LINK_INDIVIDUAL__', (string) $urlInd);
	$printSubRow('__DOLIPROSPECTFORM_PUBLIC_LINK_PROFESSIONNEL__ / __DOLIPROSPECTFORM_PUBLIC_LINK_PROFESSIONAL__', (string) $urlPro);
	print '<tr><td class="wordbreak small"><code class="text-secondary">__DOLIPROSPECTFORM_PUBLIC_SALES_REP__</code></td><td class="wordbreak small">'.dol_escape_htmltag($salesRepPreview).'</td></tr>';

	print '</tbody></table></div></div>';
}

/**
 * Print simple dashboard statistics for public form submissions.
 *
 * @param DoliDB    $db    Database handler
 * @param Translate $langs Lang
 * @param User      $user  User
 * @return void
 */
function doliprospectform_print_dashboard_stats(DoliDB $db, Translate $langs, User $user)
{
	$w = doliprospectform_submissions_sql_where_user($db, $user);

	$sqlBase = " FROM ".$db->prefix()."doliprospectform_publicsubmission as s";
	$sqlBase .= " INNER JOIN ".$db->prefix()."societe as soc ON soc.rowid = s.fk_soc";
	$sqlBase .= " WHERE soc.entity IN (".getEntity('societe').")".$w;

	$total = 0;
	$sql = "SELECT COUNT(s.rowid) as nb".$sqlBase;
	$resql = $db->query($sql);
	if ($resql && ($obj = $db->fetch_object($resql))) {
		$total = (int) $obj->nb;
		$db->free($resql);
	}

	$last30 = 0;
	$sql = "SELECT COUNT(s.rowid) as nb".$sqlBase;
	$sql .= " AND s.date_submission >= '".$db->idate(dol_now() - (30 * 86400))."'";
	$resql = $db->query($sql);
	if ($resql && ($obj = $db->fetch_object($resql))) {
		$last30 = (int) $obj->nb;
		$db->free($resql);
	}

	$nbInd = 0;
	$nbPro = 0;
	$sql = "SELECT s.form_type, COUNT(s.rowid) as nb".$sqlBase." GROUP BY s.form_type";
	$resql = $db->query($sql);
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			if ($obj->form_type === 'individual') {
				$nbInd = (int) $obj->nb;
			} elseif ($obj->form_type === 'professional') {
				$nbPro = (int) $obj->nb;
			}
		}
		$db->free($resql);
	}

	print '<div class="doliprospectform-bo mb-4">';
	print '<div class="row g-3">';
	print '<div class="col-12 col-lg-6 col-xl-5">';
	print '<div class="card h-100 border shadow-sm">';
	print '<div class="card-header py-2 fw-semibold">'.dol_escape_htmltag($langs->trans('DoliProspectFormDashboardStatsTitle')).'</div>';
	print '<div class="card-body p-0">';
	print '<table class="table table-sm table-striped mb-0">';
	print '<tbody>';
	print '<tr><td>'.dol_escape_htmltag($langs->trans('DoliProspectFormDashboardStatsTotal')).'</td><td class="text-end"><span class="badge text-bg-primary rounded-pill">'.$total.'</span></td></tr>';
	print '<tr><td>'.dol_escape_htmltag($langs->trans('DoliProspectFormDashboardStatsLast30Days')).'</td><td class="text-end"><span class="badge text-bg-secondary rounded-pill">'.$last30.'</span></td></tr>';
	print '<tr><td>'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSubmissionForm_individual')).'</td><td class="text-end">'.$nbInd.'</td></tr>';
	print '<tr><td>'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSubmissionForm_professional')).'</td><td class="text-end">'.$nbPro.'</td></tr>';
	print '</tbody></table></div></div></div></div></div>';
}

/**
 * Print the public submissions list (same columns as former home list).
 *
 * @param DoliDB    $db    Database handler
 * @param Translate $langs Lang
 * @param User      $user  User
 * @return void
 */
function doliprospectform_print_submissions_list(DoliDB $db, Translate $langs, User $user)
{
	require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

	$sql = "SELECT s.rowid, s.ref, s.date_submission, s.form_type, s.nb_documents, s.fk_soc, s.fk_contact, s.fk_user_commercial,";
	$sql .= " soc.nom as soc_nom, soc.email as soc_email, soc.phone as soc_phone, soc.phone_mobile as soc_phone_mobile";
	$sql .= " FROM ".$db->prefix()."doliprospectform_publicsubmission as s";
	$sql .= " INNER JOIN ".$db->prefix()."societe as soc ON soc.rowid = s.fk_soc";
	$sql .= " WHERE soc.entity IN (".getEntity('societe').")";
	$sql .= doliprospectform_submissions_sql_where_user($db, $user);
	$sql .= " ORDER BY s.date_submission DESC, s.rowid DESC";
	$sql .= $db->plimit(200, 0);

	$resql = $db->query($sql);
	if (!$resql) {
		dol_print_error($db);
		return;
	}

	$num = $db->num_rows($resql);
	print '<div class="doliprospectform-bo table-responsive border rounded shadow-sm">';
	print '<table class="table table-hover table-striped table-sm align-middle mb-0">';
	print '<thead class="table-light">';
	print '<tr>';
	print '<th class="col-ref text-nowrap">'.dol_escape_htmltag($langs->trans('Ref')).'</th>';
	print '<th class="text-nowrap">'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSubmissionDate')).'</th>';
	print '<th>'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSubmissionFormType')).'</th>';
	print '<th>'.dol_escape_htmltag($langs->trans('ThirdParty')).'</th>';
	print '<th class="text-nowrap">'.dol_escape_htmltag($langs->trans('Phone')).'</th>';
	print '<th>'.dol_escape_htmltag($langs->trans('Email')).'</th>';
	print '<th class="text-center text-nowrap">'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSubmissionNbDocuments')).'</th>';
	print '<th class="text-end col-actions">'.dol_escape_htmltag($langs->trans('DoliProspectFormPublicSubmissionDocuments')).'</th>';
	print '</tr>';
	print '</thead>';
	print '<tbody>';

	$socstatic = new Societe($db);

	if ($num === 0) {
		print '<tr><td colspan="8" class="text-body-secondary text-center py-4">'.$langs->trans('DoliProspectFormPublicSubmissionListEmpty').'</td></tr>';
	} else {
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($resql);
			$phoneDisp = '';
			if (!empty($obj->soc_phone)) {
				$phoneDisp = $obj->soc_phone;
			} elseif (!empty($obj->soc_phone_mobile)) {
				$phoneDisp = $obj->soc_phone_mobile;
			}

			print '<tr>';
			print '<td class="text-break small"><code>'.dol_escape_htmltag($obj->ref).'</code></td>';
			print '<td class="text-nowrap small">'.dol_print_date($db->jdate($obj->date_submission), 'dayhour', 'tzuser').'</td>';
			$formLabelKey = 'DoliProspectFormPublicSubmissionForm_'.$obj->form_type;
			$formLabel = $langs->trans($formLabelKey);
			if ($formLabel === $formLabelKey) {
				$formLabel = dol_escape_htmltag($obj->form_type);
			}
			print '<td class="small">'.dol_escape_htmltag($formLabel).'</td>';

			print '<td class="text-break" style="max-width:14rem;">';
			$socstatic->id = (int) $obj->fk_soc;
			$socstatic->name = $obj->soc_nom;
			if ($socstatic->fetch($socstatic->id) <= 0) {
				print dol_escape_htmltag($obj->soc_nom);
			} elseif ($user->hasRight('societe', 'client', 'voir')) {
				print $socstatic->getNomUrl(1);
			} else {
				print dol_escape_htmltag($obj->soc_nom);
			}
			if (!empty($obj->fk_contact) && $user->hasRight('societe', 'contact', 'lire')) {
				$contactUrl = DOL_URL_ROOT.'/contact/card.php?id='.((int) $obj->fk_contact);
				print ' <span class="text-body-secondary small">('.dol_escape_htmltag($langs->trans('Contact')).': <a href="'.dol_escape_htmltag($contactUrl).'">#'.((int) $obj->fk_contact).'</a>)</span>';
			}
			print '</td>';

			print '<td class="text-nowrap small">';
			if ($phoneDisp !== '' && $user->hasRight('societe', 'client', 'voir')) {
				print dol_print_phone($phoneDisp, '', 0, 0, '', '&nbsp;', 'phone');
			} elseif ($phoneDisp !== '') {
				print dol_escape_htmltag($phoneDisp);
			}
			print '</td>';

			print '<td class="text-break small" style="max-width:12rem;">';
			if (!empty($obj->soc_email)) {
				if ($user->hasRight('societe', 'client', 'voir')) {
					print dol_print_email($obj->soc_email, 0, 0, 0, 0, 1);
				} else {
					print dol_escape_htmltag($obj->soc_email);
				}
			}
			print '</td>';

			print '<td class="text-center">'.((int) $obj->nb_documents).'</td>';

			print '<td class="text-end">';
			if ($user->hasRight('societe', 'client', 'voir')) {
				$docUrl = DOL_URL_ROOT.'/societe/document.php?socid='.((int) $obj->fk_soc);
				print '<a class="btn btn-sm btn-outline-primary" href="'.dol_escape_htmltag($docUrl).'">'.$langs->trans('Documents').'</a>';
			}
			print '</td>';
			print '</tr>';
			$i++;
		}
	}
	print '</tbody></table></div>';
	$db->free($resql);
}
