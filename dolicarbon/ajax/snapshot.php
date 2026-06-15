<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/services/CarbonSnapshotService.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
	$body = array();
}

$svc = new CarbonSnapshotService($db);

if ($method === 'GET') {
	$id = (int) ($_GET['id'] ?? 0);
	$fk_bilan = (int) ($_GET['fk_bilan'] ?? 0);
	if ($id > 0) {
		$row = $svc->getById($id);
		if (!$row) {
			dc_json(array('error' => 'NOT_FOUND'), 404);
		}
		dc_json($row);
	}
	if ($fk_bilan > 0) {
		dc_json($svc->listByBilan($fk_bilan));
	}
	dc_json(array('error' => 'MISSING_PARAMS'), 400);
}

if ($method === 'POST') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$fk_bilan = (int) ($body['fk_bilan'] ?? 0);
	$label = trim((string) ($body['label'] ?? ''));
	if ($fk_bilan <= 0) {
		dc_json(array('error' => 'MISSING_FK_BILAN'), 400);
	}
	$out = $svc->createSnapshot($user, $fk_bilan, $label);
	if (isset($out['error'])) {
		dc_json($out, 400);
	}
	dc_json($out, 201);
}

dc_json(array('error' => 'METHOD_NOT_ALLOWED'), 405);
