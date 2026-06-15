<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/services/CarbonAuditService.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

$fk_bilan = (int) ($_GET['fk_bilan'] ?? 0);
$elementType = trim((string) ($_GET['element_type'] ?? ''));
$fk_element = (int) ($_GET['fk_element'] ?? 0);

$audit = new CarbonAuditService($db);

if ($fk_bilan > 0) {
	dc_json($audit->listForBilan($fk_bilan));
}
if ($elementType !== '' && $fk_element > 0) {
	dc_json($audit->listForElement($elementType, $fk_element));
}

dc_json(array('error' => 'MISSING_PARAMS'), 400);
