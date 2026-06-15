<?php




class ActionsOds2Csv
{
	/**
	 * Overriding the doAction function
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function afterODTCreation($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;
		$error = 0; // Error counter
		if (!function_exists('str_ends_with')) {
			function str_ends_with(string $haystack, string $needle): bool
			{
				$needle_len = strlen($needle);
				return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, -$needle_len));
			}
		}

		//on vérifie si le fichier est un ODS
		if ($parameters['file'] && str_ends_with($parameters['file'], '.ods') && $conf->global->MAIN_ODT_AS_PDF == 'libreoffice') {
			//on transforme l'ods en csv grace à libreoffice
			//on regarde si on a positionné le délimiteur à autre chose...
			dol_mkdir($conf->user->dir_temp);
			//			dol_delete_dir_recursive($conf->user->dir_temp . '/ods2csv');
			dol_mkdir($conf->user->dir_temp . '/ods2csv');
			$separator = 44; // comma
			$textDelimiter = ''; // none
			$characterSer = 76; //utf8
			$numberOfFirstLineToConvert = '';
			$convertColumnFormat = '';

			if (!empty($conf->global->ODT2CSVSEPARATOR)) {
				dol_syslog("ODT2CSVSEPARATOR : " . $conf->global->ODT2CSVSEPARATOR);
				dol_syslog("SEPARATOR BEFORE : " . $separator);
				$separator = ord($conf->global->ODT2CSVSEPARATOR[0]);
				dol_syslog("SEPARATOR AFTER : " . $separator);
			}
			$execmethod = 1;
			$command = 'soffice --headless -env:UserInstallation=file:\'' . $conf->user->dir_temp . '/ods2csv\' --convert-to csv:"Text - txt - csv (StarCalc)":"' . $separator . ',' . $textDelimiter . ',' . $characterSer . ',' . $numberOfFirstLineToConvert . ',' . $convertColumnFormat . '" --outdir ' . escapeshellarg(dirname($parameters['file'])) . " " . escapeshellarg($parameters['file']);
			dol_syslog(get_class($this) . '::afterODTCreation $execmethod=' . $execmethod . ' Run command=' . $command, LOG_DEBUG);
			exec($command, $output_arr, $retval);
		}
		if (!$error) {
			// Add odtgeneration hook
			if (!is_object($hookmanager)) {
				include_once DOL_DOCUMENT_ROOT . '/core/class/hookmanager.class.php';
				$hookmanager = new HookManager($this->db);
			}
			$hookmanager->initHooks(array('odtgeneration'));
			$parameters['file'] = str_replace('.ods', '.csv', $parameters['file']);
			$reshook = $hookmanager->executeHooks('afterOds2Csv', $parameters, $this, $action);
			return 0;
		} else {
			return -1;
		}
	}
}
