<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file        class/dolicarbonfactor.class.php
 * \ingroup     dolicarbon
 * \brief       Emission factor
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for DoliCarbon emission factor
 */
class DoliCarbonFactor extends CommonObject
{
	public $module = 'dolicarbon';
	public $element = 'dolicarbon_factor';
	public $table_element = 'dolicarbon_factor';
	public $picto = 'fa-percent';

	public $ismultientitymanaged = 1;

	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'position' => 1, 'notnull' => 1, 'visible' => 0, 'noteditable' => 1, 'index' => 1),
		'code' => array('type' => 'varchar(50)', 'label' => 'Code', 'enabled' => 1, 'position' => 10, 'notnull' => 1, 'visible' => 1, 'index' => 1),
		'label' => array('type' => 'varchar(255)', 'label' => 'Label', 'enabled' => 1, 'position' => 20, 'notnull' => 1, 'visible' => 1),
		'category' => array('type' => 'varchar(100)', 'label' => 'Category', 'enabled' => 1, 'position' => 30, 'notnull' => -1, 'visible' => 1),
		'scope' => array('type' => 'tinyint', 'label' => 'Scope', 'enabled' => 1, 'position' => 40, 'notnull' => -1, 'visible' => 1),
		'unit_input' => array('type' => 'varchar(30)', 'label' => 'UnitInput', 'enabled' => 1, 'position' => 50, 'notnull' => -1, 'visible' => 1),
		'kgco2e_per_unit' => array('type' => 'double', 'label' => 'Kgco2ePerUnit', 'enabled' => 1, 'position' => 60, 'notnull' => 1, 'visible' => 1),
		'source' => array('type' => 'varchar(100)', 'label' => 'FactorSource', 'enabled' => 1, 'position' => 70, 'notnull' => 0, 'visible' => 1),
		'year_ref' => array('type' => 'integer', 'label' => 'YearRef', 'enabled' => 1, 'position' => 80, 'notnull' => -1, 'visible' => 1),
		'active' => array('type' => 'tinyint', 'label' => 'Active', 'enabled' => 1, 'position' => 90, 'notnull' => 0, 'visible' => 1, 'default' => '1'),
		'entity' => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'position' => 100, 'notnull' => 1, 'visible' => 0, 'default' => '1'),
		'note' => array('type' => 'text', 'label' => 'Note', 'enabled' => 1, 'position' => 110, 'notnull' => -1, 'visible' => 0),
		'version_label' => array('type' => 'varchar(32)', 'label' => 'VersionLabel', 'enabled' => 1, 'position' => 115, 'notnull' => 0, 'visible' => 1, 'default' => '1.0'),
		'valid_from' => array('type' => 'date', 'label' => 'ValidFrom', 'enabled' => 1, 'position' => 116, 'notnull' => -1, 'visible' => 1),
		'valid_to' => array('type' => 'date', 'label' => 'ValidTo', 'enabled' => 1, 'position' => 117, 'notnull' => -1, 'visible' => 1),
		'governance_status' => array('type' => 'varchar(20)', 'label' => 'GovernanceStatus', 'enabled' => 1, 'position' => 118, 'notnull' => 0, 'visible' => 1, 'default' => 'validated'),
		'replacement_note' => array('type' => 'text', 'label' => 'ReplacementNote', 'enabled' => 1, 'position' => 119, 'notnull' => -1, 'visible' => 0),
		'priority_rank' => array('type' => 'integer', 'label' => 'PriorityRank', 'enabled' => 1, 'position' => 120, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'position' => 130, 'notnull' => 0, 'visible' => 0),
	);

	public $id;
	public $code;
	public $label;
	public $category;
	public $scope;
	public $unit_input;
	public $kgco2e_per_unit;
	public $source;
	public $year_ref;
	public $active;
	public $entity;
	public $note;
	public $version_label;
	public $valid_from;
	public $valid_to;
	public $governance_status;
	public $replacement_note;
	public $priority_rank;
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
	 * Create
	 *
	 * @param User $user User
	 * @param int  $notrigger No trigger
	 * @return int
	 */
	public function create(User $user, $notrigger = 0)
	{
		global $conf;
		if (!isset($this->entity)) {
			$this->entity = $conf->entity;
		}
		if (!isset($this->active)) {
			$this->active = 1;
		}
		if ($this->version_label === null || $this->version_label === '') {
			$this->version_label = '1.0';
		}
		if ($this->governance_status === null || $this->governance_status === '') {
			$this->governance_status = 'validated';
		}
		if ($this->priority_rank === null || $this->priority_rank === '') {
			$this->priority_rank = 0;
		}
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Fetch
	 *
	 * @param int    $id   Id
	 * @param string $code Code
	 * @return int
	 */
	public function fetch($id, $code = null)
	{
		if ($id > 0) {
			return $this->fetchCommon($id, $code);
		}
		if ($code !== null && $code !== '') {
			global $conf;
			$sql = "SELECT rowid FROM ".$this->db->prefix().$this->table_element;
			$sql .= " WHERE code = '".$this->db->escape($code)."'";
			$sql .= " AND entity IN (0, ".((int) $conf->entity).")";
			$sql .= " ORDER BY entity DESC";
			$resql = $this->db->query($sql);
			if ($resql && $this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				return $this->fetchCommon((int) $obj->rowid);
			}
			return 0;
		}
		return -1;
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
		return $this->updateCommon($user, $notrigger);
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
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 * Factors by category and scope for current entity
	 *
	 * @param string   $category Category code
	 * @param int|null $scope    Scope filter
	 * @return self[]
	 */
	public function getByCategory($category, $scope = null)
	{
		global $conf;

		$out = array();
		$sql = "SELECT rowid FROM ".$this->db->prefix().$this->table_element;
		$sql .= " WHERE entity IN (0, ".((int) $conf->entity).")";
		$sql .= " AND category = '".$this->db->escape($category)."'";
		$sql .= " AND active = 1";
		if ($scope !== null && $scope !== '') {
			$sql .= " AND scope = ".((int) $scope);
		}
		$sql .= " ORDER BY label";

		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$fac = new self($this->db);
				if ($fac->fetch((int) $obj->rowid) > 0) {
					$out[] = $fac;
				}
			}
		}
		return $out;
	}

	/**
	 * Import CSV (semicolon); columns: code,label,category,scope,unit_input,kgco2e_per_unit,source,year_ref
	 *
	 * @param string $filepath Absolute path
	 * @param User   $user     User
	 * @return int Number of lines imported or -1 on error
	 */
	public function importFromCSV($filepath, User $user)
	{
		global $conf;

		if (!is_readable($filepath)) {
			$this->error = 'ErrorFileNotFound';
			return -1;
		}

		$handle = fopen($filepath, 'rb');
		if (!$handle) {
			return -1;
		}

		$n = 0;
		$lineNum = 0;
		while (($row = fgetcsv($handle, 0, ';')) !== false) {
			$lineNum++;
			if ($lineNum === 1 && isset($row[0]) && stripos($row[0], 'code') !== false) {
				continue;
			}
			if (count($row) < 6) {
				continue;
			}

			$fac = new self($this->db);
			$fac->code = trim($row[0]);
			$fac->label = trim($row[1]);
			$fac->category = trim($row[2]);
			$fac->scope = (int) $row[3];
			$fac->unit_input = trim($row[4]);
			$fac->kgco2e_per_unit = (float) str_replace(',', '.', $row[5]);
			$fac->source = isset($row[6]) ? trim($row[6]) : 'CSV import';
			$fac->year_ref = isset($row[7]) ? (int) $row[7] : null;
			$fac->entity = $conf->entity;
			$fac->active = 1;

			if ($fac->create($user, 1) > 0) {
				$n++;
			}
		}
		fclose($handle);
		return $n;
	}
}
