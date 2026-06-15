<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/dolicarbonaction.class.php';

if (!$user->hasRight('dolicarbon', 'read')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

$fk_bilan = (int) ($_GET['fk_bilan'] ?? 0);
if ($fk_bilan <= 0) {
	dc_json(array('error' => 'MISSING_FK_BILAN'), 400);
}

$sql = "SELECT rowid FROM ".$db->prefix()."dolicarbon_action WHERE fk_bilan = ".$fk_bilan." ORDER BY rowid ASC";
$resql = $db->query($sql);
$macc = array();
$matrix = array();
if ($resql) {
	while ($obj = $db->fetch_object($resql)) {
		$a = new DoliCarbonAction($db);
		if ($a->fetch((int) $obj->rowid) <= 0) {
			continue;
		}
		$gain = (float) $a->gain_tco2e_estimated;
		$cost = (float) $a->cost_eur + (float) $a->capex_eur + (float) $a->opex_eur;
		$costPerT = ($gain > 0.0001) ? ($cost / $gain) : null;
		$macc[] = array(
			'id' => (int) $a->id,
			'label' => $a->label,
			'gain_tco2e_estimated' => $gain,
			'total_cost_eur' => $cost,
			'cost_per_tco2e_eur' => $costPerT,
			'feasibility_score' => $a->feasibility_score !== null ? (int) $a->feasibility_score : null,
			'impact_score' => $a->impact_score !== null ? (int) $a->impact_score : null,
			'roadmap_quarter' => $a->roadmap_quarter,
			'milestone_date' => $a->milestone_date,
		);
		$matrix[] = array(
			'id' => (int) $a->id,
			'label' => $a->label,
			'x' => $a->feasibility_score !== null ? (int) $a->feasibility_score : 0,
			'y' => $a->impact_score !== null ? (int) $a->impact_score : 0,
		);
	}
}

usort($macc, function ($a, $b) {
	$ca = $a['cost_per_tco2e_eur'];
	$cb = $b['cost_per_tco2e_eur'];
	if ($ca === null && $cb === null) {
		return 0;
	}
	if ($ca === null) {
		return 1;
	}
	if ($cb === null) {
		return -1;
	}
	return $ca <=> $cb;
});

dc_json(array('macc' => $macc, 'impact_feasibility' => $matrix));
