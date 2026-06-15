<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr> */

/**
 * \file        carbon_import.php
 * \ingroup     dolicarbon
 * \brief       Import activity data from Dolibarr objects
 */

$res = 0;
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once __DIR__.'/class/dolicarbonbilan.class.php';
require_once __DIR__.'/class/dolicarbonentry.class.php';
require_once __DIR__.'/class/dolicarbonfactor.class.php';
require_once __DIR__.'/lib/dolicarbon.lib.php';

$langs->loadLangs(array('dolicarbon@dolicarbon', 'bills'));

if (!isModEnabled('dolicarbon') || !$user->hasRight('dolicarbon', 'write')) {
	accessforbidden();
}

$step = GETPOSTINT('step');
if ($step <= 0) {
	$step = 1;
}

$fk_bilan = GETPOSTINT('fk_bilan');
$source = GETPOST('source', 'aZ09');
if ($step >= 2 && GETPOSTISSET('fk_bilan')) {
	$fk_bilan = GETPOSTINT('fk_bilan');
}
if ($step >= 2 && GETPOSTISSET('source')) {
	$source = GETPOST('source', 'aZ09');
}
$dt_start = dol_mktime(0, 0, 0, GETPOSTINT('date_startmonth'), GETPOSTINT('date_startday'), GETPOSTINT('date_startyear'));
$dt_end = dol_mktime(23, 59, 59, GETPOSTINT('date_endmonth'), GETPOSTINT('date_endday'), GETPOSTINT('date_endyear'));

$bilan = new DoliCarbonBilan($db);
if ($fk_bilan > 0 && $bilan->fetch($fk_bilan) <= 0) {
	$fk_bilan = 0;
}

if ($fk_bilan > 0 && (int) $bilan->status !== DoliCarbonBilan::STATUS_DRAFT) {
	setEventMessages($langs->trans('ErrorOnlyDraftEditable'), null, 'errors');
	$fk_bilan = 0;
}

$form = new Form($db);

if ($step == 5 && GETPOST('confirm', 'alpha') == 'yes' && $fk_bilan > 0 && $source == 'supplier_invoice') {
	$lines = isset($_SESSION['dolicarbon_import_preview']) ? $_SESSION['dolicarbon_import_preview'] : array();
	foreach ($lines as $ln) {
		if (empty($ln['take'])) {
			continue;
		}
		$ent = new DoliCarbonEntry($db);
		$ent->fk_bilan = $fk_bilan;
		$ent->scope = 3;
		$ent->category = 'purchases_services';
		$ent->label = $ln['label'];
		$ent->quantity = (float) $ln['qty'];
		$ent->unit = 'EUR';
		$ent->fk_factor = (int) $ln['fk_factor'];
		$ent->source_type = 'invoice';
		$ent->fk_source_object = (int) $ln['fk_invoice'];
		$ent->source_ref = $ln['ref'];
		$ent->create($user);
		if (!empty($ln['fk_soc']) && !empty($ln['fk_factor'])) {
			dolicarbon_import_map_save($db, (int) $ln['fk_soc'], 'purchases_services', (int) $ln['fk_factor']);
		}
	}
	unset($_SESSION['dolicarbon_import_preview']);
	setEventMessages($langs->trans('DOLICARBON_ImportDone'), null, 'mesgs');
	header('Location: '.dol_buildpath('/dolicarbon/carbon_entry_list.php', 1).'?fk_bilan='.$fk_bilan);
	exit;
}

llxHeader('', $langs->trans('ImportFromDolibarr'), '', '', 0, 0, '', '', '', 'mod-dolicarbon page-import');

print load_fiche_titre($langs->trans('ImportFromDolibarr'), '', 'fa-download');

print '<p><span class="opacitymedium">'.$langs->trans('DOLICARBON_ImportWizardStep').' '.$step.' / 5</span></p>';

