<?php

class MCollection {
	static function fields (MongoDB $db, $collection) {
		$one = $db->selectCollection($collection)->findOne();
		if (empty($one)) {
			return array();
		}
		$fields = array();
		self::_fieldsFromRow($fields, $one);
		return $fields;
	}
	
	private static function _fieldsFromRow(&$fields, $row, $prefix = null) {
		foreach ($row as $field => $value) {
			if (is_integer($field) || is_float($field)) {
				continue;
			}
			$namespace = (is_null($prefix)) ? $field : $prefix . "." . $field;
			$fields[] = $namespace;
			if (is_array($value)) {
				self::_fieldsFromRow($fields, $value, $namespace);
			}
		}
	}
	
	/**
	 * If a row is GridFS row
	 *
	 * @param array $row record data
	 * @return boolean
	 */
	static function isFile(array $row) {
		return isset($row["filename"]) && isset($row["chunkSize"]);
	}
	
	/**
	 * get .chunks collection name from .files collection name
	 *
	 * @param string $filesCollection
	 * @return string
	 */
	static function chunksCollection($filesCollection) {
		return preg_replace("/\.files$/", ".chunks", $filesCollection);
	}
}

?>