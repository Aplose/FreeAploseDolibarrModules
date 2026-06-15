<?php

require_once __DIR__.'/../dolicarboncadrage.class.php';

class CarbonCadrageService
{
	/** @var DoliDB */
	private $db;

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public function getByBilan($fkBilan)
	{
		$c = new DoliCarbonCadrage($this->db);
		if ($c->fetchByBilan((int) $fkBilan) > 0) {
			return $this->toArray($c);
		}
		return null;
	}

	/**
	 * @return DoliCarbonCadrage|null
	 */
	public function getObjectByBilan($fkBilan)
	{
		$c = new DoliCarbonCadrage($this->db);
		if ($c->fetchByBilan((int) $fkBilan) > 0) {
			return $c;
		}
		return null;
	}

	/**
	 * @param array<string,mixed> $data
	 * @return DoliCarbonCadrage
	 */
	public function save(User $user, $fkBilan, array $data)
	{
		$c = new DoliCarbonCadrage($this->db);
		$exists = $c->fetchByBilan((int) $fkBilan) > 0;
		if ($exists && !empty($c->locked)) {
			$c->error = 'CadrageLocked';
			return $c;
		}
		if (!$exists) {
			$c->fk_bilan = (int) $fkBilan;
		}
		if (isset($data['org_perimeter'])) {
			$c->org_perimeter = (string) $data['org_perimeter'];
		}
		if (isset($data['op_perimeter'])) {
			$c->op_perimeter = (string) $data['op_perimeter'];
		}
		if (isset($data['exclusions'])) {
			$c->exclusions = (string) $data['exclusions'];
		}
		if (isset($data['materiality_pct'])) {
			$c->materiality_pct = (float) $data['materiality_pct'];
		}
		if (isset($data['ref_year'])) {
			$c->ref_year = $data['ref_year'] !== '' && $data['ref_year'] !== null ? (int) $data['ref_year'] : null;
		}
		if (isset($data['reporting_year'])) {
			$c->reporting_year = $data['reporting_year'] !== '' && $data['reporting_year'] !== null ? (int) $data['reporting_year'] : null;
		}
		if (isset($data['completeness_note'])) {
			$c->completeness_note = (string) $data['completeness_note'];
		}
		if (isset($data['collection_checklists_json'])) {
			$c->collection_checklists_json = is_string($data['collection_checklists_json']) ? $data['collection_checklists_json'] : json_encode($data['collection_checklists_json']);
		}
		if (isset($data['method_version'])) {
			$c->method_version = (int) $data['method_version'];
		}
		if (isset($data['note_method'])) {
			$c->note_method = (string) $data['note_method'];
		}
		if (isset($data['locked']) && $user->admin) {
			$c->locked = (int) !empty($data['locked']);
		}
		if ($exists) {
			$c->update($user, 0);
		} else {
			$c->create($user, 0);
		}
		return $c;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function toArray(DoliCarbonCadrage $c)
	{
		return array(
			'id' => (int) $c->id,
			'fk_bilan' => (int) $c->fk_bilan,
			'org_perimeter' => $c->org_perimeter,
			'op_perimeter' => $c->op_perimeter,
			'exclusions' => $c->exclusions,
			'materiality_pct' => (float) $c->materiality_pct,
			'ref_year' => $c->ref_year !== null ? (int) $c->ref_year : null,
			'reporting_year' => $c->reporting_year !== null ? (int) $c->reporting_year : null,
			'completeness_note' => $c->completeness_note,
			'collection_checklists_json' => $c->collection_checklists_json,
			'method_version' => (int) $c->method_version,
			'locked' => (int) $c->locked,
			'note_method' => $c->note_method,
		);
	}
}
