<?php
/* Copyright (C) 2024 SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/custom/productrecall/product_recalls_tab.php
 *	\ingroup    productrecall
 *	\brief      Tab to show product recalls for a specific product
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
dol_include_once('/productrecall/class/recall.class.php');

// Load translation files required by the page
$langs->loadLangs(array('products', 'productrecall@productrecall'));

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');

// Security check
$fieldvalue = (!empty($id) ? $id : (!empty($ref) ? $ref : ''));
$fieldtype = (!empty($ref) ? 'ref' : 'rowid');
if ($user->socid) {
	$socid = $user->socid;
}

// Check permissions
if (!$user->hasRight('productrecall', 'read')) {
	accessforbidden();
}

$object = new Product($db);
if ($id > 0 || !empty($ref)) {
	$result = $object->fetch($id, $ref);
	if ($result <= 0) {
		dol_print_error($db, $object->error);
		exit;
	}
}

if ($object->id > 0) {
	if ($object->type == $object::TYPE_PRODUCT) {
		restrictedArea($user, 'produit', $object->id, 'product&product', '', '');
	}
	if ($object->type == $object::TYPE_SERVICE) {
		restrictedArea($user, 'service', $object->id, 'product&product', '', '');
	}
} else {
	restrictedArea($user, 'produit|service', $fieldvalue, 'product&product', '', '', $fieldtype);
}

// Initialize technical object to manage hooks of page
$hookmanager->initHooks(array('productrecallcard', 'globalcard'));

/*
 * Actions
 */

$parameters = array('id'=>$id, 'ref'=>$ref);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

/*
 * View
 */

$title = $langs->trans('ProductServiceCard');
$helpurl = '';
$shortlabel = dol_trunc($object->label, 16);
if (GETPOST("type") == '0' || ($object->type == Product::TYPE_PRODUCT)) {
	$title = $langs->trans('Product')." ".$shortlabel." - ".$langs->trans('ProductRecall Alerts');
	$helpurl = 'EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
}
if (GETPOST("type") == '1' || ($object->type == Product::TYPE_SERVICE)) {
	$title = $langs->trans('Service')." ".$shortlabel." - ".$langs->trans('ProductRecall Alerts');
	$helpurl = 'EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
}

llxHeader('', $title, $helpurl);

$form = new Form($db);

$head = product_prepare_head($object);
$titre = $langs->trans("CardProduct".$object->type);
$picto = ($object->type == Product::TYPE_SERVICE ? 'service' : 'product');

print dol_get_fiche_head($head, 'productrecall_alerts', $titre, 0, $picto);

$linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1&type='.$object->type.'">'.$langs->trans("BackToList").'</a>';

$shownav = 1;
if ($user->socid && !in_array('product', explode(',', getDolGlobalString('MAIN_MODULES_FOR_EXTERNAL')))) {
	$shownav = 0;
}

dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref', '', '', '', 0, '', '', 1);

print dol_get_fiche_end();

// Product recalls list
print "\n";
print load_fiche_titre($langs->trans("ProductRecallAlertsForProduct"), '', 'fa-exclamation-triangle');

print '<div class="div-table-responsive">'."\n";
print '<table class="noborder centpercent">';

// Build SQL query to find recalls related to this product
$sql = "SELECT r.rowid, r.referencefiche, r.nomsdesmodelesoureferences, r.nomdelamarqueduproduit,";
$sql .= " r.catgoriedeproduit, r.souscatgoriedeproduit, r.motifdurappel, r.risquesencourusparleconsomm,";
$sql .= " r.datedepublication, r.numerodecontact, r.date_creation";
$sql .= " FROM ".MAIN_DB_PREFIX."productrecall_recall as r";
$sql .= " WHERE r.entity IN (".getEntity('productrecall_recall').")";

// Filter by product name - search in multiple fields
$productname = $db->escape($object->ref);
$productlabel = $db->escape($object->label);

$sql .= " AND (";
$sql .= " r.nomsdesmodelesoureferences LIKE '%".$productname."%'";
$sql .= " OR r.nomsdesmodelesoureferences LIKE '%".$productlabel."%'";
$sql .= " OR r.nomdelamarqueduproduit LIKE '%".$productname."%'";
$sql .= " OR r.nomdelamarqueduproduit LIKE '%".$productlabel."%'";
$sql .= " OR r.identificationdesproduits LIKE '%".$productname."%'";
$sql .= " OR r.identificationdesproduits LIKE '%".$productlabel."%'";
$sql .= ")";

