<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr> */

/**
 * \file        carbon_report.php
 * \ingroup     dolicarbon
 * \brief       PDF carbon report for a bilan
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

require_once __DIR__.'/class/dolicarbonbilan.class.php';
require_once __DIR__.'/lib/dolicarbon.lib.php';
require_once DOL_DOCUMENT_ROOT.'/includes/tecnickcom/tcpdf/tcpdf.php';

$langs->loadLangs(array('dolicarbon@dolicarbon', 'main'));

if (!isModEnabled('dolicarbon') || !$user->hasRight('dolicarbon', 'read')) {
	accessforbidden();
}

$id = GETPOSTINT('id');
$bilan = new DoliCarbonBilan($db);
if ($id <= 0 || $bilan->fetch($id) <= 0) {
	accessforbidden();
}

$sql = "SELECT scope, category, label, quantity, unit, tco2e_computed, source_type";
$sql .= " FROM ".$db->prefix()."dolicarbon_entry WHERE fk_bilan = ".$id." ORDER BY scope, category, rowid";
$resql = $db->query($sql);
$lines = array();
if ($resql) {
	while ($r = $db->fetch_object($resql)) {
		$lines[] = $r;
	}
}

$sql = "SELECT label, gain_tco2e_estimated, gain_tco2e_actual, status FROM ".$db->prefix()."dolicarbon_action";
$sql .= " WHERE fk_bilan = ".$id." ORDER BY rowid";
$resql = $db->query($sql);
$actions = array();
if ($resql) {
	while ($r = $db->fetch_object($resql)) {
		$actions[] = $r;
	}
}

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('Dolibarr DoliCarbon');
$pdf->SetTitle($langs->transnoentities('DOLICARBON_ReportTitle').' '.$bilan->ref);
$pdf->SetMargins(15, 18, 15);
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, $langs->transnoentities('DOLICARBON_ReportTitle'), 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, $mysoc->name.' — '.$langs->transnoentities('Period').': '.($bilan->year ? (int) $bilan->year : '').' — '.$langs->transnoentities('Date').': '.dol_print_date(dol_now(), 'dayhour'), 0, 'L');

$pdf->Ln(4);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 8, $langs->transnoentities('ExecutiveSummary'), 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, $langs->transnoentities('TotalTco2e').': '.price2num($bilan->total_tco2e, 'MT', 3), 0, 'L');
if ($bilan->target_tco2e > 0) {
	$pdf->MultiCell(0, 6, $langs->transnoentities('TargetTco2e').': '.price2num($bilan->target_tco2e, 'MT', 3), 0, 'L');
}

$pdf->Ln(2);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 8, $langs->transnoentities('DetailedEntries'), 0, 1);
$pdf->SetFont('helvetica', '', 8);

$html = '<table border="1" cellpadding="3"><thead><tr style="background-color:#eee;">';
$html .= '<th>'.$langs->transnoentities('Scope').'</th><th>'.$langs->transnoentities('Category').'</th><th>'.$langs->transnoentities('Label').'</th>';
$html .= '<th align="right">'.$langs->transnoentities('Quantity').'</th><th>'.$langs->transnoentities('Unit').'</th><th align="right">'.$langs->transnoentities('TotalTco2e').'</th></tr></thead><tbody>';
$catmap = dolicarbon_get_category_map();
foreach ($lines as $r) {
	$clab = $r->category;
	if (isset($catmap[(int) $r->scope][$r->category])) {
		$clab = $langs->transnoentities($catmap[(int) $r->scope][$r->category]);
	}
	$html .= '<tr><td>'.((int) $r->scope).'</td><td>'.dol_escape_htmltag($clab).'</td><td>'.dol_escape_htmltag($r->label).'</td>';
	$html .= '<td align="right">'.price2num($r->quantity, 'MT', 3).'</td><td>'.dol_escape_htmltag($r->unit).'</td>';
	$html .= '<td align="right">'.price2num($r->tco2e_computed, 'MT', 3).'</td></tr>';
}
$html .= '</tbody></table>';
$pdf->writeHTML($html, true, false, true, false, '');

if (count($actions)) {
	$pdf->Ln(4);
	$pdf->SetFont('helvetica', 'B', 11);
	$pdf->Cell(0, 8, $langs->transnoentities('CarbonActions'), 0, 1);
	$pdf->SetFont('helvetica', '', 8);
	$html2 = '<table border="1" cellpadding="3"><tr style="background-color:#eee;"><th>'.$langs->transnoentities('Label').'</th><th align="right">'.$langs->transnoentities('GainEstimated').'</th><th align="right">'.$langs->transnoentities('GainActual').'</th></tr>';
	foreach ($actions as $a) {
		$html2 .= '<tr><td>'.dol_escape_htmltag($a->label).'</td><td align="right">'.price2num($a->gain_tco2e_estimated, 'MT', 3).'</td>';
		$html2 .= '<td align="right">'.price2num($a->gain_tco2e_actual, 'MT', 3).'</td></tr>';
	}
	$html2 .= '</table>';
	$pdf->writeHTML($html2, true, false, true, false, '');
}

$pdf->Ln(6);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->MultiCell(0, 5, $langs->transnoentities('DOLICARBON_ReportFooterNote'), 0, 'L');

$filename = 'dolicarbon_report_'.$bilan->ref.'.pdf';
$pdf->Output($filename, 'D');
exit;
