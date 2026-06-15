<?php
/* Copyright (C) 2004-2017  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    doliprospectform/admin/setup.php
 * \ingroup doliprospectform
 * \brief   DoliProspectForm setup page.
 */

$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include str_replace("..", "", $_SERVER["CONTEXT_DOCUMENT_ROOT"])."/main.inc.php";
}
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
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

require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
dol_include_once('custom/doliprospectform/lib/doliprospectform.lib.php');
dol_include_once('custom/doliprospectform/lib/doliprospectform_publicform.lib.php');

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

$langs->loadLangs(array("admin", "doliprospectform@doliprospectform"));
$hookmanager->initHooks(array('doliprospectformsetup', 'globalsetup'));

$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$modulepart = GETPOST('modulepart', 'aZ09');

if (!$user->admin) {
	accessforbidden();
}

if (isModEnabled('doliprospectform')) {
	require_once __DIR__.'/../core/modules/modDoliProspectForm.class.php';
	if (class_exists('modDoliProspectForm')) {
		$tmpDoliProspectFormMod = new modDoliProspectForm($db);
		$tmpDoliProspectFormMod->runEmailTemplateMaintenance();
	}
}

if (!class_exists('FormSetup')) {
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
}
$formSetup = new FormSetup($db);

doliprospectform_publicform_ensure_public_form_enable_consts($db);

$formSetup->newItem('DOLIPROSPECTFORM_TOKEN_VALIDITY_DAYS')->setAsNumber(1, 3650, 1);
$itemTechUser = $formSetup->newItem('DOLIPROSPECTFORM_PUBLIC_DEFAULT_USER');
$itemTechUser->defaultFieldValue = '0';
$itemTechUser->setAsSelect(doliprospectform_get_public_default_user_select_options($db, $langs));
$item = $formSetup->newItem('DOLIPROSPECTFORM_PUBLIC_FORM_INDIVIDUAL');
$item->defaultFieldValue = '1';
$item->setAsYesNo();
$item = $formSetup->newItem('DOLIPROSPECTFORM_PUBLIC_FORM_PROFESSIONAL');
$item->defaultFieldValue = '1';
$item->setAsYesNo();

$itemTpl = $formSetup->newItem('DOLIPROSPECTFORM_SUBMISSION_NOTIFY_TEMPLATE_ID');
$itemTpl->setAsEmailTemplate('user');
$itemTpl->helpText = $langs->trans('DOLIPROSPECTFORM_SUBMISSION_NOTIFY_TEMPLATE_IDTooltip');

$itemFb = $formSetup->newItem('DOLIPROSPECTFORM_SUBMISSION_NOTIFY_FALLBACK_EMAIL');
$itemFb->setAsEmail();
$itemFb->helpText = $langs->trans('DOLIPROSPECTFORM_SUBMISSION_NOTIFY_FALLBACK_EMAILTooltip');

$itemInvTpl = $formSetup->newItem('DOLIPROSPECTFORM_INVITATION_EMAIL_TEMPLATE_ID');
$itemInvTpl->setAsEmailTemplate('thirdparty');
$itemInvTpl->helpText = $langs->trans('DOLIPROSPECTFORM_INVITATION_EMAIL_TEMPLATE_IDTooltip');

if (versioncompare(explode('.', DOL_VERSION), array(15)) < 0 && $action == 'update' && !empty($user->admin)) {
	$formSetup->saveConfFromPost();
}

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

$action = 'edit';

/*
 * View
 */

$title = "DoliProspectFormSetup";
llxHeader('', $langs->trans($title), '', '', 0, 0, doliprospectform_backoffice_llx_js(), doliprospectform_backoffice_llx_css(), '', 'mod-doliprospectform page-admin');

$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.img_picto($langs->trans("BackToModuleList"), 'back', 'class="pictofixedwidth"').'<span class="hideonsmartphone">'.$langs->trans("BackToModuleList").'</span></a>';

print load_fiche_titre($langs->trans($title), $linkback, 'title_setup');

$head = doliprospectformAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($title), -1, "doliprospectform@doliprospectform");

print '<div class="container-fluid px-2 px-md-3 pb-4 doliprospectform-bo">';
print '<p class="lead text-body-secondary mb-4">'.$langs->trans("DoliProspectFormSetupPage").'</p>';

if (!empty($formSetup->items)) {
	print '<div class="card border shadow-sm mb-4"><div class="card-body p-0">';
	print $formSetup->generateOutput(true);
	print '</div></div>';
}

$anonymousHubUrl = doliprospectform_publicform_url_hub($db, (int) $conf->entity, 0, 0, true);
print '<div class="card border shadow-sm mb-4">';
print '<div class="card-header fw-semibold py-3">'.dol_escape_htmltag($langs->trans('DoliProspectFormAnonymousHubUrlTitle')).'</div>';
print '<div class="card-body">';
print '<p class="text-body-secondary small mb-3">'.$langs->trans('DoliProspectFormAnonymousHubUrlHelp', (int) $conf->entity).'</p>';
if ($anonymousHubUrl !== '') {
	print '<div class="table-responsive"><code class="d-block text-break p-2 bg-body-secondary rounded border"><a href="'.dol_escape_htmltag($anonymousHubUrl).'" target="_blank" rel="noopener noreferrer" title="'.dol_escape_htmltag($langs->trans('NewWindow')).'">'.dol_escape_htmltag($anonymousHubUrl).'</a></code></div>';
} else {
	print '<p class="text-warning mb-0">'.$langs->trans('DoliProspectFormAnonymousHubUrlUnavailable').'</p>';
}
print '</div></div>';

print '<div class="alert alert-info" role="status">';
print '<strong class="d-block mb-2">'.$langs->trans('DoliProspectFormSubstitutionHelpTitle').'</strong>';
print '<div class="small">'.$langs->trans('DoliProspectFormSubstitutionHelpBody').'</div>';
print '</div>';

print '</div>';

print dol_get_fiche_end();

llxFooter();
$db->close();
