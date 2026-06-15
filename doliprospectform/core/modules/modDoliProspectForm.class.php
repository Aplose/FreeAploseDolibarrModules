<?php
/* Copyright (C) 2004-2018	Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019	Nicolas ZABOURI				<info@inovea-conseil.com>
 * Copyright (C) 2019-2024	Frédéric France				<frederic.france@free.fr>
 * Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   doliprospectform     Module DoliProspectForm
 *  \brief      DoliProspectForm module descriptor.
 *
 *  \file       htdocs/doliprospectform/core/modules/modDoliProspectForm.class.php
 *  \ingroup    doliprospectform
 *  \brief      Description and activation file for module DoliProspectForm
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module DoliProspectForm
 */
class modDoliProspectForm extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs;

		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 109052; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'doliprospectform';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "Aplose - Ma Gestion Cloud";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '100320';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleDoliProspectFormName' not found (DoliProspectForm is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// DESCRIPTION_FLAG
		// Module description, used if translation string 'ModuleDoliProspectFormDesc' not found (DoliProspectForm is name of module).
		$this->description = "DoliProspectFormDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "DoliProspectFormDescription";

		// Author
		$this->editor_name = 'Aplose';
		$this->editor_url = 'https://www.aplose.fr/';		// Must be an external online web site
		$this->editor_squarred_logo = '';					// Must be image filename into the module/img directory followed with @modulename. Example: 'myimage.png@doliprospectform'

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated', 'experimental_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (MAIN_MODULE_ + strtoupper of module technical name)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'fa-file-signature';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 0,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 1,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 0,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/doliprospectform/css/doliprospectform.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/doliprospectform/js/doliprospectform.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			/* BEGIN MODULEBUILDER HOOKSCONTEXTS */
			'hooks' => array(
				//   'data' => array(
				//       'hookcontext1',
				//       'hookcontext2',
				//   ),
				//   'entity' => '0',
			),
			/* END MODULEBUILDER HOOKSCONTEXTS */
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
			// Set this to 1 if the module provides a website template into doctemplates/websites/website_template-mytemplate
			'websitetemplates' => 0,
			// Set this to 1 if the module provides a captcha driver
			'captcha' => 0
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/doliprospectform/temp","/doliprospectform/subdir");
		$this->dirs = array("/doliprospectform/temp");

		// Config pages (single entry: public form texts are reached via tabs on setup.php).
		$this->config_page_url = array("setup.php@doliprospectform");

		// Dependencies
		// A condition to hide module
		$this->hidden = getDolGlobalInt('MODULE_DOLIPROSPECTFORM_DISABLED'); // A condition to disable module;
		// List of module class names that must be enabled if this module is enabled. Example: array('always'=>array('modModuleToEnable1','modModuleToEnable2'), 'FR'=>array('modModuleToEnableFR')...)
		$this->depends = array('modSociete');
		// List of module class names to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->requiredby = array();
		// List of module class names this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array();

		// The language file dedicated to your module
		$this->langfiles = array("doliprospectform@doliprospectform");

		// Prerequisites
		$this->phpmin = array(7, 2); // Minimum version of PHP required by module
		// $this->phpmax = array(8, 0); // Maximum version of PHP required by module
		$this->need_dolibarr_version = array(19, -3); // Minimum version of Dolibarr required by module
		// $this->max_dolibarr_version = array(19, -3); // Maximum version of Dolibarr required by module
		$this->need_javascript_ajax = 0;

		// Messages at activation
		$this->warnings_activation = array(); 		// Warning to show when we activate a module. Example: array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = array(); 	// Warning to show when we activate a module if another module is on. Example: array('modOtherModule' => array('always'=>'text')) or array('always' => array('FR'=>'textfr','MX'=>'textmx'...))
		//$this->automatic_activation = array('FR'=>'DoliProspectFormWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = false;			// If true, can't be disabled. Value true is reserved for core modules. Not allowed for external modules.

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		$this->const = array(
			1 => array('DOLIPROSPECTFORM_TOKEN_VALIDITY_DAYS', 'chaine', '90', 'Signed public form link validity (days)', 0, 'current', 0),
			2 => array('DOLIPROSPECTFORM_PUBLIC_FORM_INDIVIDUAL', 'chaine', '1', 'Enable DoliProspectForm public individual form', 0, 'current', 0),
			3 => array('DOLIPROSPECTFORM_PUBLIC_FORM_PROFESSIONAL', 'chaine', '1', 'Enable DoliProspectForm public professional form', 0, 'current', 0),
		);

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isModEnabled("doliprospectform")) {
			$conf->doliprospectform = new stdClass();
			$conf->doliprospectform->enabled = 0;
		}

		// Array to add new pages in new tabs
		/* BEGIN MODULEBUILDER TABS */
		// Don't forget to deactivate/reactivate your module to test your changes
		$this->tabs = array();
		/* END MODULEBUILDER TABS */
		// Example:
		// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data' => 'objecttype:+tabname1:Title1:mylangfile@doliprospectform:$user->hasRight('doliprospectform', 'myobject', 'read'):/doliprospectform/mynewtab1.php?id=__ID__');
		// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data' => 'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@doliprospectform:$user->hasRight('othermodule', 'otherobject', 'read'):/doliprospectform/mynewtab2.php?id=__ID__',
		// To remove an existing tab identified by code tabname
		// $this->tabs[] = array('data' => 'objecttype:-tabname:NU:conditiontoremove');
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'delivery'         to add a tab in delivery view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'supplier_invoice' to add a tab in supplier invoice view
		// 'member'           to add a tab in foundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in sale order view
		// 'supplier_order'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'supplier_payment' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view


		// Dictionaries
		/* Example:
		 $this->dictionaries=array(
		 'langs' => 'doliprospectform@doliprospectform',
		 // List of tables we want to see into dictionary editor
		 'tabname' => array("table1", "table2", "table3"),
		 // Label of tables
		 'tablib' => array("Table1", "Table2", "Table3"),
		 // Request to select fields
		 'tabsql' => array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.$this->db->prefix().'table1 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.$this->db->prefix().'table2 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.$this->db->prefix().'table3 as f'),
		 // Sort order
		 'tabsqlsort' => array("label ASC", "label ASC", "label ASC"),
		 // List of fields (result of select to show dictionary)
		 'tabfield' => array("code,label", "code,label", "code,label"),
		 // List of fields (list of fields to edit a record)
		 'tabfieldvalue' => array("code,label", "code,label", "code,label"),
		 // List of fields (list of fields for insert)
		 'tabfieldinsert' => array("code,label", "code,label", "code,label"),
		 // Name of columns with primary key (try to always name it 'rowid')
		 'tabrowid' => array("rowid", "rowid", "rowid"),
		 // Condition to show each dictionary
		 'tabcond' => array(isModEnabled('doliprospectform'), isModEnabled('doliprospectform'), isModEnabled('doliprospectform')),
		 // Tooltip for every fields of dictionaries: DO NOT PUT AN EMPTY ARRAY
		 'tabhelp' => array(array('code' => $langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'), array('code' => $langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'), ...),
		 );
		 */
		/* BEGIN MODULEBUILDER DICTIONARIES */
		$this->dictionaries = array();
		/* END MODULEBUILDER DICTIONARIES */

		// Boxes/Widgets
		// Add here list of php file(s) stored in doliprospectform/core/boxes that contains a class to show a widget.
		/* BEGIN MODULEBUILDER WIDGETS */
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'doliprospectformwidget1.php@doliprospectform',
			//      'note' => 'Widget provided by DoliProspectForm',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);
		/* END MODULEBUILDER WIDGETS */

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		/* BEGIN MODULEBUILDER CRON */
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/doliprospectform/class/myobject.class.php',
			//      'objectname' => 'MyObject',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => 'isModEnabled("doliprospectform")',
			//      'priority' => 50,
			//  ),
		);
		/* END MODULEBUILDER CRON */
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'isModEnabled("doliprospectform")', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'isModEnabled("doliprospectform")', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		/*
		$o = 1;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of DoliProspectForm'; // Permission label
		$this->rights[$r][4] = 'myobject';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->hasRight('doliprospectform', 'myobject', 'read'))
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 2); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of DoliProspectForm'; // Permission label
		$this->rights[$r][4] = 'myobject';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->hasRight('doliprospectform', 'myobject', 'write'))
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 3); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of DoliProspectForm'; // Permission label
		$this->rights[$r][4] = 'myobject';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->hasRight('doliprospectform', 'myobject', 'delete'))
		$r++;
		*/
		/* END MODULEBUILDER PERMISSIONS */


		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++] = array(
			'fk_menu' => '', // Will be stored into mainmenu + leftmenu. Use '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'top', // This is a Top menu entry
			'titre' => 'ModuleDoliProspectFormName',
			'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle"'),
			'mainmenu' => 'doliprospectform',
			'leftmenu' => '',
			'url' => '/doliprospectform/doliprospectformindex.php',
			'langs' => 'doliprospectform@doliprospectform', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => "isModEnabled('doliprospectform')", // Define condition to show or hide menu entry. Use "isModEnabled('doliprospectform')" if entry must be visible if module is enabled (those quote marks are importants).
			'perms' => '1', // Use 'perms'=>'$user->hasRight("doliprospectform", "myobject", "read")' if you want your menu with a permission rules
			'target' => '',
			'user' => 0, // Internal users only (public forms area)
		);

		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=doliprospectform',
			'type' => 'left',
			'titre' => 'DoliProspectFormMenuDashboard',
			'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle paddingright"'),
			'mainmenu' => 'doliprospectform',
			'leftmenu' => 'doliprospectform_area',
			'url' => '/doliprospectform/doliprospectformindex.php',
			'langs' => 'doliprospectform@doliprospectform',
			'position' => 1000 + $r,
			'enabled' => "isModEnabled('doliprospectform')",
			'perms' => '1',
			'target' => '',
			'user' => 0,
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=doliprospectform,fk_leftmenu=doliprospectform_area',
			'type' => 'left',
			'titre' => 'DoliProspectFormMenuInvitation',
			'mainmenu' => 'doliprospectform',
			'leftmenu' => 'doliprospectform_invitation',
			'url' => '/doliprospectform/invitation.php',
			'langs' => 'doliprospectform@doliprospectform',
			'position' => 1000 + $r,
			'enabled' => "isModEnabled('doliprospectform')",
			'perms' => '$user->hasRight("societe", "creer")',
			'target' => '',
			'user' => 0,
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=doliprospectform,fk_leftmenu=doliprospectform_area',
			'type' => 'left',
			'titre' => 'DoliProspectFormMenuSubmissions',
			'mainmenu' => 'doliprospectform',
			'leftmenu' => 'doliprospectform_submissions',
			'url' => '/doliprospectform/submissions_list.php',
			'langs' => 'doliprospectform@doliprospectform',
			'position' => 1000 + $r,
			'enabled' => "isModEnabled('doliprospectform')",
			'perms' => '1',
			'target' => '',
			'user' => 0,
		);
		/* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU MYOBJECT */
		/*
		$this->menu[$r++]=array(
			'fk_menu' => 'fk_mainmenu=doliprospectform',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'left',                          // This is a Left menu entry
			'titre' => 'MyObject',
			'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle paddingright"'),
			'mainmenu' => 'doliprospectform',
			'leftmenu' => 'myobject',
			'url' => '/doliprospectform/doliprospectformindex.php',
			'langs' => 'doliprospectform@doliprospectform',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => "isModEnabled('doliprospectform')", // Define condition to show or hide menu entry. Use isModEnabled("doliprospectform") if entry must be visible if module is enabled.
			'perms' => '$user->hasRight("doliprospectform", "myobject", "read")',
			'target' => '',
			'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
			'object' => 'MyObject'
		);
		$this->menu[$r++]=array(
			'fk_menu' => 'fk_mainmenu=doliprospectform,fk_leftmenu=myobject',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'left',			                // This is a Left menu entry
			'titre' => 'New_MyObject',
			'mainmenu' => 'doliprospectform',
			'leftmenu' => 'doliprospectform_myobject_new',
			'url' => '/doliprospectform/myobject_card.php?action=create',
			'langs' => 'doliprospectform@doliprospectform',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => "isModEnabled('doliprospectform')", // Define condition to show or hide menu entry. Use isModEnabled("doliprospectform") if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms' => '$user->hasRight("doliprospectform", "myobject", "write")'
			'target' => '',
			'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
			'object' => 'MyObject'
		);
		$this->menu[$r++]=array(
			'fk_menu' => 'fk_mainmenu=doliprospectform,fk_leftmenu=myobject',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'left',			                // This is a Left menu entry
			'titre' => 'List_MyObject',
			'mainmenu' => 'doliprospectform',
			'leftmenu' => 'doliprospectform_myobject_list',
			'url' => '/doliprospectform/myobject_list.php',
			'langs' => 'doliprospectform@doliprospectform',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => "isModEnabled('doliprospectform')", // Define condition to show or hide menu entry. Use isModEnabled("doliprospectform") if entry must be visible if module is enabled.
			'perms' => '$user->hasRight("doliprospectform", "myobject", "read")'
			'target' => '',
			'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
			'object' => 'MyObject'
		);
		*/
		/* END MODULEBUILDER LEFTMENU MYOBJECT */


		// Exports profiles provided by this module
		$r = 0;
		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */
		/*
		$langs->load("doliprospectform@doliprospectform");
		$this->export_code[$r] = $this->rights_class.'_'.$r;
		$this->export_label[$r] = 'MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r] = $this->picto;
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'MyObject'; $keyforclassfile='/doliprospectform/class/myobject.class.php'; $keyforelement='myobject@doliprospectform';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'MyObjectLine'; $keyforclassfile='/doliprospectform/class/myobject.class.php'; $keyforelement='myobjectline@doliprospectform'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject@doliprospectform';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='myobjectline'; $keyforaliasextra='extraline'; $keyforelement='myobjectline@doliprospectform';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('myobjectline' => array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field' => '...');
		//$this->export_examplevalues_array[$r] = array('t.field' => 'Example');
		//$this->export_help_array[$r] = array('t.field' => 'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.$this->db->prefix().'doliprospectform_myobject as t';
		//$this->export_sql_end[$r]  .=' LEFT JOIN '.$this->db->prefix().'doliprospectform_myobject_line as tl ON tl.fk_myobject = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject').')';
		$r++; */
		/* END MODULEBUILDER EXPORT MYOBJECT */

		// Imports profiles provided by this module
		$r = 0;
		/* BEGIN MODULEBUILDER IMPORT MYOBJECT */
		/*
		$langs->load("doliprospectform@doliprospectform");
		$this->import_code[$r] = $this->rights_class.'_'.$r;
		$this->import_label[$r] = 'MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->import_icon[$r] = $this->picto;
		$this->import_tables_array[$r] = array('t' => $this->db->prefix().'doliprospectform_myobject', 'extra' => $this->db->prefix().'doliprospectform_myobject_extrafields');
		$this->import_tables_creator_array[$r] = array('t' => 'fk_user_author'); // Fields to store import user id
		$import_sample = array();
		$keyforclass = 'MyObject'; $keyforclassfile='/doliprospectform/class/myobject.class.php'; $keyforelement='myobject@doliprospectform';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinimport.inc.php';
		$import_extrafield_sample = array();
		$keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject@doliprospectform';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinimport.inc.php';
		$this->import_fieldshidden_array[$r] = array('extra.fk_object' => 'lastrowid-'.$this->db->prefix().'doliprospectform_myobject');
		$this->import_regex_array[$r] = array();
		$this->import_examplevalues_array[$r] = array_merge($import_sample, $import_extrafield_sample);
		$this->import_updatekeys_array[$r] = array('t.ref' => 'Ref');
		$this->import_convertvalue_array[$r] = array(
			't.ref' => array(
				'rule'=>'getrefifauto',
				'class'=>(!getDolGlobalString('DOLIPROSPECTFORM_MYOBJECT_ADDON') ? 'mod_myobject_standard' : getDolGlobalString('DOLIPROSPECTFORM_MYOBJECT_ADDON')),
				'path'=>"/core/modules/doliprospectform/".(!getDolGlobalString('DOLIPROSPECTFORM_MYOBJECT_ADDON') ? 'mod_myobject_standard' : getDolGlobalString('DOLIPROSPECTFORM_MYOBJECT_ADDON')).'.php',
				'classobject'=>'MyObject',
				'pathobject'=>'/doliprospectform/class/myobject.class.php',
			),
			't.fk_soc' => array('rule' => 'fetchidfromref', 'file' => '/societe/class/societe.class.php', 'class' => 'Societe', 'method' => 'fetch', 'element' => 'ThirdParty'),
			't.fk_user_valid' => array('rule' => 'fetchidfromref', 'file' => '/user/class/user.class.php', 'class' => 'User', 'method' => 'fetch', 'element' => 'user'),
			't.fk_mode_reglement' => array('rule' => 'fetchidfromcodeorlabel', 'file' => '/compta/paiement/class/cpaiement.class.php', 'class' => 'Cpaiement', 'method' => 'fetch', 'element' => 'cpayment'),
		);
		$this->import_run_sql_after_array[$r] = array();
		$r++; */
		/* END MODULEBUILDER IMPORT MYOBJECT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int<-1,1>          	1 if OK, <=0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs, $user;

		// Create tables of module at module activation
		//$result = $this->_load_tables('/install/mysql/', 'doliprospectform');
		$result = $this->_load_tables('/doliprospectform/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		if (!getDolGlobalString('DOLIPROSPECTFORM_PUBLIC_FORM_SECRET')) {
			require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
			$sec = getRandomPassword(true, null, 48);
			$ressec = dolibarr_set_const($this->db, 'DOLIPROSPECTFORM_PUBLIC_FORM_SECRET', $sec, 'chaine', 0, '', $conf->entity);
			if ($ressec <= 0) {
				$this->error = 'Failed to set DOLIPROSPECTFORM_PUBLIC_FORM_SECRET';
				return -1;
			}
		}

		$resTpl = $this->ensureDefaultThirdpartyEmailTemplate();
		if ($resTpl < 0) {
			dol_syslog('modDoliProspectForm::init ensureDefaultThirdpartyEmailTemplate failed', LOG_WARNING);
		}

		$this->upgradeLegacyThirdpartyEmailTemplate();

		$resSubTpl = $this->ensureDefaultSubmissionNotifyEmailTemplate();
		if ($resSubTpl < 0) {
			dol_syslog('modDoliProspectForm::init ensureDefaultSubmissionNotifyEmailTemplate failed', LOG_WARNING);
		}
		$this->ensureSubmissionNotifyFallbackEmailConstant();

		$resInvTpl = $this->ensureDefaultInvitationEmailTemplate();
		if ($resInvTpl < 0) {
			dol_syslog('modDoliProspectForm::init ensureDefaultInvitationEmailTemplate failed', LOG_WARNING);
		}

		dol_include_once('custom/doliprospectform/lib/doliprospectform_publicform.lib.php');
		doliprospectform_publicform_ensure_default_text_consts($this->db);

		// Permissions
		$this->remove($options);

		$sql = array();

		return $this->_init($sql, $options);
	}

	/**
	 *	Function called when module is disabled.
	 *	Remove from database constants, boxes and permissions from Dolibarr database.
	 *	Data directories are not deleted
	 *
	 *	@param	string		$options	Options when enabling module ('', 'noboxes')
	 *	@return	int<-1,1>				1 if OK, <=0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}

	/**
	 * Run optional maintenance (legacy email template body). Safe to call from admin UI.
	 *
	 * @return void
	 */
	public function runEmailTemplateMaintenance()
	{
		$this->upgradeLegacyThirdpartyEmailTemplate();
		$this->ensureDefaultSubmissionNotifyEmailTemplate();
		$this->ensureSubmissionNotifyFallbackEmailConstant();
		$this->ensureDefaultInvitationEmailTemplate();
	}

	/**
	 * Build default topic and body for the third-party email template.
	 * Body includes <a href="__DOLIPROSPECTFORM_PUBLIC_LINK_HUB__">…</a> — must be written with direct SQL (admin UI restricthtml strips invalid href).
	 *
	 * @return array{topic:string,body:string}
	 */
	private function buildDefaultThirdpartyEmailTemplateContent()
	{
		global $langs;

		if (is_object($langs)) {
			$langs->load('doliprospectform@doliprospectform');
		}

		$topic = '[__[MAIN_INFO_SOCIETE_NOM]__] ';
		$subjKey = 'DoliProspectFormEmailTplThirdpartySubject';
		$topicSuffix = (is_object($langs) && $langs->trans($subjKey) !== $subjKey) ? $langs->trans($subjKey) : 'Energy prospect — your information';
		$topic .= $topicSuffix;

		$bodyKey = 'DoliProspectFormEmailTplThirdpartyBody';
		$body = (is_object($langs) ? $langs->trans($bodyKey) : '');
		if (!is_object($langs) || $body === $bodyKey) {
			$body = 'Hello __THIRDPARTY_NAME__,<br><br>'
				.'Please complete your details and attach your PDF documents (bills, etc.) using the secure link below.<br><br>'
				.'Your contact: __USER_FULLNAME__<br><br>'
				.'<a href="__DOLIPROSPECTFORM_PUBLIC_LINK_HUB__">Open the secure page</a><br><br>'
				.'This link is tied to your request; do not forward it.<br><br>'
				.'Best regards,<br>'
				.'__MYCOMPANY_NAME__<br><br>'
				.'__USER_SIGNATURE__';
		}

		return array('topic' => $topic, 'body' => $body);
	}

	/**
	 * Default subject/body for invitation flow email template (type thirdparty, label DoliProspectFormInvitation).
	 * Body includes <a href="__DOLIPROSPECTFORM_PUBLIC_LINK_HUB__">…</a> — direct SQL keeps anchor href intact.
	 *
	 * @return array{topic:string,body:string}
	 */
	private function buildDefaultInvitationEmailTemplateContent()
	{
		global $langs;

		if (is_object($langs)) {
			$langs->load('doliprospectform@doliprospectform');
		}

		$topic = '[__[MAIN_INFO_SOCIETE_NOM]__] ';
		$subjKey = 'DoliProspectFormEmailTplInvitationSubject';
		$topicSuffix = (is_object($langs) && $langs->trans($subjKey) !== $subjKey) ? $langs->trans($subjKey) : 'Invitation — complete your file';
		$topic .= $topicSuffix;

		$bodyKey = 'DoliProspectFormEmailTplInvitationBody';
		$body = (is_object($langs) ? $langs->trans($bodyKey) : '');
		if (!is_object($langs) || $body === $bodyKey) {
			$body = 'Hello __THIRDPARTY_NAME__,<br><br>'
				.'You are invited to complete your information and attach your documents (PDF) using the secure link below.<br><br>'
				.'Your contact: __USER_FULLNAME__<br><br>'
				.'<a href="__DOLIPROSPECTFORM_PUBLIC_LINK_HUB__">Open the secure page</a><br><br>'
				.'This link is personal; please do not forward it.<br><br>'
				.'Best regards,<br>'
				.'__MYCOMPANY_NAME__<br><br>'
				.'__USER_SIGNATURE__';
		}

		return array('topic' => $topic, 'body' => $body);
	}

	/**
	 * Insert c_email_templates row (type thirdparty) for invitation presend default.
	 *
	 * @param string $topic   Email subject template
	 * @param string $content Email body HTML
	 * @return int<-1,max>    Inserted rowid or -1 on failure
	 */
	private function insertDoliProspectFormInvitationEmailTemplateSql($topic, $content)
	{
		global $conf;

		$label = '(DoliProspectFormInvitation)';
		$sql = "INSERT INTO ".$this->db->prefix()."c_email_templates (";
		$sql .= "entity, module, type_template, lang, private, fk_user, datec, label,";
		$sql .= " position, defaultfortype, enabled, active, email_from, email_to,";
		$sql .= " email_tocc, email_tobcc, topic, joinfiles, content, content_lines";
		$sql .= ") VALUES (";
		$sql .= ((int) $conf->entity).",";
		$sql .= "'doliprospectform',";
		$sql .= "'thirdparty',";
		$sql .= "'',";
		$sql .= "0, NULL,";
		$sql .= "'".$this->db->idate(dol_now())."',";
		$sql .= "'".$this->db->escape($label)."',";
		$sql .= "79, 0,";
		$sql .= "'1',";
		$sql .= "1,";
		$sql .= "NULL, NULL, NULL, NULL,";
		$sql .= "'".$this->db->escape($topic)."',";
		$sql .= "0,";
		$sql .= "'".$this->db->escape($content)."',";
		$sql .= "NULL)";

		$resql = $this->db->query($sql);
		if (!$resql) {
			dol_syslog('modDoliProspectForm::insertDoliProspectFormInvitationEmailTemplateSql '.$this->db->lasterror(), LOG_ERR);
			return -1;
		}
		return (int) $this->db->last_insert_id($this->db->prefix().'c_email_templates');
	}

	/**
	 * Create default invitation email template (type thirdparty) if missing; set DOLIPROSPECTFORM_INVITATION_EMAIL_TEMPLATE_ID when empty.
	 *
	 * @return int 1 OK or already exists, 0 skipped, -1 on failure to create
	 */
	private function ensureDefaultInvitationEmailTemplate()
	{
		global $conf;

		$label = '(DoliProspectFormInvitation)';
		$sqlCheck = "SELECT rowid FROM ".$this->db->prefix()."c_email_templates";
		$sqlCheck .= " WHERE entity = ".((int) $conf->entity);
		$sqlCheck .= " AND module = 'doliprospectform'";
		$sqlCheck .= " AND type_template = 'thirdparty'";
		$sqlCheck .= " AND label = '".$this->db->escape($label)."'";
		$sqlCheck .= " AND (lang = '' OR lang IS NULL)";
		$sqlCheck .= " ".$this->db->plimit(1);

		$resql = $this->db->query($sqlCheck);
		if (!$resql) {
			return -1;
		}
		$existingRowid = 0;
		if ($this->db->num_rows($resql) > 0) {
			$obj = $this->db->fetch_object($resql);
			$existingRowid = (int) $obj->rowid;
		}

		if ($existingRowid > 0) {
			if (!getDolGlobalInt('DOLIPROSPECTFORM_INVITATION_EMAIL_TEMPLATE_ID')) {
				dolibarr_set_const($this->db, 'DOLIPROSPECTFORM_INVITATION_EMAIL_TEMPLATE_ID', (string) $existingRowid, 'chaine', 0, '', $conf->entity);
			}
			return 1;
		}

		$parts = $this->buildDefaultInvitationEmailTemplateContent();
		$newid = $this->insertDoliProspectFormInvitationEmailTemplateSql($parts['topic'], $parts['body']);
		if ($newid > 0) {
			if (!getDolGlobalInt('DOLIPROSPECTFORM_INVITATION_EMAIL_TEMPLATE_ID')) {
				dolibarr_set_const($this->db, 'DOLIPROSPECTFORM_INVITATION_EMAIL_TEMPLATE_ID', (string) $newid, 'chaine', 0, '', $conf->entity);
			}
			return 1;
		}

		return -1;
	}

	/**
	 * Whether stored body should be rewritten to add a proper <a href> around the DoliProspectForm link placeholder.
	 *
	 * @param string $content Current DB content (mediumtext HTML)
	 * @return bool
	 */
	private function needsDoliProspectFormEmailTemplateHrefRefresh($content)
	{
		if ($content === '' || $content === null) {
			return false;
		}
		if (strpos($content, '__DOLIPROSPECTFORM_PUBLIC_LINK_PARTICULIER__') !== false
			&& strpos($content, '<a href="__DOLIPROSPECTFORM_PUBLIC_LINK_PARTICULIER__"') === false) {
			return true;
		}
		if (strpos($content, '__DOLIPROSPECTFORM_PUBLIC_LINK_HUB__') !== false
			&& strpos($content, '<a href="__DOLIPROSPECTFORM_PUBLIC_LINK_HUB__"') === false) {
			return true;
		}
		return false;
	}

	/**
	 * Insert c_email_templates row with same shape as CEmailTemplate::create (direct SQL keeps anchor href intact).
	 *
	 * @param string $topic Email subject template
	 * @param string $content Email body HTML
	 * @return int<-1,> Inserted rowid or -1 on failure
	 */
	private function insertDoliProspectFormThirdpartyEmailTemplateSql($topic, $content)
	{
		global $conf;

		$label = '(DoliProspectFormPublicIndividualForm)';
		$sql = "INSERT INTO ".$this->db->prefix()."c_email_templates (";
		$sql .= "entity, module, type_template, lang, private, fk_user, datec, label,";
		$sql .= " position, defaultfortype, enabled, active, email_from, email_to,";
		$sql .= " email_tocc, email_tobcc, topic, joinfiles, content, content_lines";
		$sql .= ") VALUES (";
		$sql .= ((int) $conf->entity).",";
		$sql .= "'doliprospectform',";
		$sql .= "'thirdparty',";
		$sql .= "'',";
		$sql .= "0, NULL,";
		$sql .= "'".$this->db->idate(dol_now())."',";
		$sql .= "'".$this->db->escape($label)."',";
		$sql .= "80, 0,";
		$sql .= "'1',";
		$sql .= "1,";
		$sql .= "NULL, NULL, NULL, NULL,";
		$sql .= "'".$this->db->escape($topic)."',";
		$sql .= "0,";
		$sql .= "'".$this->db->escape($content)."',";
		$sql .= "NULL)";

		$resql = $this->db->query($sql);
		if (!$resql) {
			dol_syslog('modDoliProspectForm::insertDoliProspectFormThirdpartyEmailTemplateSql '.$this->db->lasterror(), LOG_ERR);
			return -1;
		}
		return (int) $this->db->last_insert_id($this->db->prefix().'c_email_templates');
	}

	/**
	 * Refresh template body when the DoliProspectForm URL is not wrapped in an anchor (Thunderbird needs href).
	 *
	 * @return void
	 */
	private function upgradeLegacyThirdpartyEmailTemplate()
	{
		global $conf;

		$label = '(DoliProspectFormPublicIndividualForm)';
		$sql = "SELECT rowid, content FROM ".$this->db->prefix()."c_email_templates";
		$sql .= " WHERE entity = ".((int) $conf->entity);
		$sql .= " AND module = 'doliprospectform'";
		$sql .= " AND type_template = 'thirdparty'";
		$sql .= " AND label = '".$this->db->escape($label)."'";
		$sql .= " AND (lang = '' OR lang IS NULL)";
		$sql .= " ".$this->db->plimit(1);

		$resql = $this->db->query($sql);
		if (!$resql || $this->db->num_rows($resql) === 0) {
			return;
		}
		$obj = $this->db->fetch_object($resql);
		if (empty($obj->rowid) || !$this->needsDoliProspectFormEmailTemplateHrefRefresh((string) $obj->content)) {
			return;
		}

		$parts = $this->buildDefaultThirdpartyEmailTemplateContent();
		$sqlUp = "UPDATE ".$this->db->prefix()."c_email_templates SET";
		$sqlUp .= " topic = '".$this->db->escape($parts['topic'])."',";
		$sqlUp .= " content = '".$this->db->escape($parts['body'])."'";
		$sqlUp .= " WHERE rowid = ".((int) $obj->rowid);
		$this->db->query($sqlUp);
	}

	/**
	 * Create default email template for third parties (type thirdparty) if not already present for this entity.
	 *
	 * @return int 1 OK or already exists, 0 skipped, -1 on failure to create
	 */
	private function ensureDefaultThirdpartyEmailTemplate()
	{
		global $conf;

		$label = '(DoliProspectFormPublicIndividualForm)';
		$sqlCheck = "SELECT rowid FROM ".$this->db->prefix()."c_email_templates";
		$sqlCheck .= " WHERE entity = ".((int) $conf->entity);
		$sqlCheck .= " AND module = 'doliprospectform'";
		$sqlCheck .= " AND type_template = 'thirdparty'";
		$sqlCheck .= " AND label = '".$this->db->escape($label)."'";
		$sqlCheck .= " AND (lang = '' OR lang IS NULL)";
		$sqlCheck .= " ".$this->db->plimit(1);

		$resql = $this->db->query($sqlCheck);
		if (!$resql) {
			return -1;
		}
		if ($this->db->num_rows($resql) > 0) {
			return 1;
		}

		$parts = $this->buildDefaultThirdpartyEmailTemplateContent();
		$newid = $this->insertDoliProspectFormThirdpartyEmailTemplateSql($parts['topic'], $parts['body']);
		if ($newid > 0) {
			return 1;
		}

		return -1;
	}

	/**
	 * Default subject/body for submission notification email template (user type).
	 *
	 * @return array{topic:string,body:string}
	 */
	private function buildDefaultSubmissionNotifyEmailTemplateContent()
	{
		global $langs;

		if (is_object($langs)) {
			$langs->load('doliprospectform@doliprospectform');
		}

		$topic = '[__[MAIN_INFO_SOCIETE_NOM]__] ';
		$subjKey = 'DoliProspectFormEmailTplSubmissionSubject';
		$topicSuffix = (is_object($langs) && $langs->trans($subjKey) !== $subjKey) ? $langs->trans($subjKey) : 'New public form submission';
		$topic .= $topicSuffix;

		$bodyKey = 'DoliProspectFormEmailTplSubmissionBody';
		$body = (is_object($langs) ? $langs->trans($bodyKey) : '');
		if (!is_object($langs) || $body === $bodyKey) {
			$body = 'Hello,<br><br>'
				.'A public DoliProspectForm was submitted.<br><br>'
				.'<strong>Submission</strong><br>'
				.'Ref: __DOLIPROSPECTFORM_SUBMISSION_REF__ — __DOLIPROSPECTFORM_SUBMISSION_FORM_TYPE_LABEL__ — __DOLIPROSPECTFORM_SUBMISSION_DATE__<br>'
				.'PDF files: __DOLIPROSPECTFORM_SUBMISSION_NB_DOCUMENTS__<br><br>'
				.'<strong>Third party</strong><br>'
				.'__THIRDPARTY_NAME__ (id __THIRDPARTY_ID__)<br>'
				.'<a href="__DOLIPROSPECTFORM_SUBMISSION_THIRDPARTY_DOLIBARR_URL__">Open third-party card in Dolibarr</a><br><br>'
				.'<strong>Contact</strong><br>'
				.'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_FULLNAME__<br>'
				.'Email: __DOLIPROSPECTFORM_SUBMISSION_CONTACT_EMAIL__<br>'
				.'Phone: __DOLIPROSPECTFORM_SUBMISSION_CONTACT_PHONE__<br><br>'
				.'<strong>Assigned sales representative</strong><br>'
				.'__DOLIPROSPECTFORM_SUBMISSION_COMMERCIAL_FULLNAME__ (__DOLIPROSPECTFORM_SUBMISSION_COMMERCIAL_EMAIL__)<br><br>'
				.'Regards,<br>__MYCOMPANY_NAME__';
		}

		return array('topic' => $topic, 'body' => $body);
	}

	/**
	 * Insert c_email_templates row (type user) for submission notifications.
	 *
	 * @param string $topic   Email subject template
	 * @param string $content Email body HTML
	 * @return int<-1,max>    Inserted rowid or -1 on failure
	 */
	private function insertDoliProspectFormSubmissionNotifyEmailTemplateSql($topic, $content)
	{
		global $conf;

		$label = '(DoliProspectFormSubmissionNotify)';
		$sql = "INSERT INTO ".$this->db->prefix()."c_email_templates (";
		$sql .= "entity, module, type_template, lang, private, fk_user, datec, label,";
		$sql .= " position, defaultfortype, enabled, active, email_from, email_to,";
		$sql .= " email_tocc, email_tobcc, topic, joinfiles, content, content_lines";
		$sql .= ") VALUES (";
		$sql .= ((int) $conf->entity).",";
		$sql .= "'doliprospectform',";
		$sql .= "'user',";
		$sql .= "'',";
		$sql .= "0, NULL,";
		$sql .= "'".$this->db->idate(dol_now())."',";
		$sql .= "'".$this->db->escape($label)."',";
		$sql .= "81, 0,";
		$sql .= "'1',";
		$sql .= "1,";
		$sql .= "NULL, NULL, NULL, NULL,";
		$sql .= "'".$this->db->escape($topic)."',";
		$sql .= "0,";
		$sql .= "'".$this->db->escape($content)."',";
		$sql .= "NULL)";

		$resql = $this->db->query($sql);
		if (!$resql) {
			dol_syslog('modDoliProspectForm::insertDoliProspectFormSubmissionNotifyEmailTemplateSql '.$this->db->lasterror(), LOG_ERR);
			return -1;
		}
		return (int) $this->db->last_insert_id($this->db->prefix().'c_email_templates');
	}

	/**
	 * Create default submission notification template (type user) if missing; set DOLIPROSPECTFORM_SUBMISSION_NOTIFY_TEMPLATE_ID once.
	 *
	 * @return int 1 OK or already exists, 0 skipped, -1 on failure to create
	 */
	private function ensureDefaultSubmissionNotifyEmailTemplate()
	{
		global $conf;

		$label = '(DoliProspectFormSubmissionNotify)';
		$sqlCheck = "SELECT rowid FROM ".$this->db->prefix()."c_email_templates";
		$sqlCheck .= " WHERE entity = ".((int) $conf->entity);
		$sqlCheck .= " AND module = 'doliprospectform'";
		$sqlCheck .= " AND type_template = 'user'";
		$sqlCheck .= " AND label = '".$this->db->escape($label)."'";
		$sqlCheck .= " AND (lang = '' OR lang IS NULL)";
		$sqlCheck .= " ".$this->db->plimit(1);

		$resql = $this->db->query($sqlCheck);
		if (!$resql) {
			return -1;
		}
		$existingRowid = 0;
		if ($this->db->num_rows($resql) > 0) {
			$obj = $this->db->fetch_object($resql);
			$existingRowid = (int) $obj->rowid;
		}

		if ($existingRowid > 0) {
			if (!getDolGlobalInt('DOLIPROSPECTFORM_SUBMISSION_NOTIFY_TEMPLATE_ID')) {
				dolibarr_set_const($this->db, 'DOLIPROSPECTFORM_SUBMISSION_NOTIFY_TEMPLATE_ID', (string) $existingRowid, 'chaine', 0, '', $conf->entity);
			}
			return 1;
		}

		$parts = $this->buildDefaultSubmissionNotifyEmailTemplateContent();
		$newid = $this->insertDoliProspectFormSubmissionNotifyEmailTemplateSql($parts['topic'], $parts['body']);
		if ($newid > 0) {
			if (!getDolGlobalInt('DOLIPROSPECTFORM_SUBMISSION_NOTIFY_TEMPLATE_ID')) {
				dolibarr_set_const($this->db, 'DOLIPROSPECTFORM_SUBMISSION_NOTIFY_TEMPLATE_ID', (string) $newid, 'chaine', 0, '', $conf->entity);
			}
			return 1;
		}

		return -1;
	}

	/**
	 * Initialise fallback notification email from company main email when empty.
	 *
	 * @return void
	 */
	private function ensureSubmissionNotifyFallbackEmailConstant()
	{
		global $conf;

		if (trim((string) getDolGlobalString('DOLIPROSPECTFORM_SUBMISSION_NOTIFY_FALLBACK_EMAIL', '')) !== '') {
			return;
		}
		$mainmail = trim((string) getDolGlobalString('MAIN_INFO_SOCIETE_MAIL', ''));
		if ($mainmail !== '') {
			dolibarr_set_const($this->db, 'DOLIPROSPECTFORM_SUBMISSION_NOTIFY_FALLBACK_EMAIL', $mainmail, 'chaine', 0, '', $conf->entity);
		}
	}
}
