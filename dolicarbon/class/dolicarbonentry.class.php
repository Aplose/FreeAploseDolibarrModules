<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file        class/dolicarbonentry.class.php
 * \ingroup     dolicarbon
 * \brief       Single carbon activity line
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once __DIR__.'/dolicarbonfactor.class.php';
require_once __DIR__.'/dolicarbonbilan.class.php';

/**
 * Class for DoliCarbon entry
 */
class DoliCarbonEntry extends CommonObject
{
	public $module = 'dolicarbon';
	public $element = 'dolicarbon_entry';
	public $table_element = 'dolicarbon_entry';
	public $picto = 'fa-chart-line';

	public $ismultientitymanaged = 0;

	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'position' => 1, 'notnull' => 1, 'visible' => 0, 'noteditable' => 1, 'index' => 1),
		'fk_bilan' => array('type' => 'integer:DoliCarbonBilan:custom/dolicarbon/class/dolicarbonbilan.class.php', 'label' => 'Bilan', 'enabled' => 1, 'position' => 10, 'notnull' => 1, 'visible' => 1, 'index' => 1),
		'scope' => array('type' => 'tinyint', 'label' => 'Scope', 'enabled' => 1, 'position' => 20, 'notnull' => 1, 'visible' => 1),
		'category' => array('type' => 'varchar(100)', 'label' => 'Category', 'enabled' => 1, 'position' => 30, 'notnull' => 1, 'visible' => 1),
		'label' => array('type' => 'varchar(255)', 'label' => 'Label', 'enabled' => 1, 'position' => 40, 'notnull' => -1, 'visible' => 1),
		'quantity' => array('type' => 'double', 'label' => 'Quantity', 'enabled' => 1, 'position' => 50, 'notnull' => 1, 'visible' => 1, 'default' => '0'),
		'unit' => array('type' => 'varchar(30)', 'label' => 'Unit', 'enabled' => 1, 'position' => 60, 'notnull' => -1, 'visible' => 1),
		'fk_factor' => array('type' => 'integer:DoliCarbonFactor:custom/dolicarbon/class/dolicarbonfactor.class.php', 'label' => 'Factor', 'enabled' => 1, 'position' => 70, 'notnull' => -1, 'visible' => 1),
		'tco2e_computed' => array('type' => 'double', 'label' => 'Tco2eComputed', 'enabled' => 1, 'position' => 80, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'source_type' => array('type' => 'varchar(30)', 'label' => 'SourceType', 'enabled' => 1, 'position' => 90, 'notnull' => 0, 'visible' => 1, 'default' => 'manual'),
		'fk_source_object' => array('type' => 'integer', 'label' => 'FkSourceObject', 'enabled' => 1, 'position' => 100, 'notnull' => -1, 'visible' => 0),
		'source_ref' => array('type' => 'varchar(50)', 'label' => 'SourceRef', 'enabled' => 1, 'position' => 110, 'notnull' => -1, 'visible' => 1),
		'quality_grade' => array('type' => 'varchar(2)', 'label' => 'QualityGrade', 'enabled' => 1, 'position' => 115, 'notnull' => 0, 'visible' => 1, 'default' => 'B'),
		'uncertainty_pct_low' => array('type' => 'double', 'label' => 'UncertaintyLow', 'enabled' => 1, 'position' => 116, 'notnull' => 0, 'visible' => 1, 'default' => '10'),
		'uncertainty_pct_high' => array('type' => 'double', 'label' => 'UncertaintyHigh', 'enabled' => 1, 'position' => 117, 'notnull' => 0, 'visible' => 1, 'default' => '20'),
		'workflow_status' => array('type' => 'varchar(32)', 'label' => 'WorkflowStatus', 'enabled' => 1, 'position' => 118, 'notnull' => 0, 'visible' => 1, 'default' => 'draft'),
		'evidence_ref' => array('type' => 'varchar(255)', 'label' => 'EvidenceRef', 'enabled' => 1, 'position' => 119, 'notnull' => -1, 'visible' => 1),
		'factor_kgco2e_snapshot' => array('type' => 'double', 'label' => 'FactorSnapshotKg', 'enabled' => 1, 'position' => 1195, 'notnull' => -1, 'visible' => 0),
		'calculation_formula' => array('type' => 'varchar(255)', 'label' => 'CalcFormula', 'enabled' => 1, 'position' => 1196, 'notnull' => -1, 'visible' => 0),
		'calculation_fingerprint' => array('type' => 'varchar(128)', 'label' => 'CalcFingerprint', 'enabled' => 1, 'position' => 1197, 'notnull' => -1, 'visible' => 0),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => 1, 'position' => 120, 'notnull' => -1, 'visible' => -2),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'position' => 130, 'notnull' => -1, 'visible' => -2),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'position' => 140, 'notnull' => 0, 'visible' => 0),
	);

	public $id;
	public $fk_bilan;
	public $scope;
	public $category;
	public $label;
	public $quantity;
	public $unit;
	public $fk_factor;
	public $tco2e_computed;
	public $source_type;
	public $fk_source_object;
	public $source_ref;
	public $quality_grade;
	public $uncertainty_pct_low;
	public $uncertainty_pct_high;
	public $workflow_status;
	public $evidence_ref;
	public $factor_kgco2e_snapshot;
	public $calculation_formula;
	public $calculation_fingerprint;
	public $fk_user_creat;
	public $date_creation;
	public $tms;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 * Create entry and compute emission
	 *
	 * @param User $user User
	 * @param int  $notrigger No trigger
	 * @return int
	 */
	public function create(User $user, $notrigger = 0)
	{
		$this->fk_user_creat = $user->id;
		if (empty($this->date_creation)) {
			$this->date_creation = dol_now();
		}
		if (empty($this->source_type)) {
			$this->source_type = 'manual';
		}
		if ($this->quality_grade === null || $this->quality_grade === '') {
			$this->quality_grade = 'B';
		}
		if ($this->workflow_status === null || $this->workflow_status === '') {
			$this->workflow_status = 'draft';
		}
		if ($this->uncertainty_pct_low === null || $this->uncertainty_pct_low === '') {
			$this->uncertainty_pct_low = 10;
		}
		if ($this->uncertainty_pct_high === null || $this->uncertainty_pct_high === '') {
			$this->uncertainty_pct_high = 20;
		}
		$this->computeEmission(false);
		$res = $this->createCommon($user, $notrigger);
		if ($res > 0) {
			$this->refreshBilanTotal();
		}
		return $res;
	}

	/**
	 * Fetch
	 *
	 * @param int    $id Id
	 * @param string $ref Unused
	 * @return int
	 */
	public function fetch($id, $ref = null)
	{
		return $this->fetchCommon($id, $ref);
	}

	/**
	 * Update
	 *
	 * @param User $user User
	 * @param int  $notrigger No trigger
	 * @return int
	 */
	public function update(User $user, $notrigger = 0)
	{
		$this->computeEmission(false);
		$res = $this->updateCommon($user, $notrigger);
		if ($res > 0) {
			$this->refreshBilanTotal();
		}
		return $res;
	}

	/**
	 * Delete
	 *
	 * @param User $user User
	 * @param int  $notrigger No trigger
	 * @return int
	 */
	public function delete(User $user, $notrigger = 0)
	{
		$bilanId = (int) $this->fk_bilan;
		$res = $this->deleteCommon($user, $notrigger);
		if ($res > 0 && $bilanId > 0) {
			$bilan = new DoliCarbonBilan($this->db);
			if ($bilan->fetch($bilanId) > 0) {
				$bilan->computeTotals();
			}
		}
		return $res;
	}

	/**
	 * Compute tCO2e from factor: (quantity * kgco2e_per_unit) / 1000
	 *
	 * @param bool $save If true, update DB (needs rowid)
	 * @return float
	 */
	public function computeEmission($save = true)
	{
		require_once __DIR__.'/services/CarbonCalculationEngineService.php';
		$this->tco2e_computed = 0.0;
		$this->factor_kgco2e_snapshot = null;
		$this->calculation_formula = null;
		$this->calculation_fingerprint = null;

		$engine = new CarbonCalculationEngineService($this->db);
		$ver = $engine->getActiveVersion();
		$formula = isset($ver['rules']['formula']) ? (string) $ver['rules']['formula'] : 'quantity * kgco2e_per_unit / 1000';

		if (!empty($this->fk_factor)) {
			$factor = new DoliCarbonFactor($this->db);
			if ($factor->fetch((int) $this->fk_factor) > 0) {
				$qty = (float) $this->quantity;
				$kg = (float) $factor->kgco2e_per_unit;
				$this->tco2e_computed = ($qty * $kg) / 1000.0;
				$this->factor_kgco2e_snapshot = $kg;
				$this->calculation_formula = $formula;
				$vlabel = isset($factor->version_label) ? (string) $factor->version_label : '1.0';
				$this->calculation_fingerprint = hash('sha256', $ver['code'].'|'.(int) $this->fk_factor.'|'.$vlabel.'|'.$kg.'|'.$qty);
				if (empty($this->unit) && !empty($factor->unit_input)) {
					$this->unit = $factor->unit_input;
				}
			}
		}

		if ($save && !empty($this->id)) {
			$sql = "UPDATE ".$this->db->prefix().$this->table_element;
			$sql .= " SET tco2e_computed = ".((float) $this->tco2e_computed);
			$sql .= ", unit = ".($this->unit ? "'".$this->db->escape($this->unit)."'" : "NULL");
			$sql .= ", factor_kgco2e_snapshot = ".($this->factor_kgco2e_snapshot !== null ? ((float) $this->factor_kgco2e_snapshot) : "NULL");
			$sql .= ", calculation_formula = ".($this->calculation_formula ? "'".$this->db->escape($this->calculation_formula)."'" : "NULL");
			$sql .= ", calculation_fingerprint = ".($this->calculation_fingerprint ? "'".$this->db->escape($this->calculation_fingerprint)."'" : "NULL");
			$sql .= " WHERE rowid = ".((int) $this->id);
			$this->db->query($sql);
		}

		return (float) $this->tco2e_computed;
	}

	/**
	 * Refresh parent bilan total
	 *
	 * @return void
	 */
	protected function refreshBilanTotal()
	{
		$bilan = new DoliCarbonBilan($this->db);
		if ($bilan->fetch((int) $this->fk_bilan) > 0) {
			$bilan->computeTotals();
		}
	}
}
