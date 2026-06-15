<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/dolicarbonbilan.class.php';
require_once __DIR__.'/../class/dolicarbonentry.class.php';
require_once __DIR__.'/../class/dolicarbonaction.class.php';

// Super-admin flag (llx_user.admin); distinct from “tous les droits” sur un module.
if (!(int) $user->admin) {
	dc_json(array('error' => 'FORBIDDEN', 'reason' => 'ADMIN_ONLY', 'hint' => 'Requires user flag Administrator in Dolibarr user card (llx_user.admin=1).'), 403);
}

$action = (string) ($_GET['action'] ?? 'generate_2y');
$batch = (string) ($_GET['batch'] ?? '');
if ($batch === '') {
	$batch = 'SEED_'.date('Ymd_His');
}

/** @var array<int,array{s:int,cat:string,lbl:string}> Codes alignés sur dolicarbon_get_category_map() */
$rowsdef = array(
	array('s' => 1, 'cat' => 'energy_combustion', 'lbl' => 'Combustion (énergie)'),
	array('s' => 2, 'cat' => 'electricity', 'lbl' => 'Électricité'),
	array('s' => 3, 'cat' => 'purchases_services', 'lbl' => 'Achats services'),
);

if ($action === 'generate_2y') {
	$currentYear = (int) dol_print_date(dol_now(), '%Y');
	$years = array($currentYear - 1, $currentYear);
	$createdBilans = 0;
	$createdEntries = 0;
	$createdActions = 0;
	/** @var array<int,array{label:string,category:string,status:int,description:string,gain:float,cost:float,capex:float,opex:float,base:float,target:float,feas:int|null,imp:int|null,quarter:string}> */
	$actionDefs = array(
		array(
			'label' => 'Chaudière biomasse (démo)',
			'category' => 'energy_combustion',
			'status' => DoliCarbonAction::STATUS_PLANNED,
			'description' => '<p>Remplacement gaz par biomasse sur site principal.</p>',
			'gain' => 12.5,
			'cost' => 45000,
			'capex' => 40000,
			'opex' => 5000,
			'base' => 45,
			'target' => 32,
			'feas' => 3,
			'imp' => 4,
			'quarter' => '2026-Q2',
		),
		array(
			'label' => 'Électricité verte (démo)',
			'category' => 'electricity',
			'status' => DoliCarbonAction::STATUS_IN_PROGRESS,
			'description' => '<p>Contrat fournisseur avec garantie d’origine.</p>',
			'gain' => 8.2,
			'cost' => 1200,
			'capex' => 0,
			'opex' => 1200,
			'base' => 28,
			'target' => 20,
			'feas' => 5,
			'imp' => 3,
			'quarter' => '2026-Q1',
		),
		array(
			'label' => 'Achats services responsables (démo)',
			'category' => 'purchases_services',
			'status' => DoliCarbonAction::STATUS_DONE,
			'description' => '<p>Clause carbone dans appels d’offres prestataires.</p>',
			'gain' => 3.1,
			'cost' => 0,
			'capex' => 0,
			'opex' => 0,
			'base' => 15,
			'target' => 12,
			'feas' => 4,
			'imp' => 2,
			'quarter' => '2025-Q4',
		),
		array(
			'label' => 'Optimisation déchets (démo)',
			'category' => 'waste',
			'status' => DoliCarbonAction::STATUS_PLANNED,
			'description' => '<p>Tri à la source et filière recyclage.</p>',
			'gain' => 2.4,
			'cost' => 8000,
			'capex' => 6000,
			'opex' => 2000,
			'base' => 6,
			'target' => 4,
			'feas' => 4,
			'imp' => 3,
			'quarter' => '2026-Q3',
		),
		array(
			'label' => 'Green IT (démo)',
			'category' => 'digital',
			'status' => DoliCarbonAction::STATUS_IN_PROGRESS,
			'description' => '<p>Allongement durée de vie postes et cloud bas carbone.</p>',
			'gain' => 1.8,
			'cost' => 3500,
			'capex' => 2000,
			'opex' => 1500,
			'base' => 5,
			'target' => 3,
			'feas' => 5,
			'imp' => 2,
			'quarter' => '2026-Q1',
		),
		array(
			'label' => 'Transport amont mutualisé (démo)',
			'category' => 'transport_upstream',
			'status' => DoliCarbonAction::STATUS_DONE,
			'description' => '<p>Regroupement livraisons fournisseurs régionaux.</p>',
			'gain' => 4.6,
			'cost' => 15000,
			'capex' => 0,
			'opex' => 15000,
			'base' => 22,
			'target' => 17,
			'feas' => 3,
			'imp' => 4,
			'quarter' => '2025-Q3',
		),
	);
	foreach ($years as $year) {
		$bilan = new DoliCarbonBilan($db);
		$bilan->label = 'Fictional '.$year;
		$bilan->year = $year;
		$bilan->target_tco2e = 85;
		if ($bilan->create($user, 0) <= 0) {
			continue;
		}
		$createdBilans++;
		$sqlb = "UPDATE ".$db->prefix()."dolicarbon_bilan SET is_fictional = 1, seed_batch = '".$db->escape($batch)."' WHERE rowid = ".((int) $bilan->id);
		$db->query($sqlb);

		for ($m = 1; $m <= 12; $m++) {
			foreach ($rowsdef as $idx => $rd) {
				$entry = new DoliCarbonEntry($db);
				$entry->fk_bilan = (int) $bilan->id;
				$entry->scope = (int) $rd['s'];
				$entry->category = $rd['cat'];
				$entry->label = $rd['lbl'].' — '.$year.'-'.sprintf('%02d', $m);
				$entry->quantity = 1000 + ($m * 15) + ($idx * 120);
				$entry->unit = 'activity_unit';
				$entry->fk_factor = 0;
				if ($entry->create($user, 0) <= 0) {
					continue;
				}
				// create() + computeEmission zeroes tCO2e when fk_factor = 0 — set realistic demo values.
				$base = 14 + ($m * 0.55) + ($year === $currentYear ? -1.5 : 2);
				$tco2e = $base * (0.18 + $idx * 0.32) + sin($m / 6 * M_PI) * (1 + $idx) * 0.55;
				$tco2e = round($tco2e, 4);
				$day = 8 + $idx * 6;
				$hour = 9 + $idx * 2;
				$ts = dol_mktime($hour, 0, 0, $m, $day, $year);
				$sql = "UPDATE ".$db->prefix()."dolicarbon_entry SET tco2e_computed = ".((float) $tco2e);
				$sql .= ", is_fictional = 1, seed_batch = '".$db->escape($batch)."'";
				$sql .= ", date_creation = '".$db->idate($ts)."'";
				$sql .= " WHERE rowid = ".((int) $entry->id);
				$db->query($sql);
				$createdEntries++;
			}
		}
		$bilan->computeTotals();

		foreach ($actionDefs as $ad) {
			$act = new DoliCarbonAction($db);
			$act->fk_bilan = (int) $bilan->id;
			$act->label = $ad['label'].' '.$year;
			$act->category = $ad['category'];
			$act->description = $ad['description'];
			$act->status = (int) $ad['status'];
			$act->gain_tco2e_estimated = (float) $ad['gain'];
			$act->gain_tco2e_actual = ($ad['status'] === DoliCarbonAction::STATUS_DONE) ? (float) $ad['gain'] * 0.92 : 0;
			$act->cost_eur = (float) $ad['cost'];
			$act->capex_eur = (float) $ad['capex'];
			$act->opex_eur = (float) $ad['opex'];
			$act->baseline_tco2e = (float) $ad['base'];
			$act->target_tco2e = (float) $ad['target'];
			$act->feasibility_score = $ad['feas'];
			$act->impact_score = $ad['imp'];
			$act->roadmap_quarter = $ad['quarter'];
			if ($act->create($user, 0) > 0) {
				$sqla = "UPDATE ".$db->prefix()."dolicarbon_action SET is_fictional = 1, seed_batch = '".$db->escape($batch)."'";
				$sqla .= " WHERE rowid = ".((int) $act->id);
				$db->query($sqla);
				$createdActions++;
			}
		}
	}
	dc_json(array('success' => true, 'batch' => $batch, 'created_bilans' => $createdBilans, 'created_entries' => $createdEntries, 'created_actions' => $createdActions));
}

