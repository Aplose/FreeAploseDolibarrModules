<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file        class/doliprospectformpublicsubmission.class.php
 * \ingroup     doliprospectform
 * \brief       Public prospect form submission (one row per completed public form)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class DoliProspectFormPublicSubmission
 */
class DoliProspectFormPublicSubmission extends CommonObject
{
	public $module = 'doliprospectform';
	public $element = 'doliprospectformpublicsubmission';
	public $table_element = 'doliprospectform_publicsubmission';
	public $picto = 'fa-file-signature';
	public $isextrafieldmanaged = 0;
	public $ismultientitymanaged = 1;

	const STATUS_VALIDATED = 1;

	/**
	 * @var array<string,array<string,mixed>>
	 */
	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'position' => 1, 'notnull' => 1, 'visible' => 0, 'noteditable' => 1, 'index' => 1, 'lang' => 'doliprospectform@doliprospectform'),
		'entity' => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'position' => 5, 'notnull' => 1, 'visible' => 0, 'default' => '1', 'index' => 1, 'lang' => 'doliprospectform@doliprospectform'),
		'ref' => array('type' => 'varchar(128)', 'label' => 'Ref', 'enabled' => 1, 'position' => 10, 'notnull' => 1, 'visible' => 1, 'index' => 1, 'searchall' => 1, 'showoncombobox' => 1, 'lang' => 'doliprospectform@doliprospectform'),
		'fk_soc' => array('type' => 'integer:Societe:societe/class/societe.class.php:1', 'label' => 'ThirdParty', 'picto' => 'company', 'enabled' => 'isModEnabled("societe")', 'position' => 20, 'notnull' => 1, 'visible' => 1, 'index' => 1, 'lang' => 'doliprospectform@doliprospectform'),
		'fk_contact' => array('type' => 'integer:Contact:contact/class/contact.class.php', 'label' => 'Contact', 'picto' => 'contact', 'enabled' => 'isModEnabled("societe")', 'position' => 30, 'notnull' => -1, 'visible' => 1, 'index' => 1, 'lang' => 'doliprospectform@doliprospectform'),
		'fk_user_commercial' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'DoliProspectFormPublicSubmissionCommercial', 'picto' => 'user', 'enabled' => 1, 'position' => 40, 'notnull' => 1, 'visible' => 1, 'default' => '0', 'index' => 1, 'lang' => 'doliprospectform@doliprospectform'),
		'form_type' => array('type' => 'varchar(32)', 'label' => 'DoliProspectFormPublicSubmissionFormType', 'enabled' => 1, 'position' => 50, 'notnull' => 1, 'visible' => 1, 'lang' => 'doliprospectform@doliprospectform'),
		'nb_documents' => array('type' => 'integer', 'label' => 'DoliProspectFormPublicSubmissionNbDocuments', 'enabled' => 1, 'position' => 55, 'notnull' => 1, 'visible' => 1, 'default' => '0', 'lang' => 'doliprospectform@doliprospectform'),
		'date_submission' => array('type' => 'datetime', 'label' => 'DoliProspectFormPublicSubmissionDate', 'enabled' => 1, 'position' => 60, 'notnull' => 1, 'visible' => 1, 'lang' => 'doliprospectform@doliprospectform'),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'position' => 500, 'notnull' => 1, 'visible' => -2, 'lang' => 'doliprospectform@doliprospectform'),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'position' => 501, 'notnull' => 0, 'visible' => -2, 'lang' => 'doliprospectform@doliprospectform'),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'picto' => 'user', 'enabled' => 1, 'position' => 510, 'notnull' => 1, 'visible' => -2, 'lang' => 'doliprospectform@doliprospectform'),
		'fk_user_modif' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'picto' => 'user', 'enabled' => 1, 'position' => 511, 'notnull' => -1, 'visible' => -2, 'lang' => 'doliprospectform@doliprospectform'),
		'import_key' => array('type' => 'varchar(14)', 'label' => 'ImportId', 'enabled' => 1, 'position' => 1000, 'notnull' => -1, 'visible' => -2, 'lang' => 'doliprospectform@doliprospectform'),
		'status' => array('type' => 'integer', 'label' => 'Status', 'enabled' => 1, 'position' => 2000, 'notnull' => 1, 'visible' => 0, 'default' => '1', 'lang' => 'doliprospectform@doliprospectform'),
	);

	public $rowid;
	public $entity;
	public $ref;
	public $fk_soc;
	public $fk_contact;
	public $fk_user_commercial;
	public $form_type;
	public $nb_documents;
	public $date_submission;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $status;

	/**
	 * @param DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $langs;

		$this->db = $db;

		if (!getDolGlobalInt('MAIN_SHOW_TECHNICAL_ID') && isset($this->fields['rowid']) && !empty($this->fields['ref'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create record
	 *
	 * @param User     $user       User that creates
	 * @param int<0,1> $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int<-1,max>        <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = 0)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int    $id  Id object
	 * @param string $ref Ref
	 * @return int<-1,1> 0 if found, <0 if KO
	 */
	public function fetch($id, $ref = null)
	{
		return $this->fetchCommon($id, $ref);
	}
}
