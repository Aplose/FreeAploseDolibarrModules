<?php
/* DoliCarbon Angular shell */

$res = 0;
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once __DIR__.'/class/angularloader.class.php';

$langs->loadLangs(array('dolicarbon@dolicarbon'));
if (!isModEnabled('dolicarbon') || !$user->hasRight('dolicarbon', 'read')) {
	accessforbidden();
}

$loader = new DoliCarbonAngularLoader(false, true);
llxHeader(
	'',
	$langs->trans('DoliCarbonArea'),
	'',
	'',
	0,
	0,
	array(),
	$loader->getStyles(),
	'',
	'',
	$loader->generateHtml()
);
llxFooter();
$db->close();

