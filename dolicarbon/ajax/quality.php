<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/dolicarbonentry.class.php';
require_once __DIR__.'/../class/services/CarbonAuditService.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
	$body = array();
}

if ($method === 'PUT' || $method === 'PATCH') {
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
	$res = $item->update($user, 0);
	if ($res <= 0) {
		dc_json(array('error' => 'UPDATE_FAILED', 'message' => $item->error), 400);
	}
	$audit = new CarbonAuditService($db);
	$audit->log('dolicarbon_entry', (int) $item->id, 'quality_update', $user, array('fields' => array_keys($body)));
	dc_json(array('success' => true, 'id' => (int) $item->id));
}

dc_json(array('error' => 'METHOD_NOT_ALLOWED'), 405);
