<?php

import("classes.BasicController");

class CollectionController extends BasicController {
	public $db;
	public $collection;
	/**
	 * Enter description here...
	 *
	 * @var MongoDB
	 */
	protected $_mongodb;
	
	function onBefore() {
		parent::onBefore();
		$this->db = xn("db");
		$this->collection = xn("collection");
		$this->_mongodb = $this->_mongo->selectDB($this->db);
	}	
	
	/**
	 * load single record
	 *
	 */
	function doRecord() {
		$id = rock_real_id(xn("id"));
		$format = xn("format");
		
		$queryFields = x("query_fields");
		$fields = array();
		if (!empty($queryFields)) {
			foreach ($queryFields as $queryField) {
				$fields[$queryField] = 1;
			}
		}
		
		$row = $this->_mongodb->selectCollection($this->collection)->findOne(array( "_id" => $id ), $fields);
		if (empty($row)) {
			$this->_outputJson(array("code" => 300, "message" => "The record has been removed."));
		}
		$exporter = new VarExportor($this->_mongodb, $row);
		$data = $exporter->export($format);
		$html = $this->_highlight($row, $format, true);
		$this->_outputJson(array("code" => 200, "data" => $data, "html" => $html ));
	}
}

?>