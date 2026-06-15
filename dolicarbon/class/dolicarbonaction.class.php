<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file        class/dolicarbonaction.class.php
 * \ingroup     dolicarbon
 * \brief       Reduction action linked to a bilan
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once __DIR__.'/dolicarbonbilan.class.php';

/**
 * Class for DoliCarbon reduction action
 */
class DoliCarbonAction extends CommonObject
{
	public $module = 'dolicarbon';
	public $element = 'dolicarbon_action';
	public $table_element = 'dolicarbon_action';
	public $picto = 'fa-tasks';

	public $ismultientitymanaged = 0;

	public const STATUS_PLANNED = 0;
	public const STATUS_IN_PROGRESS = 1;
	public const STATUS_DONE = 2;

	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'position' => 1, 'notnull' => 1, 'visible' => 0, 'noteditable' => 1, 'index' => 1),
		'fk_bilan' => array('type' => 'integer:DoliCarbonBilan:custom/dolicarbon/class/dolicarbonbilan.class.php', 'label' => 'Bilan', 'enabled' => 1, 'position' => 10, 'notnull' => 1, 'visible' => 1, 'index' => 1),
		'label' => array('type' => 'varchar(255)', 'label' => 'Label', 'enabled' => 1, 'position' => 20, 'notnull' => 1, 'visible' => 1),
		'description' => array('type' => 'text', 'label' => 'Description', 'enabled' => 1, 'position' => 30, 'notnull' => -1, 'visible' => 1),
		'category' => array('type' => 'varchar(100)', 'label' => 'Category', 'enabled' => 1, 'position' => 40, 'notnull' => -1, 'visible' => 1),
		'gain_tco2e_estimated' => array('type' => 'double', 'label' => 'GainEstimated', 'enabled' => 1, 'position' => 50, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'gain_tco2e_actual' => array('type' => 'double', 'label' => 'GainActual', 'enabled' => 1, 'position' => 60, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'cost_eur' => array('type' => 'double', 'label' => 'CostEur', 'enabled' => 1, 'position' => 70, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'fk_user_responsible' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'Responsible', 'enabled' => 1, 'position' => 80, 'notnull' => -1, 'visible' => 1),
		'date_deadline' => array('type' => 'date', 'label' => 'Deadline', 'enabled' => 1, 'position' => 90, 'notnull' => -1, 'visible' => 1),
		'date_done' => array('type' => 'date', 'label' => 'DateDone', 'enabled' => 1, 'position' => 100, 'notnull' => -1, 'visible' => 1),
		'status' => array('type' => 'tinyint', 'label' => 'Status', 'enabled' => 1, 'position' => 110, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'baseline_tco2e' => array('type' => 'double', 'label' => 'BaselineTco2e', 'enabled' => 1, 'position' => 111, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'target_tco2e' => array('type' => 'double', 'label' => 'TargetTco2e', 'enabled' => 1, 'position' => 112, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'capex_eur' => array('type' => 'double', 'label' => 'CapexEur', 'enabled' => 1, 'position' => 113, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'opex_eur' => array('type' => 'double', 'label' => 'OpexEur', 'enabled' => 1, 'position' => 114, 'notnull' => 0, 'visible' => 1, 'default' => '0'),
		'feasibility_score' => array('type' => 'tinyint', 'label' => 'FeasibilityScore', 'enabled' => 1, 'position' => 115, 'notnull' => -1, 'visible' => 1),
		'impact_score' => array('type' => 'tinyint', 'label' => 'ImpactScore', 'enabled' => 1, 'position' => 116, 'notnull' => -1, 'visible' => 1),
		'uncertainty_gain_low' => array('type' => 'double', 'label' => 'UncGainLow', 'enabled' => 1, 'position' => 117, 'notnull' => -1, 'visible' => 0),
		'uncertainty_gain_high' => array('type' => 'double', 'label' => 'UncGainHigh', 'enabled' => 1, 'position' => 118, 'notnull' => -1, 'visible' => 0),
		'milestone_date' => array('type' => 'date', 'label' => 'MilestoneDate', 'enabled' => 1, 'position' => 119, 'notnull' => -1, 'visible' => 1),
		'roadmap_quarter' => array('type' => 'varchar(15)', 'label' => 'RoadmapQuarter', 'enabled' => 1, 'position' => 120, 'notnull' => -1, 'visible' => 1),
		'dependencies' => array('type' => 'text', 'label' => 'Dependencies', 'enabled' => 1, 'position' => 121, 'notnull' => -1, 'visible' => 1),
		'evidence_done' => array('type' => 'text', 'label' => 'EvidenceDone', 'enabled' => 1, 'position' => 122, 'notnull' => -1, 'visible' => 1),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => 1, 'position' => 130, 'notnull' => -1, 'visible' => -2),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'position' => 140, 'notnull' => -1, 'visible' => -2),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'position' => 150, 'notnull' => 0, 'visible' => 0),
	);

	public $id;
	public $fk_bilan;
	public $label;
	public $description;
	public $category;
	public $gain_tco2e_estimated;
	public $gain_tco2e_actual;
	public $cost_eur;
	public $fk_user_responsible;
	public $date_deadline;
	public $date_done;
	public $status;
	public $baseline_tco2e;
	public $target_tco2e;
	public $capex_eur;
	public $opex_eur;
	public $feasibility_score;
	public $impact_score;
	public $uncertainty_gain_low;
	public $uncertainty_gain_high;
	public $milestone_date;
	public $roadmap_quarter;
	public $dependencies;
	public $evidence_done;
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
	 * Create
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
		if ($this->status === null || $this->status === '') {
			$this->status = self::STATUS_PLANNED;
		}
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Fetch
	 *
	 * @param int $id Id
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
	 * Mark done and set actual gain if empty
	 *
	 * @param User $user User
	 * @return int
	 */
	public function markDone(User $user)
	{
		$this->status = self::STATUS_DONE;
		if (empty($this->date_done)) {
			$this->date_done = dol_now();
		}
		if (empty($this->gain_tco2e_actual) && !empty($this->gain_tco2e_estimated)) {
			$this->gain_tco2e_actual = $this->gain_tco2e_estimated;
		}
		return $this->update($user, 1);
	}

	/**
	 * Status label
	 *
	 * @param int $status Status
	 * @return string
	 */
	public function getLibStatut($status)
	{
		global $langs;
		$langs->load('dolicarbon@dolicarbon');
		if ($status == self::STATUS_IN_PROGRESS) {
			return $langs->trans('ActionInProgress');
		}
		if ($status == self::STATUS_DONE) {
			return $langs->trans('ActionDone');
		}
		return $langs->trans('ActionPlanned');
	}
}
