<?php

require_once __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../class/services/CarbonImportService.php';
require_once __DIR__.'/../class/dolicarbonentry.class.php';
require_once __DIR__.'/../lib/dolicarbon.lib.php';

if (!$user->hasRight('dolicarbon', 'write')) {
	dc_json(array('error' => 'FORBIDDEN'), 403);
}

$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
	$body = array();
}

$service = new CarbonImportService($db);
$action = (string) ($_GET['action'] ?? $body['action'] ?? 'check');

if ($action === 'check') {
	$sourceType = (string) ($body['source_type'] ?? 'manual');
	$sourceId = (int) ($body['source_id'] ?? 0);
	$factorId = (int) ($body['factor_id'] ?? 0);
	$qty = (float) ($body['quantity'] ?? 0);
	$importHash = $service->computeImportHash($sourceType, $sourceId, $factorId, $qty);
	dc_json(array(
		'import_hash' => $importHash,
		'already_imported' => $service->hasAlreadyImported($importHash),
	));
}

if ($action === 'preview') {
	$fkBilan = (int) ($_GET['fk_bilan'] ?? $body['fk_bilan'] ?? 0);
	$dateStart = (string) ($_GET['date_start'] ?? $body['date_start'] ?? '');
	$dateEnd = (string) ($_GET['date_end'] ?? $body['date_end'] ?? '');
	$sql = "SELECT f.rowid, f.ref, f.total_ht as amount, f.fk_soc";
	$sql .= " FROM ".$db->prefix()."facture_fourn as f";
	$sql .= " WHERE f.fk_statut > 0";
	if ($dateStart !== '') {
		$sql .= " AND f.datef >= '".$db->idate(strtotime($dateStart))."'";
	}
	if ($dateEnd !== '') {
		$sql .= " AND f.datef <= '".$db->idate(strtotime($dateEnd.' 23:59:59'))."'";
	}
	$sql .= " ORDER BY f.datef DESC LIMIT 200";
	$resql = $db->query($sql);
	$rows = array();
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			$fkFactor = dolicarbon_import_map_get_factor($db, (int) $obj->fk_soc, 'purchases_services');
			$rows[] = array(
				'source_id' => (int) $obj->rowid,
				'source_ref' => $obj->ref,
				'fk_bilan' => $fkBilan,
				'fk_factor' => $fkFactor,
				'quantity' => (float) $obj->amount,
				'category' => 'purchases_services',
				'scope' => 3,
			);
		}
	}
	dc_json(array('items' => $rows));
}

if ($action === 'confirm') {
	$items = isset($body['items']) && is_array($body['items']) ? $body['items'] : array();
	$created = 0;
	$skipped = 0;
	foreach ($items as $line) {
		$sourceType = 'supplier_invoice';
		$sourceId = (int) ($line['source_id'] ?? 0);
		$factorId = (int) ($line['fk_factor'] ?? 0);
		$qty = (float) ($line['quantity'] ?? 0);
		$importHash = $service->computeImportHash($sourceType, $sourceId, $factorId, $qty);
		if ($service->hasAlreadyImported($importHash)) {
			$skipped++;
			continue;
		}
		$entry = new DoliCarbonEntry($db);
		$entry->fk_bilan = (int) ($line['fk_bilan'] ?? 0);
		$entry->scope = (int) ($line['scope'] ?? 3);
		$entry->category = (string) ($line['category'] ?? 'purchases_services');
		$entry->label = (string) ($line['source_ref'] ?? ('INV-'.$sourceId));
		$entry->quantity = $qty;
		$entry->unit = 'EUR';
		$entry->fk_factor = $factorId;
		$entry->source_type = 'invoice';
		$entry->fk_source_object = $sourceId;
		$entry->source_ref = (string) ($line['source_ref'] ?? '');
		if ($entry->create($user, 0) > 0) {
			$sql = "UPDATE ".$db->prefix()."dolicarbon_entry SET import_hash = '".$db->escape($importHash)."' WHERE rowid = ".((int) $entry->id);
			$db->query($sql);
			$created++;
		}
	}
	dc_json(array('created' => $created, 'skipped' => $skipped));
}

if ($action === 'mappings') {
	if (strtoupper($_SERVER['REQUEST_METHOD']) === 'GET') {
		$category = (string) ($_GET['category'] ?? 'purchases_services');
		$sql = "SELECT rowid, fk_soc, category, fk_factor FROM ".$db->prefix()."dolicarbon_import_map WHERE category = '".$db->escape($category)."' ORDER BY rowid DESC";
		$resql = $db->query($sql);
		$out = array();
		if ($resql) {
			while ($obj = $db->fetch_object($resql)) {
				$out[] = $obj;
			}
		}
		dc_json(array('items' => $out));
	}
	$fkSoc = (int) ($body['fk_soc'] ?? 0);
	$category = (string) ($body['category'] ?? 'purchases_services');
	$fkFactor = (int) ($body['fk_factor'] ?? 0);
	$res = dolicarbon_import_map_save($db, $fkSoc, $category, $fkFactor);
	if ($res < 0) {
		dc_json(array('error' => 'SAVE_FAILED'), 400);
	}
	dc_json(array('success' => true));
}

dc_json(array('error' => 'METHOD_NOT_ALLOWED'), 405);

