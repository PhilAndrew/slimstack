<?php

import("classes.BasicController");

class FieldController extends BasicController {
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
	
	function doRemove() {
		$collection = $this->_mongo->selectCollection($this->db, $this->collection);
		
		$field = xn("field");
		$id = xn("id");
		if ($id) {
			$collection->update(array(
				"_id" => rock_real_id($id)
			), array(
				'$unset' => array(
					$field => 1
				)
			));
		}
		else {
			$collection->update(array(), array(
				'$unset' => array(
					$field => 1
				)
			), array( "multiple" => 1 ));
		}
		exit();
	}
	
	function doClear() {
		$collection = $this->_mongo->selectCollection($this->db, $this->collection);
		
		$field = xn("field");
		$id = xn("id");
		if ($id) {
			$collection->update(array(
				"_id" => rock_real_id($id)
			), array(
				'$set' => array(
					$field => NULL
				)
			));
		}
		else {
			$collection->update(array(), array(
				'$set' => array(
					$field => NULL
				)
			), array( "multiple" => 1 ));
		}
		exit();
	}	
	
	function doRename() {
		$db = $this->_mongo->selectDB($this->db);
		
		$field = xn("field");
		$id = xn("id");
		$newname = trim(xn("newname"));
		$keep = xn("keep");
		if ($newname === "") {
			$this->_outputJson(array( "code" => 300, "message" => "New field name must not be empty"));
		}
		$ret = $db->execute('function (coll, field, newname, id, keep){
				var cursor;
				if (id) {
					cursor = db[coll].find({_id:id});
				}
				else {
					cursor = db[coll].find();
				}
				while(cursor.hasNext()) {
					var row = cursor.next();
					var newobj = { $unset: {}, $set:{} };
					newobj["$unset"][field] = 1;
					if (typeof(row[newname]) == "undefined" || !keep) {
						newobj["$set"][newname] = (typeof(row[field])!="undefined") ? row[field] : null;
					}
					if (typeof(row["_id"]) != "undefined") {
						db[coll].update({ _id:row["_id"] }, newobj);
					}
					else {
						db[coll].update(row, newobj);
					}
				}
			}', array($this->collection, $field, $newname, rock_real_id($id), $keep ? true:false));
		$this->_outputJson(array( "code" => 200 ));
	}
	
	function doNew() {
		$db = $this->_mongo->selectDB($this->db);
		
		$id = xn("id");
		$newname = trim(xn("newname"));
		$keep = xn("keep");
		$dataType = xn("data_type");
		$value = xn("value");
		$boolValue = xn("bool_value");
		$floatValue = xn("float_value");
		$mixedValue = xn("mixed_value");
		if ($newname === "") {
			$this->_outputJson(array( "code" => 300, "message" => "New field name must not be empty"));
		}
		
		$realValue = null;
		try {
			$realValue = $this->_convertValue($dataType, $value, $floatValue, $boolValue, $mixedValue);
		} catch (Exception $e) {
			$this->_outputJson(array( "code" => 400, "message" => $e->getMessage()));
		}
		if (!$keep) {
			if ($id) {
				$db->selectCollection($this->collection)->update(array(
					"_id" => rock_real_id($id)
				), array( '$set' => array( $newname => $realValue ) ));
			}
			else {
				$db->selectCollection($this->collection)->update(array(), array( '$set' => array( $newname => $realValue ) ), array( "multiple" => 1 ));
			}
			$this->_outputJson(array( "code" => 200 ));
		}
		$ret = $db->execute('function (coll, newname, value, id, keep){
				var cursor;
				if (id) {
					cursor = db[coll].find({_id:id});
				}
				else {
					cursor = db[coll].find();
				}
				while(cursor.hasNext()) {
					var row = cursor.next();
					var newobj = { $set:{} };
					if (typeof(row[newname]) == "undefined" || !keep) {
						newobj["$set"][newname] = value;
					}
					if (typeof(row["_id"]) != "undefined") {
						db[coll].update({ _id:row["_id"] }, newobj);
					}
					else {
						db[coll].update(row, newobj);
					}
				}
			}', array($this->collection, $newname, $realValue, rock_real_id($id), $keep ? true:false));
		$this->_outputJson(array( "code" => 200 ));
	}	
	
