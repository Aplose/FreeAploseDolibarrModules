<?php
/* Copyright (C) 2021 SuperAdmin
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
 * \file    autosend/class/actions_autosend.class.php
 * \ingroup autosend
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsAutoSend
 */
class ActionsAutoSend
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	public $priority = 60;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	function afterOds2Csv($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;
		$error = 0; // Error counter
		//d'abord on va regarder si il y a des règles d'envois automatiques
		$sql = "select * from " . MAIN_DB_PREFIX . "autosend a where a.auto = true";
		$resql = $this->db->query($sql);
		if ($resql) {
			//dans ce cas on boucle pour savoir si le fichier correspond niveau regex
			while ($row = $this->db->fetch_array($resql)) {
				//d'abord on récupère le nom court du fichier
				$fileName = basename($parameters['file']);
				if (preg_match($row['file_regex'], $fileName) && $parameters['object']->statut == $row['object_status']) {
					//dans ce cas on doit envoyer le fichier... 					
					$ftpHost = $row['ftp_host'];
					$ftpPort = $row['ftp_port'];
					$ftpUser = $row['ftp_user'];
					$ftpPassword = $row['ftp_password'];
					$ftpDir = $row['ftp_directory'];
					$ftpMode = $row['ftp_mode'];
					$fileRenameRule = $row['file_rename_rule'];
					$newFileName = $fileName;
					if (!empty($fileRenameRule)) {
						$newFileName = $fileRenameRule;
						//remplacement par la référence
						$newFileName = str_replace('#REF#', $parameters['object']->ref, $newFileName);
						//remplacement par la référence client
						$newFileName = str_replace('#REF_CUSTOMER#', $parameters['object']->ref_customer, $newFileName);
						//remplacement par l'année
						$newFileName = str_replace('#YYYY#', date('Y'), $newFileName);
						//remplacement par l'année
						$newFileName = str_replace('#YY#', date('y'), $newFileName);
						//remplacement par le mois
						$newFileName = str_replace('#MM#', date('m'), $newFileName);
						//remplacement par le jour
						$newFileName = str_replace('#DD#', date('d'), $newFileName);
						//remplacement par l'heure
						$newFileName = str_replace('#H#', date('H'), $newFileName);
						//remplacement par la minute
						$newFileName = str_replace('#m#', date('i'), $newFileName);
						//remplacement par la seconde
						$newFileName = str_replace('#s#', date('s'), $newFileName);
					}
					$ftpConn = null;
					//on ouvre la connexion ftp et on envoi
					if ($ftpMode == 'ftps') {
						$ftpConn = ftp_ssl_connect($ftpHost, $ftpPort);
					} else if ($ftpMode == 'ftp') {
						$ftpConn = ftp_connect($ftpHost, $ftpPort);
					}
					//on se logue
					$ftpIsLoggedIn = ftp_login($ftpConn, $ftpUser, $ftpPassword);
					if ($ftpIsLoggedIn) {
						$isChDir = false;
						if (!empty($ftpDir)) {
							$isChDir = ftp_chdir($ftpConn, $ftpDir);
						}
						$isFtpPasv = ftp_pasv($ftpConn, true);
						$isFtpPut = false;
						$isFtpDelete = false;
						$isFtpDelete = ftp_delete($ftpConn, $newFileName);
						$isFtpPut = ftp_put($ftpConn, $newFileName, $parameters['file']);
					}
					ftp_close($ftpConn);
				}
			}
		}
	}
}
