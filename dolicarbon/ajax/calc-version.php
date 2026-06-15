<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/services/CarbonCalculationEngineService.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

$engine = new CarbonCalculationEngineService($db);
$method = strtoupper($_SERVER['REQUEST_METHOD']);

if ($method === 'GET') {
	dc_json(array(
		'active' => $engine->getActiveVersion(),
		'versions' => $engine->listVersions(),
	));
}

dc_json(array('error' => 'METHOD_NOT_ALLOWED'), 405);
