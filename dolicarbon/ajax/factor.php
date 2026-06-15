<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/dolicarbonfactor.class.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

function dc_factor_payload(DoliCarbonFactor $item)
{
	return array(
		'id' => (int) $item->id,
		'code' => $item->code,
		'label' => $item->label,
		'category' => $item->category,
		'scope' => (int) $item->scope,
		'unit_input' => $item->unit_input,
		'kgco2e_per_unit' => (float) $item->kgco2e_per_unit,
		'source' => $item->source,
		'year_ref' => $item->year_ref !== null ? (int) $item->year_ref : null,
		'active' => (int) $item->active,
		'version_label' => isset($item->version_label) ? $item->version_label : '1.0',
		'valid_from' => isset($item->valid_from) ? $item->valid_from : null,
		'valid_to' => isset($item->valid_to) ? $item->valid_to : null,
		'governance_status' => isset($item->governance_status) ? $item->governance_status : 'validated',
		'replacement_note' => isset($item->replacement_note) ? $item->replacement_note : '',
		'priority_rank' => isset($item->priority_rank) ? (int) $item->priority_rank : 0,
	);
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
	$body = array();
}

if ($method === 'GET') {
	global $conf;
	$scope = isset($_GET['scope']) && $_GET['scope'] !== '' ? (int) $_GET['scope'] : null;
	$category = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
	$search = isset($_GET['search']) ? trim((string) $_GET['search']) : '';

	$sql = "SELECT rowid FROM ".$db->prefix()."dolicarbon_factor";
	$sql .= " WHERE entity IN (0, ".((int) $conf->entity).")";
	if ($scope !== null) {
		$sql .= " AND scope = ".((int) $scope);
	}
	if ($category !== '') {
		$sql .= " AND category = '".$db->escape($category)."'";
	}
	if ($search !== '') {
		$sql .= " AND (code LIKE '%".$db->escape($search)."%' OR label LIKE '%".$db->escape($search)."%')";
	}
	$sql .= " ORDER BY scope ASC, category ASC, label ASC";

	$resql = $db->query($sql);
	$out = array();
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			$item = new DoliCarbonFactor($db);
			if ($item->fetch((int) $obj->rowid) > 0) {
				$out[] = dc_factor_payload($item);
			}
		}
	}
	dc_json($out);
}

if ($method === 'POST') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$item = new DoliCarbonFactor($db);
	$item->code = trim((string) ($body['code'] ?? ''));
	$item->label = trim((string) ($body['label'] ?? ''));
	$item->category = trim((string) ($body['category'] ?? ''));
	$item->scope = (int) ($body['scope'] ?? 3);
	$item->unit_input = trim((string) ($body['unit_input'] ?? ''));
	$item->kgco2e_per_unit = (float) ($body['kgco2e_per_unit'] ?? 0);
	$item->source = trim((string) ($body['source'] ?? ''));
	$item->year_ref = ($body['year_ref'] ?? '') !== '' ? (int) $body['year_ref'] : null;
	$item->active = isset($body['active']) ? (int) !empty($body['active']) : 1;
	$item->version_label = trim((string) ($body['version_label'] ?? '1.0'));
	$item->valid_from = ($body['valid_from'] ?? '') !== '' ? trim((string) $body['valid_from']) : null;
	$item->valid_to = ($body['valid_to'] ?? '') !== '' ? trim((string) $body['valid_to']) : null;
	$item->governance_status = trim((string) ($body['governance_status'] ?? 'validated'));
	$item->replacement_note = trim((string) ($body['replacement_note'] ?? ''));
	$item->priority_rank = isset($body['priority_rank']) ? (int) $body['priority_rank'] : 0;

	$res = $item->create($user, 0);
	if ($res <= 0) {
		dc_json(array('error' => 'CREATE_FAILED', 'message' => $item->error), 400);
	}
	dc_json(dc_factor_payload($item), 201);
}

if ($method === 'PUT') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$id = (int) ($body['id'] ?? 0);
	$item = new DoliCarbonFactor($db);
	if ($id <= 0 || $item->fetch($id) <= 0) {
		dc_json(array('error' => 'NOT_FOUND'), 404);
	}
	$item->code = trim((string) ($body['code'] ?? $item->code));
	$item->label = trim((string) ($body['label'] ?? $item->label));
	$item->category = trim((string) ($body['category'] ?? $item->category));
	$item->scope = (int) ($body['scope'] ?? $item->scope);
	$item->unit_input = trim((string) ($body['unit_input'] ?? $item->unit_input));
	$item->kgco2e_per_unit = (float) ($body['kgco2e_per_unit'] ?? $item->kgco2e_per_unit);
	$item->source = trim((string) ($body['source'] ?? $item->source));
	$item->year_ref = ($body['year_ref'] ?? '') !== '' ? (int) $body['year_ref'] : null;
	$item->active = isset($body['active']) ? (int) !empty($body['active']) : (int) $item->active;
	if (isset($body['version_label'])) {
		$item->version_label = trim((string) $body['version_label']);
	}
	if (array_key_exists('valid_from', $body)) {
		$item->valid_from = $body['valid_from'] !== '' && $body['valid_from'] !== null ? trim((string) $body['valid_from']) : null;
	}
	if (array_key_exists('valid_to', $body)) {
		$item->valid_to = $body['valid_to'] !== '' && $body['valid_to'] !== null ? trim((string) $body['valid_to']) : null;
	}
	if (isset($body['governance_status'])) {
		$item->governance_status = trim((string) $body['governance_status']);
	}
	if (isset($body['replacement_note'])) {
		$item->replacement_note = trim((string) $body['replacement_note']);
	}
	if (isset($body['priority_rank'])) {
		$item->priority_rank = (int) $body['priority_rank'];
	}

	$res = $item->update($user, 0);
	if ($res <= 0) {
		dc_json(array('error' => 'UPDATE_FAILED', 'message' => $item->error), 400);
	}
	dc_json(dc_factor_payload($item));
}

if ($method === 'DELETE') {
	if (!$user->hasRight('dolicarbon', 'delete')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($body['id'] ?? 0);
	$item = new DoliCarbonFactor($db);
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

