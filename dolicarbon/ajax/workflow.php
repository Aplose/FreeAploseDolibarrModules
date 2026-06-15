<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/dolicarbonentry.class.php';
require_once __DIR__.'/../class/services/CarbonWorkflowService.php';
require_once __DIR__.'/../class/services/CarbonAuditService.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
	$body = array();
}

$svc = new CarbonWorkflowService($db);
$fk_bilan = (int) ($_GET['fk_bilan'] ?? $body['fk_bilan'] ?? 0);

if ($method === 'GET') {
	if ($fk_bilan <= 0) {
		dc_json(array('error' => 'MISSING_FK_BILAN'), 400);
	}
	dc_json($svc->listComments($fk_bilan));
}

if ($method === 'POST') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	if ($fk_bilan <= 0) {
		dc_json(array('error' => 'MISSING_FK_BILAN'), 400);
	}
	$msg = trim((string) ($body['message'] ?? ''));
	if ($msg === '') {
		dc_json(array('error' => 'EMPTY_MESSAGE'), 400);
	}
	$fk_entry = isset($body['fk_entry']) ? (int) $body['fk_entry'] : null;
	$ws = isset($body['workflow_status']) ? trim((string) $body['workflow_status']) : null;
	$id = $svc->addComment($user, $fk_bilan, $msg, $fk_entry, $ws);
	if ($id < 0) {
		dc_json(array('error' => 'INSERT_FAILED'), 400);
	}
	$audit = new CarbonAuditService($db);
	$audit->log('dolicarbon_bilan', $fk_bilan, 'workflow_comment', $user, array('comment_id' => $id));
	dc_json(array('success' => true, 'id' => $id), 201);
}

if ($method === 'PUT') {
	$entryId = (int) ($body['entry_id'] ?? $body['id'] ?? 0);
	$newStatus = trim((string) ($body['workflow_status'] ?? ''));
	if ($entryId <= 0 || $newStatus === '') {
		dc_json(array('error' => 'INVALID'), 400);
	}
	$restricted = in_array($newStatus, array('validated', 'locked'), true);
	if ($restricted && !$user->hasRight('dolicarbon', 'validate') && !$user->admin) {
		dc_json(array('error' => 'FORBIDDEN_VALIDATE'), 403);
	}
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$item = new DoliCarbonEntry($db);
	if ($item->fetch($entryId) <= 0) {
		dc_json(array('error' => 'NOT_FOUND'), 404);
	}
	if (!empty($item->workflow_status) && $item->workflow_status === 'locked' && !$user->admin) {
		dc_json(array('error' => 'ENTRY_LOCKED'), 403);
	}
	$old = $item->workflow_status;
	$item->workflow_status = $newStatus;
	$res = $item->update($user, 0);
	if ($res <= 0) {
		dc_json(array('error' => 'UPDATE_FAILED', 'message' => $item->error), 400);
	}
	$audit = new CarbonAuditService($db);
	$audit->log('dolicarbon_entry', $entryId, 'workflow_transition', $user, array('from' => $old, 'to' => $newStatus));
	dc_json(array('success' => true, 'workflow_status' => $newStatus));
}

dc_json(array('error' => 'METHOD_NOT_ALLOWED'), 405);
