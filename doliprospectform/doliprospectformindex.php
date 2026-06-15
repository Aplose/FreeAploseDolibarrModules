<?php
/* Copyright (C) 2001-2005  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2015       Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 *	\file       doliprospectform/doliprospectformindex.php
 *	\ingroup    doliprospectform
 *	\brief      Dashboard: statistics + substitution tags / links for the current user
 */

$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include str_replace("..", "", $_SERVER["CONTEXT_DOCUMENT_ROOT"])."/main.inc.php";
}
if (!$res && !empty($_SERVER['SCRIPT_FILENAME']) && file_exists(__DIR__.'/../../../main.inc.php')) {
	$res = @include __DIR__.'/../../../main.inc.php';
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

$socid = GETPOSTINT('socid');
if (!empty($user->socid) && $user->socid > 0) {
	$socid = $user->socid;
}

if (!isModEnabled('doliprospectform')) {
	accessforbidden('Module not enabled');
}
if (!empty($user->socid)) {
	accessforbidden();
}

llxHeader('', $langs->trans('DoliProspectFormMenuDashboard'), '', '', 0, 0, doliprospectform_backoffice_llx_js(), doliprospectform_backoffice_llx_css(), '', 'mod-doliprospectform page-index');

$head = doliprospectform_main_area_prepare_head('dashboard');
print dol_get_fiche_head($head, 'dashboard', $langs->trans('DoliProspectFormArea'), 0, 'doliprospectform@doliprospectform');

print '<div class="container-fluid px-2 px-md-3 pb-4">';
print '<div class="alert alert-info mb-3" role="status">'.$langs->trans('DoliProspectFormHomePublicFormHint').'</div>';

doliprospectform_print_dashboard_stats($db, $langs, $user);
doliprospectform_print_substitution_urls_table($db, $langs, $user, $conf);

print '</div>';

llxFooter();
$db->close();
