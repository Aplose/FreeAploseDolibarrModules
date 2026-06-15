<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file        class/dolicarbonbilan.class.php
 * \ingroup     dolicarbon
 * \brief       Carbon bilan (GHG inventory period)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for DoliCarbon bilan
 */
class DoliCarbonBilan extends CommonObject
{
	/**
	 * @var string Module name.
	 */
	public $module = 'dolicarbon';

	/**
	 * @var string Element for triggers / permissions
	 */
	public $element = 'dolicarbon_bilan';

	/**
	 * @var string Table name without prefix
	 */
	public $table_element = 'dolicarbon_bilan';

	/**
	 * @var string Picto
	 */
	public $picto = 'fa-leaf';

	/**
	 * @var int Multicompany
	 */
	public $ismultientitymanaged = 1;

	public const STATUS_DRAFT = 0;
	public const STATUS_VALIDATED = 1;
	public const STATUS_ARCHIVED = 9;

	/**
	 * @var array<string,array<string,mixed>> Fields
	 */
	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'position' => 1, 'notnull' => 1, 'visible' => 0, 'noteditable' => 1, 'index' => 1),
		'ref' => array('type' => 'varchar(30)', 'label' => 'Ref', 'enabled' => 1, 'position' => 10, 'notnull' => 1, 'visible' => 1, 'index' => 1),
		'label' => array('type' => 'varchar(255)', 'label' => 'Label', 'enabled' => 1, 'position' => 20, 'notnull' => 0, 'visible' => 1),
		'year' => array('type' => 'integer', 'label' => 'Year', 'enabled' => 1, 'position' => 30, 'notnull' => 1, 'visible' => 1),
		'date_start' => array('type' => 'date', 'label' => 'DateStart', 'enabled' => 1, 'position' => 40, 'notnull' => -1, 'visible' => 1),
		'date_end' => array('type' => 'date', 'label' => 'DateEnd', 'enabled' => 1, 'position' => 50, 'notnull' => -1, 'visible' => 1),
		'entity' => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'position' => 60, 'notnull' => 1, 'visible' => 0, 'default' => '1'),
		'fk_soc' => array('type' => 'integer:Societe:societe/class/societe.class.php', 'label' => 'ThirdParty', 'enabled' => 'isModEnabled("societe")', 'position' => 70, 'notnull' => -1, 'visible' => 1),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => 1, 'position' => 80, 'notnull' => -1, 'visible' => -2),
		'fk_user_modif' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'enabled' => 1, 'position' => 90, 'notnull' => -1, 'visible' => -2),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'position' => 100, 'notnull' => -1, 'visible' => -2),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'position' => 110, 'notnull' => 0, 'visible' => 0),
		'status' => array('type' => 'smallint', 'label' => 'Status', 'enabled' => 1, 'position' => 120, 'notnull' => 1, 'visible' => 1, 'default' => '0'),
		'total_tco2e' => array('type' => 'double', 'label' => 'TotalTco2e', 'enabled' => 1, 'position' => 130, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'target_tco2e' => array('type' => 'double', 'label' => 'TargetTco2e', 'enabled' => 1, 'position' => 140, 'notnull' => -1, 'visible' => 1),
		'note_public' => array('type' => 'text', 'label' => 'NotePublic', 'enabled' => 1, 'position' => 150, 'notnull' => -1, 'visible' => 0),
		'note_private' => array('type' => 'text', 'label' => 'NotePrivate', 'enabled' => 1, 'position' => 160, 'notnull' => -1, 'visible' => 0),
	);

	public $id;
	public $ref;
	public $label;
	public $year;
	public $date_start;
	public $date_end;
	public $entity;
	public $fk_soc;
	public $fk_user_creat;
	public $fk_user_modif;
	public $date_creation;
	public $tms;
	public $status;
	public $total_tco2e;
	public $target_tco2e;
	public $note_public;
	public $note_private;

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

		if (empty($this->ref)) {
			$this->ref = $this->getNextRef();
		}
		$this->entity = (isset($this->entity) ? $this->entity : $conf->entity);
		if (empty($this->date_creation)) {
			$this->date_creation = dol_now();
		}
		$this->fk_user_creat = $user->id;
		$this->status = self::STATUS_DRAFT;
		$this->total_tco2e = (float) $this->total_tco2e;

		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Generate next ref CARBON-{year}-{seq}
	 *
	 * @return string
	 */
	public function getNextRef()
	{
		global $conf;

		$year = (int) $this->year;
		if ($year <= 0) {
			$year = (int) dol_print_date(dol_now(), '%Y');
		}

		$sql = "SELECT MAX(CAST(SUBSTRING_INDEX(ref, '-', -1) AS UNSIGNED)) as maxseq";
		$sql .= " FROM ".$this->db->prefix().$this->table_element;
		$sql .= " WHERE entity = ".((int) $conf->entity);
		$sql .= " AND ref LIKE 'CARBON-".$this->db->escape($year)."-%'";

		$resql = $this->db->query($sql);
		$seq = 0;
		if ($resql && $this->db->num_rows($resql)) {
			$obj = $this->db->fetch_object($resql);
			$seq = (int) $obj->maxseq;
		}
		$seq++;
		return 'CARBON-'.$year.'-'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
	}

	/**
	 * Load from database
	 *
	 * @param int    $id   Id
	 * @param string $ref  Ref
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
		$this->fk_user_modif = $user->id;
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete bilan and related entries/actions
	 *
	 * @param User $user User
	 * @param int  $notrigger No trigger
	 * @return int
	 */
	public function delete(User $user, $notrigger = 0)
	{
		$id = (int) $this->id;
		if ($id <= 0) {
			return -1;
		}

		$this->db->begin();

		$sql = "DELETE FROM ".$this->db->prefix()."dolicarbon_entry WHERE fk_bilan = ".$id;
		if (!$this->db->query($sql)) {
			$this->db->rollback();
			return -1;
		}
		$sql = "DELETE FROM ".$this->db->prefix()."dolicarbon_action WHERE fk_bilan = ".$id;
		if (!$this->db->query($sql)) {
			$this->db->rollback();
			return -1;
		}

		$res = $this->deleteCommon($user, $notrigger);
		if ($res <= 0) {
			$this->db->rollback();
			return $res;
		}

		$this->db->commit();
		return 1;
	}

	/**
	 * Validate bilan
	 *
	 * @param User $user User
	 * @return int
	 */
	public function validateBilan(User $user)
	{
		$this->computeTotals();
		$this->status = self::STATUS_VALIDATED;
		return $this->update($user, 1);
	}

	/**
	 * Archive bilan
	 *
	 * @param User $user User
	 * @return int
	 */
	public function archiveBilan(User $user)
	{
		$this->status = self::STATUS_ARCHIVED;
		return $this->update($user, 1);
	}

	/**
	 * Recompute total_tco2e from entries
	 *
	 * @return float Total tCO2e
	 */
	public function computeTotals()
	{
		$id = (int) $this->id;
		if ($id <= 0) {
			return 0.0;
		}

		$sql = "SELECT SUM(tco2e_computed) as total FROM ".$this->db->prefix()."dolicarbon_entry";
		$sql .= " WHERE fk_bilan = ".$id;

		$resql = $this->db->query($sql);
		$total = 0.0;
		if ($resql && ($obj = $this->db->fetch_object($resql))) {
			$total = (float) $obj->total;
		}

		$sql = "UPDATE ".$this->db->prefix().$this->table_element;
		$sql .= " SET total_tco2e = ".((float) $total);
		$sql .= " WHERE rowid = ".$id;
		$this->db->query($sql);
		$this->total_tco2e = $total;
		return $total;
	}

	/**
	 * Entries for a scope
	 *
	 * @param int $scope 1|2|3
	 * @return DoliCarbonEntry[]
	 */
	public function getEntriesByScope($scope)
	{
		require_once __DIR__.'/dolicarbonentry.class.php';

		$out = array();
		$sql = "SELECT rowid FROM ".$this->db->prefix()."dolicarbon_entry";
		$sql .= " WHERE fk_bilan = ".((int) $this->id)." AND scope = ".((int) $scope);

		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$line = new DoliCarbonEntry($this->db);
				if ($line->fetch($obj->rowid) > 0) {
					$out[] = $line;
				}
			}
		}
		return $out;
	}

	/**
	 * Get label for status
	 *
	 * @param int  $status Status code
	 * @param int  $mode   0=label, 1=badge
	 * @return string
	 */
	public function LibStatut($status, $mode = 0)
	{
		global $langs;
		$langs->load('dolicarbon@dolicarbon');

		if ($status == self::STATUS_VALIDATED) {
			return ($mode == 1) ? $langs->transnoentitiesnoconv('Validated') : $langs->trans('Validated');
		}
		if ($status == self::STATUS_ARCHIVED) {
			return ($mode == 1) ? $langs->transnoentitiesnoconv('Archived') : $langs->trans('Archived');
		}
		return ($mode == 1) ? $langs->transnoentitiesnoconv('Draft') : $langs->trans('Draft');
	}

	/**
	 * Link to bilan card
	 *
	 * @param int    $withpicto With picto
	 * @param string $option    Option
	 * @param int    $notooltip No tooltip
	 * @param string $morecss   CSS
	 * @param int    $save_lastsearch_value Save
	 * @param int    $id        Id override
	 * @param string $ref       Ref override
	 * @return string
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1, $id = 0, $ref = '')
	{
		global $langs;

		if (empty($id) && !empty($this->id)) {
			$id = $this->id;
		}
		if ($ref === '' && !empty($this->ref)) {
			$ref = $this->ref;
		}

		$url = dol_buildpath('/dolicarbon/carbon_bilan_card.php', 1).'?id='.((int) $id);
		$label = $langs->trans('Show').': '.$ref;

		$linkstart = '<a href="'.$url.'" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
		$linkend = '</a>';

		$result = $linkstart;
		if ($withpicto) {
			$result .= img_object(($notooltip ? '' : $label), $this->picto, ($notooltip ? (($morecss ? 'class="'.$morecss.'"' : '')) : 'class="'.($morecss ? $morecss.' ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		}
		$result .= ($withpicto ? ' ' : '').$ref;
		$result .= $linkend;

		return $result;
	}
}
