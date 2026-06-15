<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/dolicarbonaction.class.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

function dc_action_payload(DoliCarbonAction $item)
{
	return array(
		'id' => (int) $item->id,
		'fk_bilan' => (int) $item->fk_bilan,
		'label' => $item->label,
		'description' => $item->description,
		'category' => $item->category,
		'gain_tco2e_estimated' => (float) $item->gain_tco2e_estimated,
		'gain_tco2e_actual' => (float) $item->gain_tco2e_actual,
		'cost_eur' => (float) $item->cost_eur,
		'status' => (int) $item->status,
		'fk_user_responsible' => $item->fk_user_responsible ? (int) $item->fk_user_responsible : null,
		'baseline_tco2e' => isset($item->baseline_tco2e) ? (float) $item->baseline_tco2e : 0,
		'target_tco2e' => isset($item->target_tco2e) ? (float) $item->target_tco2e : 0,
		'capex_eur' => isset($item->capex_eur) ? (float) $item->capex_eur : 0,
		'opex_eur' => isset($item->opex_eur) ? (float) $item->opex_eur : 0,
		'feasibility_score' => isset($item->feasibility_score) ? (int) $item->feasibility_score : null,
		'impact_score' => isset($item->impact_score) ? (int) $item->impact_score : null,
		'uncertainty_gain_low' => isset($item->uncertainty_gain_low) ? (float) $item->uncertainty_gain_low : null,
		'uncertainty_gain_high' => isset($item->uncertainty_gain_high) ? (float) $item->uncertainty_gain_high : null,
		'milestone_date' => isset($item->milestone_date) ? $item->milestone_date : null,
		'roadmap_quarter' => isset($item->roadmap_quarter) ? $item->roadmap_quarter : null,
		'dependencies' => isset($item->dependencies) ? $item->dependencies : null,
		'evidence_done' => isset($item->evidence_done) ? $item->evidence_done : null,
	);
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
	$body = array();
}

if ($method === 'GET') {
	$fk_bilan = (int) ($_GET['fk_bilan'] ?? 0);
	$sql = "SELECT rowid FROM ".$db->prefix()."dolicarbon_action WHERE fk_bilan = ".$fk_bilan." ORDER BY rowid DESC";
	$resql = $db->query($sql);
	$out = array();
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			$item = new DoliCarbonAction($db);
			if ($item->fetch((int) $obj->rowid) > 0) {
				$out[] = dc_action_payload($item);
			}
		}
	}
	dc_json($out);
}

if ($method === 'POST') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$item = new DoliCarbonAction($db);
	$item->fk_bilan = (int) ($body['fk_bilan'] ?? 0);
	$item->label = trim((string) ($body['label'] ?? ''));
	$item->description = trim((string) ($body['description'] ?? ''));
	$item->category = trim((string) ($body['category'] ?? ''));
	$item->gain_tco2e_estimated = (float) ($body['gain_tco2e_estimated'] ?? 0);
	$item->gain_tco2e_actual = (float) ($body['gain_tco2e_actual'] ?? 0);
	$item->cost_eur = (float) ($body['cost_eur'] ?? 0);
	$item->fk_user_responsible = (int) ($body['fk_user_responsible'] ?? 0);
	$item->baseline_tco2e = (float) ($body['baseline_tco2e'] ?? 0);
	$item->target_tco2e = (float) ($body['target_tco2e'] ?? 0);
	$item->capex_eur = (float) ($body['capex_eur'] ?? 0);
	$item->opex_eur = (float) ($body['opex_eur'] ?? 0);
	$item->feasibility_score = isset($body['feasibility_score']) && $body['feasibility_score'] !== '' ? (int) $body['feasibility_score'] : null;
	$item->impact_score = isset($body['impact_score']) && $body['impact_score'] !== '' ? (int) $body['impact_score'] : null;
	$item->uncertainty_gain_low = isset($body['uncertainty_gain_low']) ? (float) $body['uncertainty_gain_low'] : null;
	$item->uncertainty_gain_high = isset($body['uncertainty_gain_high']) ? (float) $body['uncertainty_gain_high'] : null;
	$item->milestone_date = ($body['milestone_date'] ?? '') !== '' ? trim((string) $body['milestone_date']) : null;
	$item->roadmap_quarter = trim((string) ($body['roadmap_quarter'] ?? ''));
	$item->dependencies = trim((string) ($body['dependencies'] ?? ''));
	$item->evidence_done = trim((string) ($body['evidence_done'] ?? ''));
	$res = $item->create($user, 0);
	if ($res <= 0) {
		dc_json(array('error' => 'CREATE_FAILED', 'message' => $item->error), 400);
	}
	dc_json(dc_action_payload($item), 201);
}

