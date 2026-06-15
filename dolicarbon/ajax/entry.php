<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/services/CarbonEntryService.php';
require_once __DIR__.'/../class/services/CarbonAuditService.php';
require_once __DIR__.'/../class/dolicarbonentry.class.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

function dc_entry_payload(DoliCarbonEntry $item)
{
	return array(
		'id' => (int) $item->id,
		'fk_bilan' => (int) $item->fk_bilan,
		'scope' => (int) $item->scope,
		'category' => $item->category,
		'label' => $item->label,
		'quantity' => (float) $item->quantity,
		'unit' => $item->unit,
		'fk_factor' => $item->fk_factor !== null ? (int) $item->fk_factor : null,
		'tco2e_computed' => (float) $item->tco2e_computed,
		'source_type' => $item->source_type,
		'source_ref' => $item->source_ref,
		'quality_grade' => isset($item->quality_grade) ? $item->quality_grade : 'B',
		'uncertainty_pct_low' => isset($item->uncertainty_pct_low) ? (float) $item->uncertainty_pct_low : 10,
		'uncertainty_pct_high' => isset($item->uncertainty_pct_high) ? (float) $item->uncertainty_pct_high : 20,
		'workflow_status' => isset($item->workflow_status) ? $item->workflow_status : 'draft',
		'evidence_ref' => isset($item->evidence_ref) ? $item->evidence_ref : '',
		'factor_kgco2e_snapshot' => isset($item->factor_kgco2e_snapshot) ? (float) $item->factor_kgco2e_snapshot : null,
		'calculation_formula' => isset($item->calculation_formula) ? $item->calculation_formula : null,
		'calculation_fingerprint' => isset($item->calculation_fingerprint) ? $item->calculation_fingerprint : null,
	);
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
	$body = array();
}

if ($method === 'GET') {
	$fk_bilan = (int) ($_GET['fk_bilan'] ?? 0);
	$service = new CarbonEntryService($db);
	$items = $service->listByBilan($fk_bilan);
	$out = array();
	foreach ($items as $item) {
		$out[] = dc_entry_payload($item);
	}
	dc_json($out);
}

if ($method === 'POST') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$item = new DoliCarbonEntry($db);
	$item->fk_bilan = (int) ($body['fk_bilan'] ?? 0);
	$item->scope = (int) ($body['scope'] ?? 3);
	$item->category = trim((string) ($body['category'] ?? ''));
	$item->label = trim((string) ($body['label'] ?? ''));
	$item->quantity = (float) ($body['quantity'] ?? 0);
	$item->unit = trim((string) ($body['unit'] ?? ''));
	$item->fk_factor = (int) ($body['fk_factor'] ?? 0);
	$item->source_type = trim((string) ($body['source_type'] ?? 'manual'));
	$item->source_ref = trim((string) ($body['source_ref'] ?? ''));
	if (isset($body['quality_grade'])) {
		$item->quality_grade = substr(trim((string) $body['quality_grade']), 0, 2);
	}
	if (isset($body['uncertainty_pct_low'])) {
		$item->uncertainty_pct_low = (float) $body['uncertainty_pct_low'];
	}
	if (isset($body['uncertainty_pct_high'])) {
		$item->uncertainty_pct_high = (float) $body['uncertainty_pct_high'];
	}
	if (isset($body['evidence_ref'])) {
		$item->evidence_ref = trim((string) $body['evidence_ref']);
	}
	$res = $item->create($user, 0);
	if ($res <= 0) {
		dc_json(array('error' => 'CREATE_FAILED', 'message' => $item->error), 400);
	}
	$audit = new CarbonAuditService($db);
	$audit->log('dolicarbon_entry', (int) $item->id, 'create', $user, array());
	dc_json(dc_entry_payload($item), 201);
}

if ($method === 'PUT') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$id = (int) ($body['id'] ?? 0);
	$item = new DoliCarbonEntry($db);
	if ($id <= 0 || $item->fetch($id) <= 0) {
		dc_json(array('error' => 'NOT_FOUND'), 404);
	}
	if (!empty($item->workflow_status) && $item->workflow_status === 'locked' && !$user->admin) {
		dc_json(array('error' => 'ENTRY_LOCKED'), 403);
	}
	$item->scope = (int) ($body['scope'] ?? $item->scope);
	$item->category = trim((string) ($body['category'] ?? $item->category));
	$item->label = trim((string) ($body['label'] ?? $item->label));
	$item->quantity = (float) ($body['quantity'] ?? $item->quantity);
	$item->unit = trim((string) ($body['unit'] ?? $item->unit));
	$item->fk_factor = isset($body['fk_factor']) ? (int) $body['fk_factor'] : $item->fk_factor;
	if (isset($body['source_type'])) {
		$item->source_type = trim((string) $body['source_type']);
	}
	if (isset($body['source_ref'])) {
		$item->source_ref = trim((string) $body['source_ref']);
	}
	if (isset($body['quality_grade'])) {
		$item->quality_grade = substr(trim((string) $body['quality_grade']), 0, 2);
	}
	if (isset($body['uncertainty_pct_low'])) {
		$item->uncertainty_pct_low = (float) $body['uncertainty_pct_low'];
	}
	if (isset($body['uncertainty_pct_high'])) {
		$item->uncertainty_pct_high = (float) $body['uncertainty_pct_high'];
	}
	if (isset($body['workflow_status'])) {
		$ws = trim((string) $body['workflow_status']);
		if (in_array($ws, array('validated', 'locked'), true) && !$user->hasRight('dolicarbon', 'validate') && !$user->admin) {
			dc_json(array('error' => 'FORBIDDEN_VALIDATE'), 403);
		}
		$item->workflow_status = $ws;
	}
	if (isset($body['evidence_ref'])) {
		$item->evidence_ref = trim((string) $body['evidence_ref']);
	}
	$res = $item->update($user, 0);
	if ($res <= 0) {
		dc_json(array('error' => 'UPDATE_FAILED', 'message' => $item->error), 400);
	}
	$audit = new CarbonAuditService($db);
	$audit->log('dolicarbon_entry', (int) $item->id, 'update', $user, array());
	dc_json(dc_entry_payload($item));
}

if ($method === 'DELETE') {
	if (!$user->hasRight('dolicarbon', 'delete')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($body['id'] ?? 0);
	$item = new DoliCarbonEntry($db);
	if ($id <= 0 || $item->fetch($id) <= 0) {
		dc_json(array('error' => 'NOT_FOUND'), 404);
	}
	$eid = (int) $item->id;
	$res = $item->delete($user, 0);
	if ($res <= 0) {
		dc_json(array('error' => 'DELETE_FAILED', 'message' => $item->error), 400);
	}
	$audit = new CarbonAuditService($db);
	$audit->log('dolicarbon_entry', $eid, 'delete', $user, array());
	dc_json(array('success' => true));
}

dc_json(array('error' => 'METHOD_NOT_ALLOWED'), 405);

