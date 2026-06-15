<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/services/CarbonCadrageService.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

$svc = new CarbonCadrageService($db);
$method = strtoupper($_SERVER['REQUEST_METHOD']);
$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
	$body = array();
}

$fk_bilan = (int) ($_GET['fk_bilan'] ?? $body['fk_bilan'] ?? 0);

if ($method === 'GET') {
	if ($fk_bilan <= 0) {
		dc_json(array('error' => 'MISSING_FK_BILAN'), 400);
	}
	$data = $svc->getByBilan($fk_bilan);
	dc_json($data ?: new stdClass());
}

if ($method === 'PUT' || $method === 'POST') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	if ($fk_bilan <= 0) {
		dc_json(array('error' => 'MISSING_FK_BILAN'), 400);
	}
	$c = $svc->save($user, $fk_bilan, $body);
	if ($c->error) {
		dc_json(array('error' => $c->error, 'message' => $c->error), 400);
	}
	dc_json($svc->toArray($c));
}

dc_json(array('error' => 'METHOD_NOT_ALLOWED'), 405);
