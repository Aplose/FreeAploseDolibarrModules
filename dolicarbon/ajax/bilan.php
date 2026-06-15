<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/services/CarbonBilanService.php';
require_once __DIR__.'/../class/dolicarbonbilan.class.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

function dc_bilan_payload(DoliCarbonBilan $item)
{
	return array(
		'id' => (int) $item->id,
		'ref' => $item->ref,
		'label' => $item->label,
		'year' => (int) $item->year,
		'status' => (int) $item->status,
		'total_tco2e' => (float) $item->total_tco2e,
		'target_tco2e' => $item->target_tco2e !== null ? (float) $item->target_tco2e : null,
		'date_start' => $item->date_start ? dol_print_date($item->date_start, '%Y-%m-%d') : null,
		'date_end' => $item->date_end ? dol_print_date($item->date_end, '%Y-%m-%d') : null,
	);
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
	$body = array();
}

if ($method === 'GET') {
	$service = new CarbonBilanService($db);
	$items = $service->listByEntity();
	$out = array();
	foreach ($items as $item) {
		$out[] = dc_bilan_payload($item);
	}
	dc_json($out);
}

if ($method === 'POST') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$item = new DoliCarbonBilan($db);
	$item->label = trim((string) ($body['label'] ?? ''));
	$item->year = (int) ($body['year'] ?? dol_print_date(dol_now(), '%Y'));
	$item->target_tco2e = isset($body['target_tco2e']) ? (float) $body['target_tco2e'] : null;
	$res = $item->create($user, 0);
	if ($res <= 0) {
		dc_json(array('error' => 'CREATE_FAILED', 'message' => $item->error), 400);
	}
	dc_json(dc_bilan_payload($item), 201);
}

if ($method === 'PUT') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$id = (int) ($body['id'] ?? 0);
	$item = new DoliCarbonBilan($db);
	if ($id <= 0 || $item->fetch($id) <= 0) {
		dc_json(array('error' => 'NOT_FOUND'), 404);
	}
	$action = (string) ($body['action'] ?? 'update');
	if ($action === 'validate') {
		$res = $item->validateBilan($user);
	} elseif ($action === 'archive') {
		$res = $item->archiveBilan($user);
	} else {
		$item->label = trim((string) ($body['label'] ?? $item->label));
		$item->year = (int) ($body['year'] ?? $item->year);
		$item->target_tco2e = isset($body['target_tco2e']) ? (float) $body['target_tco2e'] : $item->target_tco2e;
		$res = $item->update($user, 0);
	}
	if ($res <= 0) {
		dc_json(array('error' => 'UPDATE_FAILED', 'message' => $item->error), 400);
	}
	$item->fetch($id);
	dc_json(dc_bilan_payload($item));
}

if ($method === 'DELETE') {
	if (!$user->hasRight('dolicarbon', 'delete')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($body['id'] ?? 0);
	$item = new DoliCarbonBilan($db);
	if ($id <= 0 || $item->fetch($id) <= 0) {
		dc_json(array('error' => 'NOT_FOUND'), 404);
	}
	$res = $item->delete($user, 0);
	if ($res <= 0) {
		dc_json(array('error' => 'DELETE_FAILED', 'message' => $item->error), 400);
	}
	dc_json(array('success' => true));
}

dc_json(array('error' => 'METHOD_NOT_ALLOWED'), 405);

