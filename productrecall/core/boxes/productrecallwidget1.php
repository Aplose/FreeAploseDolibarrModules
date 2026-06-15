<?php
/* Copyright (C) 2004-2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2023  Frédéric France     <frederic.france@netlogic.fr>
 * Copyright (C) 2024 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    productrecall/core/boxes/productrecallwidget1.php
 * \ingroup productrecall
 * \brief   Widget provided by ProductRecall
 *
 * Put detailed description here.
 */

include_once DOL_DOCUMENT_ROOT."/core/boxes/modules_boxes.php";


/**
 * Class to manage the box
 *
 * Warning: for the box to be detected correctly by dolibarr,
 * the filename should be the lowercase classname
 */
class productrecallwidget1 extends ModeleBoxes
{
	/**
	 * @var string Alphanumeric ID. Populated by the constructor.
	 */
	public $boxcode = "productrecallbox";

	/**
	 * @var string Box icon (in configuration page)
	 * Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "productrecall@productrecall";

	/**
	 * @var string Box label (in configuration page)
	 */
	public $boxlabel;

	/**
	 * @var string[] Module dependencies
	 */
	public $depends = array('productrecall');

	/**
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 * @var mixed More parameters
	 */
	public $param;

	/**
	 * @var array Header informations. Usually created at runtime by loadBox().
	 */
	public $info_box_head = array();

	/**
	 * @var array Contents informations. Usually created at runtime by loadBox().
	 */
	public $info_box_contents = array();

	/**
	 * @var string 	Widget type ('graph' means the widget is a graph widget)
	 */
	public $widgettype = 'graph';


	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 * @param string $param More parameters
	 */
	public function __construct(DoliDB $db, $param = '')
	{
		global $user, $conf, $langs;
		// Translations
		$langs->loadLangs(array("boxes", "productrecall@productrecall"));

		parent::__construct($db, $param);

		$this->boxlabel = $langs->transnoentitiesnoconv("MyWidget");

		$this->param = $param;

		// Condition when module is enabled or not
		// $this->enabled = getDolGlobalInt('MAIN_FEATURES_LEVEL') > 0;
		// Condition when module is visible by user (test on permission)
		// $this->hidden = !$user->hasRight('productrecall', 'myobject', 'read');
	}

	/**
	 * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
	 *
	 * @param int $max Maximum number of records to load
	 * @return void
	 */
	public function loadBox($max = 5)
	{
		global $langs;

		// Use configuration value for max lines count
		$this->max = $max;

		//dol_include_once("/productrecall/class/productrecall.class.php");

		// Populate the head at runtime
		$text = $langs->trans("ProductRecallWidget1Description", $max);
		$this->info_box_head = array(
			// Title text
			'text' => $text,
			// Add a link
			'sublink' => 'https://rappel.conso.gouv.fr/',
			// Sublink icon placed after the text
			'subpicto' => 'object_productrecall@productrecall',
			// Sublink icon HTML alt text
			'subtext' => '',
			// Sublink HTML target
			'target' => '_blank',
			// HTML class attached to the picto and link
			'subclass' => 'center',
			// Limit and truncate with "…" the displayed text lenght, 0 = disabled
			'limit' => 0,
			// Adds translated " (Graph)" to a hidden form value's input (?)
			'graph' => false
		);

		// Populate the contents at runtime
                $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "productrecall_recall ORDER BY rowid desc LIMIT 100";
                $resultArray = $this->db->getRows($sql);
                if (!$resultArray) {
                    $this->info_box_contents = array();
                    return;
                }

		$this->info_box_contents = array();
                $count=0;
                foreach ($resultArray as $productRecallArray) {
                    $row=array();
                    $cell0=array( // First Column
                        //  HTML properties of the TR element. Only available on the first column.
                        'tr' => 'class="left"',
                        // HTML properties of the TD element
                        'td' => '',
                        // Main text for content of cell
                        'text' => $productRecallArray->nomsdesmodelesoureferences,
                        // Link on 'text' and 'logo' elements
                        'url' => $productRecallArray->lienverslaficherappel,
                        // Link's target HTML property
                        'target' => '_blank',
//                        'textnoformat' => '',
                        // Main text for content of cell (other method)
                        //'text2' => '<p><strong>Another text</strong></p>',
                        // Truncates 'text' element to the specified character length, 0 = disabled
//                        'maxlength' => 100,
                        // Prevents HTML cleaning (and truncation)
//                        'asis' => false,
                        // Same for 'text2'
//                        'asis2' => true
                    );
                    $row[]=$cell0;
                    $cell1 = array(
                        // No TR for n≠0
                        'td' => '',
                        'text' => $productRecallArray->nomdelamarqueduproduit,                        
                    );
                    $row[]=$cell1;                    
                    $cell2 = array(
                        // No TR for n≠0
                        'td' => '',
                        'text' => $productRecallArray->souscatgoriedeproduit,                        
                    );
                    $row[]=$cell2;                    
                    $cell3 = array(
                        // No TR for n≠0
                        'td' => '',
                        'text' => $productRecallArray->catgoriedeproduit,                        
                    );
                    $row[]=$cell3;                    
                    $this->info_box_contents[]=$row;
                    if($count==$max){
                        break;
                    }
                    $count++;
                }
	}

	/**
	 * Method to show box. Called by Dolibarr eatch time it wants to display the box.
	 *
	 * @param array $head       Array with properties of box title
	 * @param array $contents   Array with properties of box lines
	 * @param int   $nooutput   No print, only return string
	 * @return string
	 */
	public function showBox($head = null, $contents = null, $nooutput = 0)
	{
		// You may make your own code here…
		// … or use the parent's class function using the provided head and contents templates
		return parent::showBox($this->info_box_head, $this->info_box_contents, $nooutput);
	}
}
