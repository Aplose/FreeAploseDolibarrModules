<?php

require_once __DIR__.'/../dolicarbonentry.class.php';

class CarbonEntryService
{
	/** @var DoliDB */
	private $db;

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	public function listByBilan($bilanId)
	{
		$out = array();
		$sql = "SELECT rowid FROM ".$this->db->prefix()."dolicarbon_entry";
		$sql .= " WHERE fk_bilan = ".((int) $bilanId)." ORDER BY rowid DESC";
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$e = new DoliCarbonEntry($this->db);
				if ($e->fetch((int) $obj->rowid) > 0) {
					$out[] = $e;
				}
			}
		}
		return $out;
	}
}

