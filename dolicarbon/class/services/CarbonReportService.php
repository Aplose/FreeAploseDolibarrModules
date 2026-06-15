<?php

require_once __DIR__.'/../dolicarbonbilan.class.php';

class CarbonReportService
{
	/** @var DoliDB */
	private $db;

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 * @param int $bilanId > 0: one bilan; 0: all entries linked to bilans of current entity (dashboard default).
	 *
	 * @return array{from:string,where:string}
	 */
	private function entryFromWhere($bilanId)
	{
		global $conf;
		$p = $this->db->prefix();
		if ($bilanId > 0) {
			return array(
				'from' => $p.'dolicarbon_entry e',
				'where' => 'e.fk_bilan = '.((int) $bilanId),
			);
		}
		return array(
			'from' => $p.'dolicarbon_entry e INNER JOIN '.$p.'dolicarbon_bilan b ON b.rowid = e.fk_bilan AND b.entity = '.((int) $conf->entity),
			'where' => '1 = 1',
		);
	}

	public function getScopesTotals($bilanId)
	{
		$out = array(1 => 0.0, 2 => 0.0, 3 => 0.0);
		$frag = $this->entryFromWhere($bilanId);
		$sql = "SELECT e.scope, SUM(e.tco2e_computed) as total FROM ".$frag['from'];
		$sql .= " WHERE ".$frag['where']." GROUP BY e.scope";
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$out[(int) $obj->scope] = (float) $obj->total;
			}
		}
		return $out;
	}

	/**
	 * Simple interval sum: per line nominal tCO2e with asymmetric % bands.
	 *
	 * @return array{nominal:float,low:float,high:float}
	 */
	public function getUncertaintyTotals($bilanId)
	{
		$nominal = 0.0;
		$low = 0.0;
		$high = 0.0;
		$frag = $this->entryFromWhere($bilanId);
		$sql = "SELECT e.tco2e_computed, e.uncertainty_pct_low, e.uncertainty_pct_high FROM ".$frag['from'];
		$sql .= " WHERE ".$frag['where'];
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$t = (float) $obj->tco2e_computed;
				$pl = property_exists($obj, 'uncertainty_pct_low') && $obj->uncertainty_pct_low !== null ? (float) $obj->uncertainty_pct_low : 10.0;
				$ph = property_exists($obj, 'uncertainty_pct_high') && $obj->uncertainty_pct_high !== null ? (float) $obj->uncertainty_pct_high : 20.0;
				$nominal += $t;
				$low += $t * (1.0 - $pl / 100.0);
				$high += $t * (1.0 + $ph / 100.0);
			}
		}
		return array('nominal' => $nominal, 'low' => $low, 'high' => $high);
	}

	/**
	 * Analyst drill-down: category x scope.
	 *
	 * @return array<int,array{category:string,scope:int,total:float,count:int}>
	 */
	public function getCategoryScopeBreakdown($bilanId)
	{
		$out = array();
		$frag = $this->entryFromWhere($bilanId);
		$sql = "SELECT e.category, e.scope, SUM(e.tco2e_computed) as total, COUNT(*) as cnt FROM ".$frag['from'];
		$sql .= " WHERE ".$frag['where']." GROUP BY e.category, e.scope ORDER BY total DESC";
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$out[] = array(
					'category' => $obj->category,
					'scope' => (int) $obj->scope,
					'total' => (float) $obj->total,
					'count' => (int) $obj->cnt,
				);
			}
		}
		return $out;
	}
}

