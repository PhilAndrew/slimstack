<?php
/**
 * eval source code in PHP or JSON format
 *
 */
class VarEval {
	private $_source;
	private $_format;
	/**
	 * current MongoDB
	 *
	 * @var MongoDB
	 */
	private $_db;
	
	function __construct($source, $format = "array", MongoDB $db = null) {
		$this->_source = $source;
		$this->_format = $format;
		if (!$this->_format) {
			$this->_format = "array";
		}
		
		$this->_db = $db;
	}
	
	function execute() {
		if ($this->_format == "array") {
			return $this->_runPHP();
		}
		else if ($this->_format == "json") {
			return $this->_runJson();
		}
	}
	
	private function _runPHP() {
		$this->_source = "return " . $this->_source . ";";
		$php = "<?php\n" . $this->_source . "\n?>";
		$tokens = token_get_all($php);
		foreach ($tokens as $token) {
			$type = $token[0];
			if (is_long($type)) {
				if (in_array($type, array(
						T_OPEN_TAG, 
						T_RETURN, 
						T_WHITESPACE, 
						T_ARRAY, 
						T_LNUMBER, 
						T_DNUMBER,
						T_CONSTANT_ENCAPSED_STRING, 
						T_DOUBLE_ARROW, 
						T_CLOSE_TAG, 
						T_NEW))) {
					continue;
				}
				
				if ($type == T_STRING) {
					$func = strtolower($token[1]);
					if (in_array($func, array(
							"mongoid", 
							"mongocode", 
							"mongodate", 
							"mongoregex", 
							"mongobindata", 
							"mongodbref",
							"mongominkey",
							"mongomaxkey",
							"mongotimestamp"))) {
						continue;
					}
				}
				exit("Illegal call: token parse failure at '" . $token[1] . "'" );
			}
		}
		return eval($this->_source);
	}
	
	private function _runJson() {
		$ret = $this->_db->execute('function () { return ' . $this->_source . ';}');
		if ($ret["ok"]) {
			return $ret["retval"];
		}
		return false;
	}
}

?>