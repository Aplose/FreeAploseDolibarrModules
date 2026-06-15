<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/services/CarbonEntryService.php';
require_once __DIR__.'/../class/services/CarbonCadrageService.php';
require_once __DIR__.'/../class/dolicarbonbilan.class.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$fk_bilan = (int) ($_GET['fk_bilan'] ?? 0);
$format = strtolower(trim((string) ($_GET['format'] ?? 'json')));
if ($fk_bilan <= 0) {
	header('HTTP/1.0 400 Bad Request');
	exit;
}

$bilan = new DoliCarbonBilan($db);
if ($bilan->fetch($fk_bilan) <= 0) {
	header('HTTP/1.0 404 Not Found');
	exit;
}

$entrySvc = new CarbonEntryService($db);
$entries = $entrySvc->listByBilan($fk_bilan);
$cadrageSvc = new CarbonCadrageService($db);
$cadrage = $cadrageSvc->getByBilan($fk_bilan);

$rows = array();
foreach ($entries as $e) {
	$rows[] = array(
		'id' => (int) $e->id,
		'scope' => (int) $e->scope,
		'category' => $e->category,
		'label' => $e->label,
		'quantity' => (float) $e->quantity,
		'unit' => $e->unit,
		'fk_factor' => $e->fk_factor ? (int) $e->fk_factor : null,
		'tco2e_computed' => (float) $e->tco2e_computed,
		'quality_grade' => isset($e->quality_grade) ? $e->quality_grade : 'B',
		'uncertainty_pct_low' => isset($e->uncertainty_pct_low) ? (float) $e->uncertainty_pct_low : 10,
		'uncertainty_pct_high' => isset($e->uncertainty_pct_high) ? (float) $e->uncertainty_pct_high : 20,
		'workflow_status' => isset($e->workflow_status) ? $e->workflow_status : 'draft',
		'evidence_ref' => isset($e->evidence_ref) ? $e->evidence_ref : '',
		'calculation_fingerprint' => isset($e->calculation_fingerprint) ? $e->calculation_fingerprint : '',
		'source_type' => $e->source_type,
	);
}

$payload = array(
	'export_version' => '1',
	'bilan' => array('id' => (int) $bilan->id, 'ref' => $bilan->ref, 'year' => (int) $bilan->year, 'total_tco2e' => (float) $bilan->total_tco2e),
	'methodology_annex' => array(
		'cadrage' => $cadrage,
		'limits' => 'Les totaux dépendent des hypothèses et facteurs saisis. DoliCarbon n’est pas « Bilan Carbone Conform » ABC sans audit externe.',
	),
	'entries' => $rows,
);

if ($format === 'csv') {
	header('Content-Type: text/csv; charset=UTF-8');
	header('Content-Disposition: attachment; filename="dolicarbon_bilan_'.$fk_bilan.'.csv"');
	$out = fopen('php://output', 'w');
	fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
	fputcsv($out, array('id', 'scope', 'category', 'label', 'quantity', 'unit', 'tco2e', 'quality', 'unc_low_pct', 'unc_high_pct', 'workflow', 'evidence_ref', 'fingerprint'), ';');
	foreach ($rows as $r) {
		fputcsv($out, array(
			$r['id'], $r['scope'], $r['category'], $r['label'], $r['quantity'], $r['unit'], $r['tco2e_computed'],
			$r['quality_grade'], $r['uncertainty_pct_low'], $r['uncertainty_pct_high'], $r['workflow_status'],
			$r['evidence_ref'], $r['calculation_fingerprint'],
		), ';');
	}
	fclose($out);
	exit;
}

header('Content-Type: application/json; charset=UTF-8');
header('Content-Disposition: attachment; filename="dolicarbon_bilan_'.$fk_bilan.'.json"');
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
