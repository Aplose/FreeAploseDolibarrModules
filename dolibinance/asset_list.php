<?php
/* Copyright (C) 2023 Olivier ANDRADE SANCHEZ
 */

/**
 *   	\file       asset_list.php
 *		\ingroup    dolibinance
 *		\brief      List page for binance spot wallet assets
 */


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
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
// Try main.inc.php using relative path
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("dolibinance@dolibinance"));

$sortfield = GETPOST('sortfield');
$sortorder = GETPOST('sortorder');
// Default sort order (if not yet defined by previous GETPOST)
if (!$sortfield) {
	$sortfield = "asset";                
}
if (!$sortorder) {
	$sortorder = "ASC";
}

// Security check (enable the most restrictive one)
if ($user->socid > 0) accessforbidden();
if (!isModEnabled("dolibinance")) {
	accessforbidden('Module dolibinance not enabled');
}

/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) {
	$action = 'list';
	$massaction = '';
}
/*
 * View
 */

$form = new Form($db);

$now = dol_now();

$title = $langs->trans("DoliBinanceAssetList");

$help_url = '';
$morejs = array();
$morecss = array();


require_once __DIR__ . '/includes/autoload.php';
require_once __DIR__ . '/lib/dolibinance.lib.php';

$assets = getUserAsset();

// Count total nb of records
$nbtotalofrecords = count($assets);


// Output page
// --------------------------------------------------------------------

llxHeader('', $title, $help_url, '', 0, 0, $morejs, $morecss, '', 'bodyforlist');	// Can use also classforhorizontalscrolloftabs instead of bodyforlist for no horizontal scroll

print_barre_liste($title, '', $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, '', -1, $nbtotalofrecords, '', 0, '', '', 0, 0, 0, 1);

if (!empty($search_all)&&$search_all) {
	$setupstring = '';
	foreach ($fieldstosearchall as $key => $val) {
		$fieldstosearchall[$key] = $langs->trans($val);
		$setupstring .= $key."=".$val.";";
	}
	print '<!-- Search done like if MYOBJECT_QUICKSEARCH_ON_FIELDS = '.$setupstring.' -->'."\n";
	print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $search_all).join(', ', $fieldstosearchall).'</div>'."\n";
}


print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="tagtable nobottomiftotal liste'.((!empty($moreforfilter)&&$moreforfilter) ? " listwithfilterbefore" : "").'">'."\n";


// Fields title label
// --------------------------------------------------------------------
print '<tr class="liste_titre"><tr><th class="liste_titre">'. $langs->trans('Asset').'</th><th class="liste_titre">'. $langs->trans('Free').'</th><th class="liste_titre">'. $langs->trans('Locked').'</th><th class="liste_titre">'. $langs->trans('Freeze').'</th><th class="liste_titre">'. $langs->trans('Withdrawing').'</th><th class="liste_titre">'. $langs->trans('Ipoable').'</th><th class="liste_titre">'. $langs->trans('BtcValuation').'</th></tr>';
foreach ($assets as $key => $asset) {
    echo '<tr><td>'.$asset['asset'].'</td><td>'.$asset['free'].'</td><td>'.$asset['locked'].'</td><td>'.$asset['freeze'].'</td><td>'.$asset['withdrawing'].'</td><td>'.$asset['ipoable'].'</td><td>'.$asset['btcValuation'].'</td></tr>';
}
print '</table>'."\n";
print '</div>'."\n";
// End of page
llxFooter();
