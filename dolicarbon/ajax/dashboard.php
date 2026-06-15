<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/services/CarbonReportService.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

$bilanId = (int) ($_GET['bilanid'] ?? 0);
$service = new CarbonReportService($db);
$scopes = $service->getScopesTotals($bilanId);

$monthly = array();
if ($bilanId > 0) {
	$sql = "SELECT DATE_FORMAT(e.date_creation, '%Y-%m') as ym, SUM(e.tco2e_computed) as total";
	$sql .= " FROM ".$db->prefix()."dolicarbon_entry e WHERE e.fk_bilan = ".$bilanId;
	$sql .= " GROUP BY ym ORDER BY ym";
} else {
	global $conf;
	$sql = "SELECT DATE_FORMAT(e.date_creation, '%Y-%m') as ym, SUM(e.tco2e_computed) as total";
	$sql .= " FROM ".$db->prefix()."dolicarbon_entry e";
	$sql .= " INNER JOIN ".$db->prefix()."dolicarbon_bilan b ON b.rowid = e.fk_bilan AND b.entity = ".((int) $conf->entity);
	$sql .= " GROUP BY ym ORDER BY ym";
}
$resql = $db->query($sql);
if ($resql) {
	while ($obj = $db->fetch_object($resql)) {
		$monthly[] = array('month' => $obj->ym, 'total' => (float) $obj->total);
	}
}

$topCategories = array();
if ($bilanId > 0) {
	$sql = "SELECT e.category, SUM(e.tco2e_computed) as total FROM ".$db->prefix()."dolicarbon_entry e";
	$sql .= " WHERE e.fk_bilan = ".$bilanId." GROUP BY e.category ORDER BY total DESC LIMIT 5";
} else {
	global $conf;
	$sql = "SELECT e.category, SUM(e.tco2e_computed) as total FROM ".$db->prefix()."dolicarbon_entry e";
	$sql .= " INNER JOIN ".$db->prefix()."dolicarbon_bilan b ON b.rowid = e.fk_bilan AND b.entity = ".((int) $conf->entity);
	$sql .= " GROUP BY e.category ORDER BY total DESC LIMIT 5";
}
$resql = $db->query($sql);
if ($resql) {
	while ($obj = $db->fetch_object($resql)) {
		$topCategories[] = array('category' => $obj->category, 'total' => (float) $obj->total);
	}
}

$kpis = array(
	'total' => (float) ($scopes[1] + $scopes[2] + $scopes[3]),
	'scope1' => (float) $scopes[1],
	'scope2' => (float) $scopes[2],
	'scope3' => (float) $scopes[3],
);

$uncertainty = $service->getUncertaintyTotals($bilanId);
$analyst_breakdown = $service->getCategoryScopeBreakdown($bilanId);

dc_json(array(
	'kpis' => $kpis,
	'scopes' => $scopes,
	'monthly' => $monthly,
	'top_categories' => $topCategories,
	'uncertainty' => $uncertainty,
	'analyst_breakdown' => $analyst_breakdown,
));

