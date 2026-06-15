<?php

require_once __DIR__.'/../dolicarbonbilan.class.php';
require_once __DIR__.'/CarbonEntryService.php';
require_once __DIR__.'/CarbonCadrageService.php';
require_once __DIR__.'/CarbonCalculationEngineService.php';

/**
 * Frozen report payload for reproducibility.
 */
class CarbonSnapshotService
{
	/** @var DoliDB */
	private $db;

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 * Build JSON snapshot for a bilan and store row.
	 *
	 * @return array{id:int,hash:string,label:string}|array{error:string}
	 */
	public function createSnapshot(User $user, $fkBilan, $label = '')
	{
		global $conf;
		$bilan = new DoliCarbonBilan($this->db);
		if ($bilan->fetch((int) $fkBilan) <= 0) {
			return array('error' => 'NOT_FOUND');
		}
		$entryService = new CarbonEntryService($this->db);
		$entries = $entryService->listByBilan((int) $fkBilan);
		$entryPayload = array();
		foreach ($entries as $e) {
			$entryPayload[] = array(
				'id' => (int) $e->id,
				'scope' => (int) $e->scope,
				'category' => $e->category,
				'quantity' => (float) $e->quantity,
				'unit' => $e->unit,
				'fk_factor' => $e->fk_factor ? (int) $e->fk_factor : null,
				'tco2e_computed' => (float) $e->tco2e_computed,
				'quality_grade' => isset($e->quality_grade) ? $e->quality_grade : 'B',
				'uncertainty_pct_low' => isset($e->uncertainty_pct_low) ? (float) $e->uncertainty_pct_low : 10,
				'uncertainty_pct_high' => isset($e->uncertainty_pct_high) ? (float) $e->uncertainty_pct_high : 20,
				'workflow_status' => isset($e->workflow_status) ? $e->workflow_status : 'draft',
				'calculation_fingerprint' => isset($e->calculation_fingerprint) ? $e->calculation_fingerprint : null,
			);
		}
		$cadrageSvc = new CarbonCadrageService($this->db);
		$cadrage = $cadrageSvc->getByBilan((int) $fkBilan);
		$engine = new CarbonCalculationEngineService($this->db);
		$calc = $engine->getActiveVersion();
		$payload = array(
			'fk_bilan' => (int) $fkBilan,
			'bilan_ref' => $bilan->ref,
			'bilan_year' => (int) $bilan->year,
			'total_tco2e' => (float) $bilan->total_tco2e,
			'calc_engine' => $calc,
			'cadrage' => $cadrage,
			'entries' => $entryPayload,
			'snapshot_at' => dol_print_date(dol_now(), 'standard'),
		);
		$json = json_encode($payload, JSON_UNESCAPED_UNICODE);
		$hash = hash('sha256', (string) $json);
		$sql = "INSERT INTO ".$this->db->prefix()."dolicarbon_snapshot (fk_bilan, label, content_json, content_hash, fk_user_creat, date_creation, entity)";
		$sql .= " VALUES (".((int) $fkBilan).", '".$this->db->escape($label)."', '".$this->db->escape($json)."', '".$this->db->escape($hash)."', ".((int) $user->id).", '".$this->db->idate(dol_now())."', ".((int) $conf->entity).")";
		$res = $this->db->query($sql);
		if (!$res) {
			return array('error' => 'INSERT_FAILED');
		}
		return array('id' => (int) $this->db->last_insert_id($this->db->prefix().'dolicarbon_snapshot'), 'hash' => $hash, 'label' => $label);
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public function listByBilan($fkBilan)
	{
		global $conf;
		$out = array();
		$sql = "SELECT rowid, label, content_hash, date_creation, fk_user_creat FROM ".$this->db->prefix()."dolicarbon_snapshot";
		$sql .= " WHERE entity = ".((int) $conf->entity)." AND fk_bilan = ".((int) $fkBilan)." ORDER BY rowid DESC";
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$out[] = array(
					'id' => (int) $obj->rowid,
					'label' => $obj->label,
					'content_hash' => $obj->content_hash,
					'date_creation' => $obj->date_creation,
					'fk_user_creat' => $obj->fk_user_creat ? (int) $obj->fk_user_creat : null,
				);
			}
		}
		return $out;
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public function getById($id)
	{
		global $conf;
		$sql = "SELECT rowid, fk_bilan, label, content_json, content_hash, date_creation FROM ".$this->db->prefix()."dolicarbon_snapshot";
		$sql .= " WHERE entity = ".((int) $conf->entity)." AND rowid = ".((int) $id);
		$resql = $this->db->query($sql);
		if ($resql && $this->db->num_rows($resql)) {
			$obj = $this->db->fetch_object($resql);
			$data = json_decode((string) $obj->content_json, true);
			return array(
				'id' => (int) $obj->rowid,
				'fk_bilan' => (int) $obj->fk_bilan,
				'label' => $obj->label,
				'content_hash' => $obj->content_hash,
				'date_creation' => $obj->date_creation,
				'data' => is_array($data) ? $data : array(),
			);
		}
		return null;
	}
}
