<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/services/CarbonReportService.php';
require_once __DIR__.'/../class/services/CarbonCadrageService.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

$bilanId = (int) ($_GET['bilanid'] ?? $_GET['fk_bilan'] ?? 0);
$service = new CarbonReportService($db);
$scopes = $service->getScopesTotals($bilanId);
$uncertainty = $service->getUncertaintyTotals($bilanId);
$analyst_breakdown = $service->getCategoryScopeBreakdown($bilanId);

$topCategories = array();
$sql = "SELECT category, SUM(tco2e_computed) as total FROM ".$db->prefix()."dolicarbon_entry";
$sql .= " WHERE fk_bilan = ".$bilanId." GROUP BY category ORDER BY total DESC LIMIT 15";
$resql = $db->query($sql);
if ($resql) {
	while ($obj = $db->fetch_object($resql)) {
		$topCategories[] = array('category' => $obj->category, 'total' => (float) $obj->total);
	}
}

$cadrageSvc = new CarbonCadrageService($db);
$cadrage = $cadrageSvc->getByBilan($bilanId);

dc_json(array(
	'executive' => array(
		'total_tco2e' => (float) ($scopes[1] + $scopes[2] + $scopes[3]),
		'scopes' => $scopes,
		'top_categories' => array_slice($topCategories, 0, 5),
	),
	'analyst' => array(
		'category_scope_breakdown' => $analyst_breakdown,
		'top_categories_extended' => $topCategories,
	),
	'uncertainty' => $uncertainty,
	'methodology_annex' => array(
		'cadrage' => $cadrage,
		'communication_guardrail' => 'Ne pas utiliser les termes « certifié » ou « conforme ABC » sans reconnaissance officielle. Préciser hypothèses, limites et niveau de qualité des données.',
	),
));
