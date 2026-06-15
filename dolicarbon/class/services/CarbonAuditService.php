<?php

/**
 * Append-only audit trail for BC expert traceability.
 */
class CarbonAuditService
{
	/** @var DoliDB */
	private $db;

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 * @param string               $elementType e.g. dolicarbon_entry
	 * @param int                  $fkElement
	 * @param string               $action      create|update|delete|workflow|validate|snapshot
	 * @param User                 $user
	 * @param array<string,mixed>  $detail
	 * @return int Insert id or -1
	 */
	public function log($elementType, $fkElement, $action, User $user, array $detail = array())
	{
		global $conf;
		$sql = "INSERT INTO ".$this->db->prefix()."dolicarbon_audit_log (element_type, fk_element, action, fk_user, date_event, detail_json, entity)";
		$sql .= " VALUES (";
		$sql .= "'".$this->db->escape($elementType)."', ".((int) $fkElement).", '".$this->db->escape($action)."', ".((int) $user->id).", '".$this->db->idate(dol_now())."', ";
		$sql .= "'".$this->db->escape(json_encode($detail, JSON_UNESCAPED_UNICODE))."', ".((int) $conf->entity);
		$sql .= ")";
		$res = $this->db->query($sql);
		if (!$res) {
			return -1;
		}
		return (int) $this->db->last_insert_id($this->db->prefix().'dolicarbon_audit_log');
	}

	/**
	 * @param string $elementType
	 * @param int    $fkElement
	 * @return array<int,array<string,mixed>>
	 */
	public function listForElement($elementType, $fkElement, $limit = 200)
	{
		global $conf;
		$out = array();
		$sql = "SELECT rowid, element_type, fk_element, action, fk_user, date_event, detail_json FROM ".$this->db->prefix()."dolicarbon_audit_log";
		$sql .= " WHERE entity = ".((int) $conf->entity);
		$sql .= " AND element_type = '".$this->db->escape($elementType)."' AND fk_element = ".((int) $fkElement);
		$sql .= " ORDER BY date_event DESC, rowid DESC LIMIT ".((int) $limit);
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$out[] = array(
					'id' => (int) $obj->rowid,
					'element_type' => $obj->element_type,
					'fk_element' => (int) $obj->fk_element,
					'action' => $obj->action,
					'fk_user' => $obj->fk_user ? (int) $obj->fk_user : null,
					'date_event' => $obj->date_event,
					'detail' => json_decode((string) $obj->detail_json, true) ?: array(),
				);
			}
		}
		return $out;
	}

	/**
	 * All audit rows for a bilan (entries + bilan-level events).
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function listForBilan($fkBilan, $limit = 500)
	{
		global $conf;
		$out = array();
		$sql = "SELECT rowid, element_type, fk_element, action, fk_user, date_event, detail_json FROM ".$this->db->prefix()."dolicarbon_audit_log";
		$sql .= " WHERE entity = ".((int) $conf->entity);
		$sql .= " AND ( (element_type = 'dolicarbon_bilan' AND fk_element = ".((int) $fkBilan).")";
		$sql .= " OR (element_type = 'dolicarbon_entry' AND fk_element IN (SELECT rowid FROM ".$this->db->prefix()."dolicarbon_entry WHERE fk_bilan = ".((int) $fkBilan).")) )";
		$sql .= " ORDER BY date_event DESC, rowid DESC LIMIT ".((int) $limit);
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$out[] = array(
					'id' => (int) $obj->rowid,
					'element_type' => $obj->element_type,
					'fk_element' => (int) $obj->fk_element,
					'action' => $obj->action,
					'fk_user' => $obj->fk_user ? (int) $obj->fk_user : null,
					'date_event' => $obj->date_event,
					'detail' => json_decode((string) $obj->detail_json, true) ?: array(),
				);
			}
		}
		return $out;
	}
}
