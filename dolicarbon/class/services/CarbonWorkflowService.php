<?php

/**
 * Comments and workflow transitions for inventory lines.
 */
class CarbonWorkflowService
{
	/** @var DoliDB */
	private $db;

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 * @return int rowid or -1
	 */
	public function addComment(User $user, $fkBilan, $message, $fkEntry = null, $workflowStatus = null)
	{
		global $conf;
		$sql = "INSERT INTO ".$this->db->prefix()."dolicarbon_workflow_comment (fk_bilan, fk_entry, message, workflow_status, fk_user, date_creation, entity)";
		$sql .= " VALUES (".((int) $fkBilan).", ";
		$sql .= ($fkEntry ? ((int) $fkEntry) : "NULL").", ";
		$sql .= "'".$this->db->escape($message)."', ";
		$sql .= ($workflowStatus ? "'".$this->db->escape($workflowStatus)."'" : "NULL").", ";
		$sql .= ((int) $user->id).", '".$this->db->idate(dol_now())."', ".((int) $conf->entity).")";
		$res = $this->db->query($sql);
		if (!$res) {
			return -1;
		}
		return (int) $this->db->last_insert_id($this->db->prefix().'dolicarbon_workflow_comment');
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public function listComments($fkBilan)
	{
		global $conf;
		$out = array();
		$sql = "SELECT rowid, fk_entry, message, workflow_status, fk_user, date_creation FROM ".$this->db->prefix()."dolicarbon_workflow_comment";
		$sql .= " WHERE entity = ".((int) $conf->entity)." AND fk_bilan = ".((int) $fkBilan)." ORDER BY rowid DESC";
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$out[] = array(
					'id' => (int) $obj->rowid,
					'fk_entry' => $obj->fk_entry ? (int) $obj->fk_entry : null,
					'message' => $obj->message,
					'workflow_status' => $obj->workflow_status,
					'fk_user' => $obj->fk_user ? (int) $obj->fk_user : null,
					'date_creation' => $obj->date_creation,
				);
			}
		}
		return $out;
	}
}