if ($action === 'purge_2y') {
	$bilanIds = array();
	$sql = "SELECT DISTINCT fk_bilan as id FROM ".$db->prefix()."dolicarbon_entry WHERE is_fictional = 1";
	if ($batch !== '') {
		$sql .= " AND seed_batch = '".$db->escape($batch)."'";
	}
	$resql = $db->query($sql);
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			$bilanIds[] = (int) $obj->id;
		}
	}

	$sql = "SELECT rowid as id FROM ".$db->prefix()."dolicarbon_bilan WHERE is_fictional = 1";
	if ($batch !== '') {
		$sql .= " AND seed_batch = '".$db->escape($batch)."'";
	}
	$resql = $db->query($sql);
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			$bilanIds[] = (int) $obj->id;
		}
	}
	$bilanIds = array_values(array_unique(array_filter($bilanIds)));

	$sql = "DELETE FROM ".$db->prefix()."dolicarbon_entry WHERE is_fictional = 1";
	if ($batch !== '') {
		$sql .= " AND seed_batch = '".$db->escape($batch)."'";
	}
	$db->query($sql);
	$sql = "DELETE FROM ".$db->prefix()."dolicarbon_action WHERE is_fictional = 1";
	if ($batch !== '') {
		$sql .= " AND seed_batch = '".$db->escape($batch)."'";
	}
	$db->query($sql);
	if (!empty($bilanIds)) {
		$ids = implode(',', array_map('intval', $bilanIds));
		$db->query("DELETE FROM ".$db->prefix()."dolicarbon_workflow_comment WHERE fk_bilan IN (".$ids.")");
		$db->query("DELETE FROM ".$db->prefix()."dolicarbon_snapshot WHERE fk_bilan IN (".$ids.")");
		$db->query("DELETE FROM ".$db->prefix()."dolicarbon_cadrage WHERE fk_bilan IN (".$ids.")");
		$db->query("DELETE FROM ".$db->prefix()."dolicarbon_action WHERE fk_bilan IN (".$ids.")");
		$db->query("DELETE FROM ".$db->prefix()."dolicarbon_entry WHERE fk_bilan IN (".$ids.")");
		$db->query("DELETE FROM ".$db->prefix()."dolicarbon_bilan WHERE rowid IN (".$ids.")");
	} else {
		$sql = "DELETE FROM ".$db->prefix()."dolicarbon_bilan WHERE is_fictional = 1";
		if ($batch !== '') {
			$sql .= " AND seed_batch = '".$db->escape($batch)."'";
		}
		$db->query($sql);
	}
	dc_json(array('success' => true, 'batch' => $batch, 'deleted_bilans' => count($bilanIds)));
}

dc_json(array('error' => 'METHOD_NOT_ALLOWED'), 405);
