<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file        class/actions_dolicarbon.class.php
 * \ingroup     dolicarbon
 * \brief       Hooks for DoliCarbon
 */
class ActionsDoliCarbon
{
	/** @var DoliDB */
	public $db;

	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * MD theme: force login_block collapse on DoliCarbon pages.
	 *
	 * @param array<string,mixed> $parameters Hook parameters (morecssonbody by reference)
	 * @param CommonObject|null   $object     Not used
	 * @param string              $action     Not used
	 * @param HookManager         $hookmanager Hook manager
	 * @return int
	 */
	public function llxHeader($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;

		if (empty($conf->theme) || $conf->theme !== 'md') {
			return 0;
		}
		if (GETPOST('dol_openinpopup', 'aZ09')) {
			return 0;
		}

		$path = '';
		if (!empty($_SERVER['SCRIPT_FILENAME'])) {
			$path = (string) $_SERVER['SCRIPT_FILENAME'];
		} elseif (!empty($_SERVER['PHP_SELF'])) {
			$path = (string) $_SERVER['PHP_SELF'];
		}
		$path = str_replace('\\', '/', $path);
		if (strpos($path, '/custom/dolicarbon/') === false) {
			return 0;
		}

		$extra = isset($parameters['morecssonbody']) ? trim((string) $parameters['morecssonbody']) : '';
		if ($extra !== '' && preg_match('/(^|\s)sidebar-collapse(\s|$)/', $extra)) {
			return 0;
		}
		$parameters['morecssonbody'] = trim('sidebar-collapse'.($extra !== '' ? ' '.$extra : ''));

		return 0;
	}
}

