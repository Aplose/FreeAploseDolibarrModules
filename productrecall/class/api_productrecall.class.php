<?php
/* Copyright (C) 2024 SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use Luracast\Restler\RestException;

dol_include_once('/productrecall/class/recall.class.php');
require_once DOL_DOCUMENT_ROOT . '/api/class/api.class.php';

/**
 * API class for productrecall module
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class ProductRecall extends DolibarrApi
{
	/**
	 * @var string[]	Mandatory fields, checked when create and update object
	 */
	public static $FIELDS = array(
		'nomsdesmodelesoureferences'
	);

	/**
	 * @var Recall {@type Recall}
	 */
	public $recall;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $db;
		parent::__construct($db, 'productrecall');
		$this->db = $db;
		$this->recall = new Recall($this->db);
	}

	/**
	 * Get list of recalls
	 *
	 * Return an array with list of recalls
	 *
	 * @param string  $sortfield  Sort field
	 * @param string  $sortorder  Sort order
	 * @param int     $limit      Limit for list
	 * @param int     $page       Page number
	 * @param string  $sqlfilters SQL filters
	 * @param string  $properties Restrict the data returned to these properties. Ignored if empty. Comma separated list of properties names
	 *
	 * @return array              Array of recall objects
	 *
	 * @url GET /recalls
	 *
	 * @throws RestException 401
	 * @throws RestException 403
	 * @throws RestException 404
	 */
	public function getRecalls($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '', $properties = '')
	{
		if (!DolibarrApiAccess::$user->hasRight('productrecall', 'read')) {
			throw new RestException(403);
		}

		$obj_ret = array();

		// case of external user, $societe param is ignored and replaced by user's socid
		//$socid = DolibarrApiAccess::$user->socid ? DolibarrApiAccess::$user->socid : $societe;

		$sql = "SELECT t.rowid";
		if ($properties) {
			$properties_array = explode(',', $properties);
			foreach ($properties_array as $prop) {
				if (in_array(trim($prop), array('nomsdesmodelesoureferences', 'catgoriedeproduit', 'souscatgoriedeproduit',
												'nomdelamarqueduproduit', 'motifdurappel', 'risquesencourusparleconsomm',
												'datedepublication', 'referencefiche'))) {
					$sql .= ", t.".trim($prop);
				}
			}
		} else {
			$sql .= ", t.*";
		}
		$sql .= " FROM ".MAIN_DB_PREFIX."productrecall_recall as t";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."productrecall_recall_extrafields as ef on (ef.fk_object = t.rowid)";
		$sql .= " WHERE t.entity IN (".getEntity('productrecall_recall').")";

		// Add sql filters
		if ($sqlfilters) {
			$errormessage = '';
			$sql .= forgeSQLFromUniversalSearchCriteria($sqlfilters, $errormessage);
			if ($errormessage) {
				throw new RestException(400, 'Error when validating parameter sqlfilters -> '.$errormessage);
			}
		}

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;

			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		dol_syslog("API Rest request");
		$result = $this->db->query($sql);

		if ($result) {
			$num = $this->db->num_rows($result);
			$min = min($num, ($limit <= 0 ? $num : $limit));
			$i = 0;
			while ($i < $min) {
				$obj = $this->db->fetch_object($result);
				$recall_static = new Recall($this->db);
				if ($recall_static->fetch($obj->rowid)) {
					$obj_ret[] = $this->_cleanObjectDatas($recall_static);
				}
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieve recalls list : '.$this->db->lasterror());
		}

		return $obj_ret;
	}

	/**
	 * Get recall by ID
	 *
	 * Return an array with recall information
	 *
	 * @param int $id ID of recall
	 *
	 * @return array|mixed Data without useless information
	 *
	 * @url GET /recalls/{id}
	 *
	 * @throws RestException 401
	 * @throws RestException 403
	 * @throws RestException 404
	 */
	public function getRecall($id)
	{
		if (!DolibarrApiAccess::$user->hasRight('productrecall', 'read')) {
			throw new RestException(403);
		}

		$result = $this->recall->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Recall not found');
		}

		if (!DolibarrApi::_checkAccessToResource('productrecall', $this->recall->id)) {
			throw new RestException(403, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		return $this->_cleanObjectDatas($this->recall);
	}

	/**
	 * Get recalls by product name
	 *
	 * Return an array with recalls matching the product name
	 *
	 * @param string $productname Product name or model reference to search for
	 * @param string $sortfield   Sort field
	 * @param string $sortorder   Sort order
	 * @param int    $limit       Limit for list
	 * @param int    $page        Page number
	 *
	 * @return array              Array of recall objects
	 *
	 * @url GET /recalls/byproduct/{productname}
	 *
	 * @throws RestException 401
	 * @throws RestException 403
	 * @throws RestException 404
	 */
	public function getRecallsByProductName($productname, $sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0)
	{
		if (!DolibarrApiAccess::$user->hasRight('productrecall', 'read')) {
			throw new RestException(403);
		}

		$obj_ret = array();

		$productname_escaped = $this->db->escape($productname);

		$sql = "SELECT t.rowid";
		$sql .= " FROM ".MAIN_DB_PREFIX."productrecall_recall as t";
		$sql .= " WHERE t.entity IN (".getEntity('productrecall_recall').")";
		$sql .= " AND (";
		$sql .= " t.nomsdesmodelesoureferences LIKE '%".$this->db->escape($productname)."%'";
		$sql .= " OR t.nomdelamarqueduproduit LIKE '%".$this->db->escape($productname)."%'";
		$sql .= " OR t.identificationdesproduits LIKE '%".$this->db->escape($productname)."%'";
		$sql .= ")";

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;
			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		dol_syslog("API Rest request for product recalls by name: ".$productname);
		$result = $this->db->query($sql);

		if ($result) {
			$num = $this->db->num_rows($result);
			$min = min($num, ($limit <= 0 ? $num : $limit));
			$i = 0;
			while ($i < $min) {
				$obj = $this->db->fetch_object($result);
				$recall_static = new Recall($this->db);
				if ($recall_static->fetch($obj->rowid)) {
					$obj_ret[] = $this->_cleanObjectDatas($recall_static);
				}
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieve recalls by product name : '.$this->db->lasterror());
		}

		if (empty($obj_ret)) {
			throw new RestException(404, 'No recalls found for product: '.$productname);
		}

		return $obj_ret;
	}

	/**
	 * Create recall object
	 *
	 * @param array $request_data Request data
	 *
	 * @return int ID of recall
	 *
	 * @url POST /recalls
	 *
	 * @throws RestException 400
	 * @throws RestException 401
	 * @throws RestException 403
	 * @throws RestException 500
	 */
	public function postRecall($request_data = null)
	{
		if (!DolibarrApiAccess::$user->hasRight('productrecall', 'write')) {
			throw new RestException(403);
		}

		// Check mandatory fields
		$result = $this->_validate($request_data);

		// Set entity to current entity
		global $conf;
		$this->recall->entity = $conf->entity;

		foreach ($request_data as $field => $value) {
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again with the caller
				$this->recall->context['caller'] = sanitizeVal($request_data['caller'], 'aZ09');
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->recall->array_options[$index] = $val;
				}
				continue;
			}

			$this->recall->$field = $this->_checkValForAPI($field, $value, $this->recall);
		}

		if ($this->recall->create(DolibarrApiAccess::$user) < 0) {
			throw new RestException(500, 'Error creating recall', array_merge(array($this->recall->error), $this->recall->errors));
		}

		return $this->recall->id;
	}

	/**
	 * Update recall
	 *
	 * @param int   $id           ID of recall to update
	 * @param array $request_data Request data
	 *
	 * @return Object Updated object
	 *
	 * @url PUT /recalls/{id}
	 *
	 * @throws RestException 400
	 * @throws RestException 401
	 * @throws RestException 403
	 * @throws RestException 404
	 * @throws RestException 500
	 */
	public function putRecall($id, $request_data = null)
	{
		if (!DolibarrApiAccess::$user->hasRight('productrecall', 'write')) {
			throw new RestException(403);
		}

		$result = $this->recall->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Recall not found');
		}

		if (!DolibarrApi::_checkAccessToResource('productrecall', $this->recall->id)) {
			throw new RestException(403, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		foreach ($request_data as $field => $value) {
			if ($field == 'id') {
				continue;
			}
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again with the caller
				$this->recall->context['caller'] = sanitizeVal($request_data['caller'], 'aZ09');
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->recall->array_options[$index] = $val;
				}
				continue;
			}

			$this->recall->$field = $this->_checkValForAPI($field, $value, $this->recall);
		}

		if ($this->recall->update(DolibarrApiAccess::$user) > 0) {
			return $this->getRecall($id);
		} else {
			throw new RestException(500, 'Error updating recall', array_merge(array($this->recall->error), $this->recall->errors));
		}
	}

	/**
	 * Delete recall
	 *
	 * @param int $id Recall ID
	 *
	 * @return array Result of deletion
	 *
	 * @url DELETE /recalls/{id}
	 *
	 * @throws RestException 400
	 * @throws RestException 401
	 * @throws RestException 403
	 * @throws RestException 404
	 * @throws RestException 500
	 */
	public function deleteRecall($id)
	{
		if (!DolibarrApiAccess::$user->hasRight('productrecall', 'delete')) {
			throw new RestException(403);
		}

		$result = $this->recall->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Recall not found');
		}

		if (!DolibarrApi::_checkAccessToResource('productrecall', $this->recall->id)) {
			throw new RestException(403, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		if (!$this->recall->delete(DolibarrApiAccess::$user)) {
			throw new RestException(500, 'Error when deleting recall : '.$this->recall->error);
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'Recall deleted'
			)
		);
	}

	/**
	 * Validate fields before creating or updating an object
	 *
	 * @param array|null $data Data to validate
	 *
	 * @return array Validated data
	 *
	 * @throws RestException 400
	 */
	private function _validate($data)
	{
		$recall = array();
		foreach (ProductRecall::$FIELDS as $field) {
			if (!isset($data[$field])) {
				throw new RestException(400, "Missing mandatory field '".$field."'");
			}
			$recall[$field] = $data[$field];
		}
		return $recall;
	}
}