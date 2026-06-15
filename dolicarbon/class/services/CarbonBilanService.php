<?php

require_once __DIR__.'/../dolicarbonbilan.class.php';

class CarbonBilanService
{
	/** @var DoliDB */
	private $db;

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	public function listByEntity()
	{
		$out = array();
		$sql = "SELECT rowid FROM ".$this->db->prefix()."dolicarbon_bilan";
		$sql .= " WHERE entity IN (".getEntity('societe').") ORDER BY year DESC, rowid DESC";
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$b = new DoliCarbonBilan($this->db);
				if ($b->fetch((int) $obj->rowid) > 0) {
					$out[] = $b;
				}
			}
		}
		return $out;
	}
}