// Order from newest to oldest
$sql .= " ORDER BY r.datedepublication DESC, r.date_creation DESC";

$result = $db->query($sql);
if ($result) {
	$num = $db->num_rows($result);

	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Reference").'</td>';
	print '<td>'.$langs->trans("ProductModelOrRef").'</td>';
	print '<td>'.$langs->trans("Brand").'</td>';
	print '<td>'.$langs->trans("Category").'</td>';
	print '<td>'.$langs->trans("RecallReason").'</td>';
	print '<td>'.$langs->trans("RisksForConsumer").'</td>';
	print '<td class="center">'.$langs->trans("PublicationDate").'</td>';
	print '<td>'.$langs->trans("ContactNumber").'</td>';
	print '</tr>';

	if ($num > 0) {
		$recall = new Recall($db);
		$i = 0;

		while ($i < $num) {
			$obj = $db->fetch_object($result);

			print '<tr class="oddeven">';

			// Reference with link to recall detail
			print '<td class="nowraponall">';
			if (!empty($obj->referencefiche)) {
				$recall->id = $obj->rowid;
				$recall->referencefiche = $obj->referencefiche;
				print $recall->getNomUrl(1);
			} else {
				print '<em>'.$langs->trans("NoRef").'</em>';
			}
			print '</td>';

			// Product model or reference
			print '<td class="tdoverflowmax200" title="'.dol_escape_htmltag($obj->nomsdesmodelesoureferences).'">'.dol_escape_htmltag($obj->nomsdesmodelesoureferences).'</td>';

			// Brand
			print '<td class="tdoverflowmax150" title="'.dol_escape_htmltag($obj->nomdelamarqueduproduit).'">'.dol_escape_htmltag($obj->nomdelamarqueduproduit).'</td>';

			// Category
			$category = '';
			if (!empty($obj->catgoriedeproduit)) {
				$category = $obj->catgoriedeproduit;
				if (!empty($obj->souscatgoriedeproduit)) {
					$category .= ' > '.$obj->souscatgoriedeproduit;
				}
			}
			print '<td class="tdoverflowmax150" title="'.dol_escape_htmltag($category).'">'.dol_escape_htmltag($category).'</td>';

			// Recall reason
			print '<td class="tdoverflowmax200" title="'.dol_escape_htmltag($obj->motifdurappel).'">'.dol_escape_htmltag($obj->motifdurappel).'</td>';

			// Risks for consumer
			print '<td class="tdoverflowmax200" title="'.dol_escape_htmltag($obj->risquesencourusparleconsomm).'">'.dol_escape_htmltag($obj->risquesencourusparleconsomm).'</td>';

			// Publication date
			print '<td class="center">';
			if (!empty($obj->datedepublication)) {
				print dol_print_date($db->jdate($obj->datedepublication), "day");
			} else {
				print dol_print_date($db->jdate($obj->date_creation), "day");
			}
			print '</td>';

			// Contact number
			print '<td>';
			if (!empty($obj->numerodecontact)) {
				print dol_print_phone($obj->numerodecontact);
			}
			print '</td>';

			print '</tr>';
			$i++;
		}
	} else {
		print '<tr class="oddeven"><td colspan="8">';
		print '<div class="info">';
		print img_picto('', 'info').' ';
		print $langs->trans("NoRecallsFoundForThisProduct");
		print '</div>';
		print '</td></tr>';
	}
	$db->free($result);
} else {
	dol_print_error($db);
}

$parameters = array('sql'=>$sql, 'function'=>'show_product_recalls');
$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object, $action);
print $hookmanager->resPrint;

print "</table>";
print '</div>';

// Add some information about product recalls
if ($num > 0) {
	print '<br>';
	print '<div class="info">';
	print '<strong>'.$langs->trans("ImportantInformation").':</strong><br>';
	print $langs->trans("ProductRecallAlertInfo");
	print '</div>';
}

// End of page
llxFooter();
$db->close();