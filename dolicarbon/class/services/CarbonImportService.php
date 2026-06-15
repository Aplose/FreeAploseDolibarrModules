<?php

require_once __DIR__.'/../dolicarbonentry.class.php';
require_once __DIR__.'/../dolicarbonfactor.class.php';

class CarbonImportService
{
	/** @var DoliDB */
	private $db;

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	public function computeImportHash($sourceType, $sourceId, $factorId, $quantity)
	{
		return sha1($sourceType.'|'.$sourceId.'|'.$factorId.'|'.((string) $quantity));
	}

	public function hasAlreadyImported($importHash)
	{
		$sql = "SELECT rowid FROM ".$this->db->prefix()."dolicarbon_entry";
		$sql .= " WHERE import_hash = '".$this->db->escape($importHash)."'";
		$resql = $this->db->query($sql);
		return $resql && $this->db->num_rows($resql) > 0;
	}
}

