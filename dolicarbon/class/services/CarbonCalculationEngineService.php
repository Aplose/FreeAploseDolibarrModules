<?php

/**
 * Versioned calculation rules reference for reproducible footprints.
 */
class CarbonCalculationEngineService
{
	/** @var DoliDB */
	private $db;

	public const DEFAULT_CODE = 'DC_CALC_V1';

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 * Ensure default row exists and return active version payload.
	 *
	 * @return array{code:string,label:string,rules:array<string,mixed>}|null
	 */
	public function getActiveVersion()
	{
		global $conf;
		$this->ensureDefaultRow();
		$sql = "SELECT code, label, rules_json FROM ".$this->db->prefix()."dolicarbon_calc_version";
		$sql .= " WHERE entity IN (0, ".((int) $conf->entity).") AND active = 1";
		$sql .= " ORDER BY entity DESC LIMIT 1";
		$resql = $this->db->query($sql);
		if ($resql && $this->db->num_rows($resql)) {
			$obj = $this->db->fetch_object($resql);
			$rules = json_decode((string) $obj->rules_json, true);
			if (!is_array($rules)) {
				$rules = array('formula' => 'quantity * kgco2e_per_unit / 1000');
			}
			return array('code' => $obj->code, 'label' => $obj->label, 'rules' => $rules);
		}
		return array(
			'code' => self::DEFAULT_CODE,
			'label' => 'Default tCO2e',
			'rules' => array('formula' => 'quantity * kgco2e_per_unit / 1000'),
		);
	}

	public function ensureDefaultRow()
	{
		global $conf;
		$sql = "SELECT rowid FROM ".$this->db->prefix()."dolicarbon_calc_version WHERE code = '".$this->db->escape(self::DEFAULT_CODE)."' AND entity = 0";
		$resql = $this->db->query($sql);
		if ($resql && $this->db->num_rows($resql)) {
			return;
		}
		$rules = json_encode(array('formula' => 'quantity * kgco2e_per_unit / 1000', 'rounding' => 6));
		$sql = "INSERT INTO ".$this->db->prefix()."dolicarbon_calc_version (code, label, rules_json, entity, date_start, active)";
		$sql .= " VALUES ('".$this->db->escape(self::DEFAULT_CODE)."', 'Default DoliCarbon engine', '".$this->db->escape($rules)."', 0, CURDATE(), 1)";
		$this->db->query($sql);
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public function listVersions()
	{
		global $conf;
		$out = array();
		$sql = "SELECT rowid, code, label, entity, date_start, active FROM ".$this->db->prefix()."dolicarbon_calc_version";
		$sql .= " WHERE entity IN (0, ".((int) $conf->entity).") ORDER BY entity DESC, rowid ASC";
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$out[] = array(
					'id' => (int) $obj->rowid,
					'code' => $obj->code,
					'label' => $obj->label,
					'entity' => (int) $obj->entity,
					'date_start' => $obj->date_start,
					'active' => (int) $obj->active,
				);
			}
		}
		return $out;
	}
}
