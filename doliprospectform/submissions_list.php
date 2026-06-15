<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       doliprospectform/submissions_list.php
 * \ingroup    doliprospectform
 * \brief      List of completed public form submissions for the current user
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
 */

dol_include_once('custom/doliprospectform/lib/doliprospectform.lib.php');

$langs->loadLangs(array('doliprospectform@doliprospectform', 'companies'));

if (!isModEnabled('doliprospectform')) {
	accessforbidden('Module not enabled');
}
if (!empty($user->socid)) {
	accessforbidden();
}

llxHeader('', $langs->trans('DoliProspectFormMenuSubmissions'), '', '', 0, 0, doliprospectform_backoffice_llx_js(), doliprospectform_backoffice_llx_css(), '', 'mod-doliprospectform page-submissions');

$head = doliprospectform_main_area_prepare_head('submissions');
print dol_get_fiche_head($head, 'submissions', $langs->trans('DoliProspectFormArea'), 0, 'doliprospectform@doliprospectform');

print '<div class="container-fluid px-2 px-md-3 pb-4">';
doliprospectform_print_submissions_list($db, $langs, $user);
print '</div>';

llxFooter();
$db->close();
