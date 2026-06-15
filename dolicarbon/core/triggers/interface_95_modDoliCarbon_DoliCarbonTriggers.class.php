<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    core/triggers/interface_95_modDoliCarbon_DoliCarbonTriggers.class.php
 * \ingroup dolicarbon
 * \brief   DoliCarbon triggers (optional import hints)
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';
dol_include_once('/dolicarbon/class/dolicarbonentry.class.php');
dol_include_once('/dolicarbon/class/dolicarbonfactor.class.php');
dol_include_once('/dolicarbon/class/dolicarbonbilan.class.php');
dol_include_once('/dolicarbon/class/services/CarbonImportService.php');

/**
 * Triggers for DoliCarbon
 */
class InterfaceDoliCarbonTriggers extends DolibarrTriggers
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database
	 */
	public function __construct($db)
	{
		$this->db = $db;
		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = 'technic';
		$this->description = 'DoliCarbon triggers';
		$this->version = self::VERSIONS['dev'];
		$this->picto = 'fa-leaf';
	}

	/**
	 * Run trigger
	 *
	 * @param string       $action Action code
	 * @param CommonObject $object Object
	 * @param User         $user   User
	 * @param Translate    $langs  Lang
	 * @param Conf         $conf   Conf
	 * @return int
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (empty($conf->dolicarbon) || empty($conf->dolicarbon->enabled)) {
			return 0;
		}

		if (!getDolGlobalInt('DOLICARBON_TRIGGER_NOTIFY')) {
			return 0;
		}

		$langs->load('dolicarbon@dolicarbon');

		switch ($action) {
			case 'BILL_SUPPLIER_VALIDATE':
				if (getDolGlobalInt('DOLICARBON_AUTO_IMPORT_SUPPLIER_INVOICE')) {
					$this->autoImportSupplierInvoice($object, $user, $langs, $conf);
				}
				$url = dol_buildpath('/dolicarbon/carbon_import.php', 1);
				setEventMessages($langs->trans('DOLICARBON_TriggerSupplierInvoice', $url), null, 'mesgs');
				break;

			case 'EXPENSE_REPORT_VALIDATE':
				setEventMessages($langs->trans('DOLICARBON_TriggerExpenseHint'), null, 'mesgs');
				break;

			case 'SHIPPING_VALIDATE':
				setEventMessages($langs->trans('DOLICARBON_TriggerShippingHint'), null, 'mesgs');
				break;

			default:
				break;
		}

		return 0;
	}

	private function autoImportSupplierInvoice($object, User $user, Translate $langs, Conf $conf)
	{
		if (empty($object->id) || empty($object->total_ht)) {
			return;
		}

		$sql = "SELECT rowid FROM ".$this->db->prefix()."dolicarbon_bilan";
		$sql .= " WHERE entity = ".((int) $conf->entity)." AND status = ".DoliCarbonBilan::STATUS_DRAFT;
		$sql .= " ORDER BY year DESC, rowid DESC LIMIT 1";
		$resql = $this->db->query($sql);
		if (!$resql || !($b = $this->db->fetch_object($resql))) {
			return;
		}
		$bilanId = (int) $b->rowid;

		$factorId = 0;
		$sql = "SELECT rowid FROM ".$this->db->prefix()."dolicarbon_factor";
		$sql .= " WHERE category = 'purchases_services' AND scope = 3 AND active = 1";
		$sql .= " AND entity IN (0, ".((int) $conf->entity).") ORDER BY entity DESC, rowid ASC LIMIT 1";
		$resql = $this->db->query($sql);
		if ($resql && ($f = $this->db->fetch_object($resql))) {
			$factorId = (int) $f->rowid;
		}
		if ($factorId <= 0) {
			return;
		}

		$importService = new CarbonImportService($this->db);
		$hash = $importService->computeImportHash('supplier_invoice', (int) $object->id, $factorId, (float) $object->total_ht);
		if ($importService->hasAlreadyImported($hash)) {
			return;
		}

		$entry = new DoliCarbonEntry($this->db);
		$entry->fk_bilan = $bilanId;
		$entry->scope = 3;
		$entry->category = 'purchases_services';
		$entry->label = (string) $object->ref;
		$entry->quantity = (float) $object->total_ht;
		$entry->unit = 'EUR';
		$entry->fk_factor = $factorId;
		$entry->source_type = 'invoice';
		$entry->fk_source_object = (int) $object->id;
		$entry->source_ref = (string) $object->ref;
		$entry->create($user, 1);

		$sql = "UPDATE ".$this->db->prefix()."dolicarbon_entry SET import_hash = '".$this->db->escape($hash)."' WHERE rowid = ".((int) $entry->id);
		$this->db->query($sql);
	}
}
