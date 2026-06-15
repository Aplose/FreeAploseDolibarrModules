<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    doliprospectform/admin/setup_public_form.php
 * \ingroup doliprospectform
 * \brief   Configure texts displayed on public forms (individual / professional).
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('custom/doliprospectform/lib/doliprospectform_publicform.lib.php');
dol_include_once('custom/doliprospectform/lib/doliprospectform.lib.php');

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

$langs->loadLangs(array('admin', 'doliprospectform@doliprospectform'));
$hookmanager->initHooks(array('doliprospectformsetup', 'globalsetup'));

$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

if (!$user->admin) {
	accessforbidden();
}

$tab = GETPOST('tab', 'aZ09');
if ($tab !== 'professional' && $tab !== 'individual' && $tab !== 'hub') {
	$tab = 'individual';
}

if (isModEnabled('doliprospectform')) {
	require_once __DIR__.'/../core/modules/modDoliProspectForm.class.php';
	if (class_exists('modDoliProspectForm')) {
		$tmpDoliProspectFormMod = new modDoliProspectForm($db);
		$tmpDoliProspectFormMod->runEmailTemplateMaintenance();
	}
	doliprospectform_publicform_ensure_default_text_consts($db);
}

if (!class_exists('FormSetup')) {
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
}

$formSetup = new FormSetup($db);
$formSetup->formHiddenInputs['tab'] = $tab;

if ($tab === 'hub') {
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_HUB_TITLE')->setAsString();
	$item->helpText = $langs->trans('DoliProspectFormSetupFormTitleHelp');
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_HUB_INTRO')->setAsTextarea();
	$item->helpText = $langs->trans('DoliProspectFormSetupFormIntroHelp');
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_HUB_CONSULTANT_BLOCK_TITLE')->setAsString();
	$item->helpText = $langs->trans('DOLIPROSPECTFORM_FORM_HUB_CONSULTANT_BLOCK_TITLETooltip');
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_HUB_CONSULTANT_BLOCK_SUBTITLE')->setAsTextarea();
	$item->helpText = $langs->trans('DOLIPROSPECTFORM_FORM_HUB_CONSULTANT_BLOCK_SUBTITLETooltip');
} elseif ($tab === 'professional') {
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_PRO_TITLE')->setAsString();
	$item->helpText = $langs->trans('DoliProspectFormSetupFormTitleHelp');
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_PRO_INTRO')->setAsTextarea();
	$item->helpText = $langs->trans('DoliProspectFormSetupFormIntroHelp');
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_PRO_DOC_TITLE')->setAsString();
	$item->helpText = $langs->trans('DoliProspectFormSetupFormDocTitleHelp');
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_PRO_DOC_HINT')->setAsTextarea();
	$item->helpText = $langs->trans('DoliProspectFormSetupFormDocHintHelp');
} else {
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_INDIV_TITLE')->setAsString();
	$item->helpText = $langs->trans('DoliProspectFormSetupFormTitleHelp');
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_INDIV_INTRO')->setAsTextarea();
	$item->helpText = $langs->trans('DoliProspectFormSetupFormIntroHelp');
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_INDIV_DOC_TITLE')->setAsString();
	$item->helpText = $langs->trans('DoliProspectFormSetupFormDocTitleHelp');
	$item = $formSetup->newItem('DOLIPROSPECTFORM_FORM_INDIV_DOC_HINT')->setAsTextarea();
	$item->helpText = $langs->trans('DoliProspectFormSetupFormDocHintHelp');
}

if (versioncompare(explode('.', DOL_VERSION), array(15)) < 0 && $action == 'update' && !empty($user->admin)) {
	$formSetup->saveConfFromPost();
}

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

$action = 'edit';

$title = 'DoliProspectFormSetupPublicFormTexts';
llxHeader('', $langs->trans($title), '', '', 0, 0, doliprospectform_backoffice_llx_js(), doliprospectform_backoffice_llx_css(), '', 'mod-doliprospectform page-admin');

$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.img_picto($langs->trans('BackToModuleList'), 'back', 'class="pictofixedwidth"').'<span class="hideonsmartphone">'.$langs->trans('BackToModuleList').'</span></a>';

print load_fiche_titre($langs->trans($title), $linkback, 'title_setup');

$head = doliprospectformAdminPrepareHead();
$activeTab = 'public_form_individual';
if ($tab === 'professional') {
	$activeTab = 'public_form_professional';
} elseif ($tab === 'hub') {
	$activeTab = 'public_form_hub';
}
print dol_get_fiche_head($head, $activeTab, $langs->trans('DoliProspectFormSetup'), -1, 'doliprospectform@doliprospectform');

print '<div class="container-fluid px-2 px-md-3 pb-4 doliprospectform-bo">';
if ($tab === 'hub') {
	print '<p class="lead text-body-secondary mb-4">'.$langs->trans('DoliProspectFormSetupHubTextsPage').'</p>';
} else {
	print '<p class="lead text-body-secondary mb-4">'.$langs->trans('DoliProspectFormSetupPublicFormTextsPage').'</p>';
}

if (!empty($formSetup->items)) {
	print '<div class="card border shadow-sm mb-4"><div class="card-body p-0">';
	print $formSetup->generateOutput(true);
	print '</div></div>';
}

print '</div>';

print dol_get_fiche_end();

llxFooter();
$db->close();
