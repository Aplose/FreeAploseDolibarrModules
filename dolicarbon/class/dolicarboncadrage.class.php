<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr> */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once __DIR__.'/dolicarbonbilan.class.php';

/**
 * Methodological framing (cadrage) for one bilan — Bilan Carbone expert track.
 */
class DoliCarbonCadrage extends CommonObject
{
	public $module = 'dolicarbon';
	public $element = 'dolicarbon_cadrage';
	public $table_element = 'dolicarbon_cadrage';
	public $picto = 'fa-clipboard-list';

	public $ismultientitymanaged = 1;

	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'position' => 1, 'notnull' => 1, 'visible' => 0, 'noteditable' => 1, 'index' => 1),
		'fk_bilan' => array('type' => 'integer:DoliCarbonBilan:custom/dolicarbon/class/dolicarbonbilan.class.php', 'label' => 'Bilan', 'enabled' => 1, 'position' => 10, 'notnull' => 1, 'visible' => 1, 'index' => 1),
		'entity' => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'position' => 15, 'notnull' => 1, 'visible' => 0, 'default' => '1'),
		'org_perimeter' => array('type' => 'text', 'label' => 'OrgPerimeter', 'enabled' => 1, 'position' => 20, 'notnull' => -1, 'visible' => 1),
		'op_perimeter' => array('type' => 'text', 'label' => 'OpPerimeter', 'enabled' => 1, 'position' => 30, 'notnull' => -1, 'visible' => 1),
		'exclusions' => array('type' => 'text', 'label' => 'Exclusions', 'enabled' => 1, 'position' => 40, 'notnull' => -1, 'visible' => 1),
		'materiality_pct' => array('type' => 'double', 'label' => 'MaterialityPct', 'enabled' => 1, 'position' => 50, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'ref_year' => array('type' => 'integer', 'label' => 'RefYear', 'enabled' => 1, 'position' => 60, 'notnull' => -1, 'visible' => 1),
		'reporting_year' => array('type' => 'integer', 'label' => 'ReportingYear', 'enabled' => 1, 'position' => 70, 'notnull' => -1, 'visible' => 1),
		'completeness_note' => array('type' => 'text', 'label' => 'CompletenessNote', 'enabled' => 1, 'position' => 80, 'notnull' => -1, 'visible' => 1),
		'collection_checklists_json' => array('type' => 'text', 'label' => 'ChecklistsJson', 'enabled' => 1, 'position' => 90, 'notnull' => -1, 'visible' => 0),
		'method_version' => array('type' => 'integer', 'label' => 'MethodVersion', 'enabled' => 1, 'position' => 100, 'notnull' => 1, 'visible' => 1, 'default' => '1'),
		'locked' => array('type' => 'tinyint', 'label' => 'Locked', 'enabled' => 1, 'position' => 110, 'notnull' => 1, 'visible' => 1, 'default' => '0'),
		'note_method' => array('type' => 'text', 'label' => 'NoteMethod', 'enabled' => 1, 'position' => 120, 'notnull' => -1, 'visible' => 1),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => 1, 'position' => 130, 'notnull' => -1, 'visible' => -2),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'position' => 140, 'notnull' => -1, 'visible' => -2),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'position' => 150, 'notnull' => 0, 'visible' => 0),
	);

	public $id;
	public $fk_bilan;
	public $entity;
	public $org_perimeter;
	public $op_perimeter;
	public $exclusions;
	public $materiality_pct;
	public $ref_year;
	public $reporting_year;
	public $completeness_note;
	public $collection_checklists_json;
	public $method_version;
	public $locked;
	public $note_method;
	public $fk_user_creat;
	public $date_creation;
	public $tms;

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	public function create(User $user, $notrigger = 0)
	{
		global $conf;
		if (!isset($this->entity)) {
			$this->entity = $conf->entity;
		}
		$this->fk_user_creat = $user->id;
		if (empty($this->date_creation)) {
			$this->date_creation = dol_now();
		}
		if ($this->locked === null || $this->locked === '') {
			$this->locked = 0;
		}
		if ($this->method_version === null || $this->method_version === '') {
			$this->method_version = 1;
		}
		return $this->createCommon($user, $notrigger);
	}

	public function fetch($id, $ref = null)
	{
		return $this->fetchCommon($id, $ref);
	}

	/**
	 * Load cadrage by bilan id (one-to-one).
	 */
	public function fetchByBilan($fkBilan)
	{
		global $conf;
		$sql = "SELECT rowid FROM ".$this->db->prefix().$this->table_element;
		$sql .= " WHERE fk_bilan = ".((int) $fkBilan)." AND entity = ".((int) $conf->entity);
		$resql = $this->db->query($sql);
		if ($resql && $this->db->num_rows($resql)) {
			$obj = $this->db->fetch_object($resql);
			return $this->fetchCommon((int) $obj->rowid);
		}
		return 0;
	}

	public function update(User $user, $notrigger = 0)
	{
		if (!empty($this->locked)) {
			$this->error = 'CadrageLocked';
			return -1;
		}
		return $this->updateCommon($user, $notrigger);
	}

	public function delete(User $user, $notrigger = 0)
	{
		if (!empty($this->locked)) {
			$this->error = 'CadrageLocked';
			return -1;
		}
		return $this->deleteCommon($user, $notrigger);
	}
}