if ($step == 1) {
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="step" value="2">';
	print '<table class="border centpercent">';
	print '<tr><td class="titlefield fieldrequired">'.$langs->trans('Bilan').'</td><td>';
	$sqlb = "SELECT rowid, ref, year FROM ".$db->prefix()."dolicarbon_bilan WHERE entity IN (".getEntity('societe').") AND status = ".DoliCarbonBilan::STATUS_DRAFT." ORDER BY year DESC";
	$resb = $db->query($sqlb);
	$opts = array();
	if ($resb) {
		while ($o = $db->fetch_object($resb)) {
			$opts[$o->rowid] = $o->ref.' ('.$o->year.')';
		}
	}
	print $form->selectarray('fk_bilan', $opts, $fk_bilan, 0, 0, 0, '', 1, 0, '', '', 1);
	print '</td></tr>';
	print '<tr><td class="fieldrequired">'.$langs->trans('Source').'</td><td>';
	print $form->selectarray('source', array('supplier_invoice' => $langs->trans('SupplierInvoices')), GETPOST('source', 'aZ09') ?: 'supplier_invoice', 0, 0, 0, '', 1, 0, '', '', 1);
	print '</td></tr>';
	print '</table>';
	print '<div class="center"><input type="submit" class="button" value="'.$langs->trans('Next').'"></div>';
	print '</form>';
} elseif ($step == 2 && $fk_bilan > 0) {
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="step" value="3">';
	print '<input type="hidden" name="fk_bilan" value="'.((int) $fk_bilan).'">';
	print '<input type="hidden" name="source" value="'.dol_escape_htmltag($source).'">';
	print '<table class="border centpercent">';
	print '<tr><td>'.$langs->trans('DateStart').'</td><td>';
	print $form->selectDate($dt_start > 0 ? $dt_start : dol_time_plus_duree(dol_now(), -1, 'y'), 'date_start', 0, 0, 0, '', 1, 0);
	print '</td></tr>';
	print '<tr><td>'.$langs->trans('DateEnd').'</td><td>';
	print $form->selectDate($dt_end > 0 ? $dt_end : dol_now(), 'date_end', 0, 0, 0, '', 1, 0);
	print '</td></tr>';
	print '</table>';
	print '<div class="center"><input type="submit" class="button" value="'.$langs->trans('Next').'"></div>';
	print '</form>';
} elseif ($step == 3 && $fk_bilan > 0 && $source == 'supplier_invoice' && $dt_start > 0 && $dt_end > 0) {
	$sql = "SELECT f.rowid, f.ref, f.fk_soc, f.total_ht, s.nom as socname";
	$sql .= " FROM ".$db->prefix()."facture_fourn as f";
	$sql .= " LEFT JOIN ".$db->prefix()."societe as s ON s.rowid = f.fk_soc";
	$sql .= " WHERE f.entity IN (".getEntity('supplier_invoice').")";
	$sql .= " AND f.datef >= '".$db->idate($dt_start)."' AND f.datef <= '".$db->idate($dt_end)."'";
	$sql .= " AND f.fk_statut >= 1";
	$sql .= " ORDER BY f.datef, f.ref";

	$resql = $db->query($sql);
	$rows = array();
	if ($resql) {
		while ($o = $db->fetch_object($resql)) {
			$rows[] = $o;
		}
	}

	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="step" value="4">';
	print '<input type="hidden" name="fk_bilan" value="'.$fk_bilan.'">';
	print '<input type="hidden" name="source" value="supplier_invoice">';
	print '<input type="hidden" name="date_startday" value="'.GETPOSTINT('date_startday').'">';
	print '<input type="hidden" name="date_startmonth" value="'.GETPOSTINT('date_startmonth').'">';
	print '<input type="hidden" name="date_startyear" value="'.GETPOSTINT('date_startyear').'">';
	print '<input type="hidden" name="date_endday" value="'.GETPOSTINT('date_endday').'">';
	print '<input type="hidden" name="date_endmonth" value="'.GETPOSTINT('date_endmonth').'">';
	print '<input type="hidden" name="date_endyear" value="'.GETPOSTINT('date_endyear').'">';

	print '<p class="opacitymedium">'.$langs->trans('DOLICARBON_MapFactorPerInvoice').'</p>';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre"><td></td><td>'.$langs->trans('Ref').'</td><td>'.$langs->trans('ThirdParty').'</td><td class="right">'.$langs->trans('AmountHT').'</td><td>'.$langs->trans('CarbonFactor').'</td></tr>';

	$facfinder = new DoliCarbonFactor($db);
	$i = 0;
	foreach ($rows as $o) {
		$mapfac = 0;
		if (!empty($o->fk_soc)) {
			$mapfac = dolicarbon_import_map_get_factor($db, (int) $o->fk_soc, 'purchases_services');
		}
		$factors = $facfinder->getByCategory('purchases_services', 3);
		$sel = array();
		foreach ($factors as $f) {
			$sel[$f->id] = $f->label;
		}
		print '<tr class="oddeven">';
		print '<td><input type="checkbox" name="take_'.$i.'" value="1" checked></td>';
		print '<td>'.dol_escape_htmltag($o->ref).'<input type="hidden" name="ref_'.$i.'" value="'.dol_escape_htmltag($o->ref, 1).'"></td>';
		print '<td>'.dol_escape_htmltag($o->socname).'<input type="hidden" name="fk_soc_'.$i.'" value="'.((int) $o->fk_soc).'"></td>';
		print '<td class="right">'.price($o->total_ht).'<input type="hidden" name="qty_'.$i.'" value="'.((float) $o->total_ht).'"></td>';
		print '<td>';
		print $form->selectarray('fk_factor_'.$i, $sel, $mapfac > 0 ? $mapfac : '', 1, 0, 0, '', 1, 0, '', '', 1);
		print '<input type="hidden" name="fk_invoice_'.$i.'" value="'.((int) $o->rowid).'">';
		print '</td></tr>';
		$i++;
	}
	print '</table>';
	print '<input type="hidden" name="numlines" value="'.$i.'">';
	print '<div class="center"><input type="submit" class="button" value="'.$langs->trans('Next').'"></div>';
	print '</form>';
} elseif ($step == 4 && $fk_bilan > 0) {
	$n = GETPOSTINT('numlines');
	$preview = array();
	for ($i = 0; $i < $n; $i++) {
		if (!GETPOSTISSET('take_'.$i)) {
			continue;
		}
		$fkfac = GETPOSTINT('fk_factor_'.$i);
		if ($fkfac <= 0) {
			continue;
		}
		$f = new DoliCarbonFactor($db);
		if ($f->fetch($fkfac) <= 0) {
			continue;
		}
		$qty = GETPOSTFLOAT('qty_'.$i, 'MT', 1);
		$tco2e = ($qty * (float) $f->kgco2e_per_unit) / 1000.0;
		$preview[] = array(
			'take' => 1,
			'ref' => GETPOST('ref_'.$i, 'alphanohtml'),
			'label' => GETPOST('ref_'.$i, 'alphanohtml').' — '.$langs->trans('SupplierInvoices'),
			'qty' => $qty,
			'fk_factor' => $fkfac,
			'fk_invoice' => GETPOSTINT('fk_invoice_'.$i),
			'fk_soc' => GETPOSTINT('fk_soc_'.$i),
			'tco2e' => $tco2e,
		);
	}
	$_SESSION['dolicarbon_import_preview'] = $preview;

	print '<h3>'.$langs->trans('Preview').'</h3>';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre"><td>'.$langs->trans('Ref').'</td><td class="right">'.$langs->trans('Quantity').'</td><td class="right">'.$langs->trans('TotalTco2e').'</td></tr>';
	foreach ($preview as $p) {
		print '<tr class="oddeven"><td>'.dol_escape_htmltag($p['ref']).'</td><td class="right">'.price2num($p['qty'], 'MT', 2).'</td><td class="right">'.price2num($p['tco2e'], 'MT', 3).'</td></tr>';
	}
	print '</table>';

	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="step" value="5">';
	print '<input type="hidden" name="fk_bilan" value="'.$fk_bilan.'">';
	print '<input type="hidden" name="source" value="supplier_invoice">';
	print '<input type="hidden" name="confirm" value="yes">';
	print '<div class="center"><input type="submit" class="button" value="'.$langs->trans('Confirm').'"></div>';
	print '</form>';
} elseif ($step == 5) {
	print '<p>'.$langs->trans('DOLICARBON_ImportDone').'</p>';
} elseif ($step == 2 && $fk_bilan <= 0) {
	print '<p class="warning">'.$langs->trans('Error').'</p>';
	print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'">'.$langs->trans('Back').'</a>';
} else {
	print '<p class="warning">'.$langs->trans('Error').'</p>';
	print '<a href="'.$_SERVER['PHP_SELF'].'">'.$langs->trans('Back').'</a>';
}

llxFooter();
$db->close();