	/**
	 * load field data
	 *
	 */
	function doLoad() {
		$collection = $this->_mongodb->selectCollection($this->collection);
		$id = xn("id");
		$field = xn("field");
		$type = "integer";
		$data = null;
		if ($id) {
			$one = $collection->findOne(array( "_id" => rock_real_id($id) ));//not select field, because there is a bug in list, such as "list.0"
			$data = rock_array_get($one, $field);
			switch (gettype($data)) {
				case "boolean":
					$type = "boolean";
					break;
				case "integer":
					$type = "integer";
					break;
				case "double":
					$type = "float";
					break;
				case "string":
					$type = "string";
					break;
				case "array":
				case "object":
				case "resource":
					$type = "mixed";
					break;
				case "NULL":
					$type = "null";
					break;
			}
		}
		$exporter = new VarExportor($this->_mongodb, $data);
		$this->_outputJson(array(
			"code" => 200,
			"type" => $type,
			"value" => $data,
			"represent" => $exporter->export(MONGO_EXPORT_PHP)
		));
	}
	
	function doUpdate() {
		$db = $this->_mongo->selectDB($this->db);
		
		$id = xn("id");
		$newname = trim(xn("newname"));
		$dataType = xn("data_type");
		$value = xn("value");
		$boolValue = xn("bool_value");
		$floatValue = xn("float_value");
		$mixedValue = xn("mixed_value");
		if ($newname === "") {
			$this->_outputJson(array( "code" => 300, "message" => "New field name must not be empty"));
		}
		
		$realValue = null;
		try {
			$realValue = $this->_convertValue($dataType, $value, $floatValue, $boolValue, $mixedValue);
		} catch (Exception $e) {
			$this->_outputJson(array( "code" => 400, "message" => $e->getMessage()));
		}
		if ($id) {
			$db->selectCollection($this->collection)->update(array(
				"_id" => rock_real_id($id)
			), array( '$set' => array( $newname => $realValue ) ));
		}
		else {
			$db->selectCollection($this->collection)->update(array(), array( '$set' => array( $newname => $realValue ) ), array( "multiple" => 1 ));
		}
		$this->_outputJson(array( "code" => 200 ));
	}
	
	function doIndexes() {
		$field = xn("field");
		$indexes = $this->_mongodb->selectCollection($this->collection)->getIndexInfo();
		$ret = array();
		foreach ($indexes as $index) {
			if (isset($index["key"][$field])) {
				$ret[] = array( "name" => $index["name"], "key" => $this->_highlight($index["key"], MONGO_EXPORT_JSON));
			}
		}
		$this->_outputJson(array( "code" => 200, "indexes" => $ret ));
	}
	
	function doCreateIndex() {
		$fields = xn("field");
		if (!is_array($fields)) {
			$this->_outputJson(array( "code" => 300, "message" =>  "Index contains one field at least."));
		}
		$orders = xn("order");
		$attrs = array();
		foreach ($fields as $index => $field) {
			$field = trim($field);
			if (!empty($field)) {
				$attrs[$field] = ($orders[$index] == "asc") ? 1 : -1;
			}
		}
		if (empty($attrs)) {
			$this->_outputJson(array( "code" => 300, "message" =>  "Index contains one field at least."));
		}
		
		//if is unique
		$options = array();
		if (x("is_unique")) {
			$options["unique"] = 1;
			if (x("drop_duplicate")) {
				$options["dropDups"] = 1;
			}
		}
		$options["background"] = 1;
		$options["safe"] = 1;
		
		//name
		$name = trim(xn("name"));
		if (!empty($name)) {
			$options["name"] = $name;
		}
		
		//check name 
		$collection = $this->_mongodb->selectCollection($this->collection);
		$indexes = $collection->getIndexInfo();
		foreach ($indexes as $index) {
			if ($index["name"] == $name) {
				$this->_outputJson(array( "code" => 300, "message" => "The name \"{$name}\" is token by other index."));
				break;
			}
			if ($attrs === $index["key"]) {
				$this->_outputJson(array( "code" => 300, "message" => "The key on same fields already exists."));
				break;
			}
 		}
 		
 		$ret = null;
		try {
			$ret = $collection->ensureIndex($attrs, $options);
		} catch (Exception $e) {
			$this->_outputJson(array( "code" => 300, "message" => $e->getMessage()));
		}
		if ($ret["ok"]) {
			$this->_outputJson(array( "code" => 200));
		}
		else {
			$this->_outputJson(array( "code" => 300, "message" => $ret["err"]));
		}
	}
}

?>