if ($method === 'PUT') {
	if (!$user->hasRight('dolicarbon', 'write')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$id = (int) ($body['id'] ?? 0);
	$item = new DoliCarbonAction($db);
	if ($id <= 0 || $item->fetch($id) <= 0) {
		dc_json(array('error' => 'NOT_FOUND'), 404);
	}
	$action = (string) ($body['action'] ?? 'update');
	if ($action === 'mark_done') {
		$res = $item->markDone($user);
	} else {
		$item->label = trim((string) ($body['label'] ?? $item->label));
		$item->description = trim((string) ($body['description'] ?? $item->description));
		$item->category = trim((string) ($body['category'] ?? $item->category));
		$item->gain_tco2e_estimated = (float) ($body['gain_tco2e_estimated'] ?? $item->gain_tco2e_estimated);
		$item->gain_tco2e_actual = (float) ($body['gain_tco2e_actual'] ?? $item->gain_tco2e_actual);
		$item->cost_eur = (float) ($body['cost_eur'] ?? $item->cost_eur);
		$item->status = (int) ($body['status'] ?? $item->status);
		$item->fk_user_responsible = (int) ($body['fk_user_responsible'] ?? $item->fk_user_responsible);
		if (isset($body['baseline_tco2e'])) {
			$item->baseline_tco2e = (float) $body['baseline_tco2e'];
		}
		if (isset($body['target_tco2e'])) {
			$item->target_tco2e = (float) $body['target_tco2e'];
		}
		if (isset($body['capex_eur'])) {
			$item->capex_eur = (float) $body['capex_eur'];
		}
		if (isset($body['opex_eur'])) {
			$item->opex_eur = (float) $body['opex_eur'];
		}
		if (array_key_exists('feasibility_score', $body)) {
			$item->feasibility_score = $body['feasibility_score'] !== '' && $body['feasibility_score'] !== null ? (int) $body['feasibility_score'] : null;
		}
		if (array_key_exists('impact_score', $body)) {
			$item->impact_score = $body['impact_score'] !== '' && $body['impact_score'] !== null ? (int) $body['impact_score'] : null;
		}
		if (isset($body['uncertainty_gain_low'])) {
			$item->uncertainty_gain_low = (float) $body['uncertainty_gain_low'];
		}
		if (isset($body['uncertainty_gain_high'])) {
			$item->uncertainty_gain_high = (float) $body['uncertainty_gain_high'];
		}
		if (array_key_exists('milestone_date', $body)) {
			$item->milestone_date = $body['milestone_date'] !== '' && $body['milestone_date'] !== null ? trim((string) $body['milestone_date']) : null;
		}
		if (isset($body['roadmap_quarter'])) {
			$item->roadmap_quarter = trim((string) $body['roadmap_quarter']);
		}
		if (isset($body['dependencies'])) {
			$item->dependencies = trim((string) $body['dependencies']);
		}
		if (isset($body['evidence_done'])) {
			$item->evidence_done = trim((string) $body['evidence_done']);
		}
		$res = $item->update($user, 0);
	}
	if ($res <= 0) {
		dc_json(array('error' => 'UPDATE_FAILED', 'message' => $item->error), 400);
	}
	$item->fetch($id);
	dc_json(dc_action_payload($item));
}

if ($method === 'DELETE') {
	if (!$user->hasRight('dolicarbon', 'delete')) {
		dc_json(array('error' => 'FORBIDDEN'), 403);
	}
	$id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($body['id'] ?? 0);
	$item = new DoliCarbonAction($db);
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

