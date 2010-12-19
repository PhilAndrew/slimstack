<?php
ob_start();
session_start();

set_time_limit(0);

import("lib.ext.RExtController");
import("models.MDb");
import("models.MCollection");
import("classes.VarExportor");
import("classes.VarEval");
class BasicController extends RExtController {
	protected $_servers = array();
	protected $_server;
	/**
	 * Enter description here...
	 *
	 * @var Mongo
	 */
	protected $_mongo;
	
	/**
	 * administrator's name
	 *
	 * @var string
	 */
	protected $_admin;
	protected $_password;//administrator's encrypted password
	protected $_serverIndex = 0;//current server index at all servers
	protected $_serverUrl;
	protected $_logQuery = false;
	
	/** called before any actions **/
	function onBefore() {
		global $MONGO;
		
		if ($this->action() != "login" && $this->action() != "logout") {
			//if user is loged in?
			if (!isset($_SESSION["login"]) || !isset($_SESSION["login"]["password"]) || !isset($_SESSION["login"]["index"])) {
				//find authentication-disabled server
				$isLogined = false;
				foreach ($MONGO["servers"] as $index => $server) {
					if ((isset($server["auth_enabled"]) && !$server["auth_enabled"]) || empty($server["admins"])) {
						//login default user
						if (empty($server["admins"])) {
							$this->_login("admin", "admin");
						}
						else {
							list($username, $password) = each($server["admins"]);
							$this->_login($username, $password);
						}
						$isLogined = true;
						break;
					}
				}
				if (!$isLogined) {
					$this->redirect("login");
				}
			}
			
			//log query
			if (isset($MONGO["features"]["log_query"]) && $MONGO["features"]["log_query"] == "on") {
				$this->_logQuery = true;
			}
			
			$this->_admin = $_SESSION["login"]["username"];
			$this->_password = $_SESSION["login"]["password"];
			$this->_serverIndex = $_SESSION["login"]["index"];
			
			//all allowed servers
			foreach ($MONGO["servers"] as $server) {
				if (empty($server["admins"]) || (isset($server["auth_enabled"]) && !$server["auth_enabled"]) || (isset($server["admins"][$this->_admin]) && $this->_encrypt($server["admins"][$this->_admin]) == $this->_password)) {
					$this->_servers[] = $server;
				}
			}
			if (empty($this->_servers)) {
				exit("No servers you can access.");
			}
			
			//connect to current server
			if (!isset($this->_servers[$this->_serverIndex])) {
				$this->_serverIndex = 0;
			}
			$server = $this->_servers[$this->_serverIndex];
			$this->_server = $server;
			$link = "mongodb://";
			if ($server["username"]) {
				$link .= $server["username"] . ":" . $server["password"] . "@";
			}
			$link .= $server["host"] . ":" . $server["port"];
			$this->_serverUrl = $link;
			try {
				$this->_mongo = new Mongo($link);
			} catch (MongoConnectionException $e) {
				echo rock_lang("can_not_connect", $e->getMessage());
				exit();
			}
		}
		
		if ($this->action() != "admin" && !$this->isAjax()) {
			$this->display("header");
		}
	}

	/** called after action call **/
	function onAfter() {
		if ($this->action() != "admin" && !$this->isAjax()) {
			$this->display("footer");
		}
	}	
	
	/**
	 * let user login
	 *
	 * @param string $username user's name
	 * @param string $password user's password
	 */
	protected function _login($username, $password) {
		$_SESSION["login"] = array(
			"username" => $username,
			"password" => $this->_encrypt($password),
			"index" => 0
		);
	}
	
	/** encrypt password **/
	protected function _encrypt($password) {
		return md5($password);
	}	
	
	protected function _convertValue($dataType, $value, $floatValue, $boolValue, $mixedValue) {
		$realValue = null;
		switch ($dataType) {
			case "integer":
				$realValue = intval($floatValue);
				break;
			case "float":
				$realValue = floatval($floatValue);
				break;
			case "string":
				$realValue = $value;
				break;
			case "boolean":
				$realValue = ($boolValue == "true");
				break;
			case "null":
				$realValue = NULL;
				break;
			case "mixed":
				$eval = new VarEval($mixedValue);
				$realValue = $eval->execute();
				if ($realValue === false) {
					throw new Exception("Unable to parse mixed value, just check syntax!");
				}
				break;
		}
		return $realValue;
	}
	
	protected function _encodeJson($var) {
		if (function_exists("json_encode")) {
			return json_encode($var);
		}
		import("classes.Services_JSON");
		$service = new Services_JSON();
		return $service->encode($var);
	}
	
	protected function _outputJson($var, $exit = true) {
		echo $this->_encodeJson($var);
		if ($exit) {
			exit();
		}
	}
	
	protected function _decodeJson($var) {
		import("classes.Services_JSON");
		$service = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$ret = array();
		$decode = $service->decode($var);
		return $decode;
	}	
	
	/**
	 * Export var as string then highlight it.
	 *
	 * @param mixed $var variable to be exported
	 * @param string $format data format, array|json
	 * @param boolean $label if add label to field
	 * @return string
	 */
	protected function _highlight($var, $format = "array", $label = false) {
		import("classes.VarExportor");
		$exportor = new VarExportor($this->_mongo->selectDB("admin"), $var);
		$varString = $exportor->export($format, $label);
		$string = null;
		if ($format == "array") {
			$string = highlight_string("<?php " . $varString, true);
			$string = preg_replace("/" . preg_quote('<span style="color: #0000BB">&lt;?php&nbsp;</span>', "/") . "/", '', $string, 1);
		}
		else {
			$string =  json_format_html($varString);
		}
		if ($label) {
			$id = addslashes(isset($var["_id"]) ? rock_id_string($var["_id"]) : "");
			$string = preg_replace_callback("/(['\"])rockfield\.(.+)\.rockfield(['\"])/U", create_function('$match', '	$fields = explode(".rockfield.", $match[2]);
					return "<span class=\"field\" field=\"" . implode(".", $fields) . "\">" . $match[1] . array_pop($fields) . $match[3] . "</span>";'), $string);
			$string = preg_replace_callback("/__rockmore\.(.+)\.rockmore__/U", create_function('$match', '
			$field = str_replace("rockfield.", "", $match[1]);
			return "<a href=\"#\" onclick=\"fieldOpMore(\'" . $field . "\',\'' . $id . '\');return false;\" title=\"More text\">[...]</a>";'), $string);
		}
		return $string;
	}
	
	/** 
	 * format bytes to human size 
	 * 
	 * @param integer $bytes size in byte
	 * @return string size in k, m, g..
	 **/
	protected function _formatBytes($bytes) {
		if ($bytes < 1024) {
			return $bytes;
		}
		if ($bytes < 1024 * 1024) {
			return round($bytes/1024, 2) . "k";
		}
		if ($bytes < 1024 * 1024 * 1024) {
			return round($bytes/1024/1024, 2) . "m";
		}
		if ($bytes < 1024 * 1024 * 1024 * 1024) {
			return round($bytes/1024/1024/1024, 2) . "g";
		}
		return $bytes;
	}
	
	/** throw operation exception **/
	protected function _checkException($ret) {
		if (!is_array($ret) || !isset($ret["ok"])) {
			return;
		}
		if ($ret["ok"]) {
			return;
		}
		if (isset($ret["assertion"])) {
			exit($ret["assertion"]);
		}
		if (isset($ret["errmsg"])) {
			exit($ret["errmsg"]);
		}
		p($ret);
		exit();
	}
	
	protected function _listdbs() {
		$dbs = $this->_mongo->listDBs();
		$this->_checkException($dbs);
		return $dbs;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param MongoDB $db
	 * @param unknown_type $from
	 * @param unknown_type $to
	 * @param unknown_type $index
	 */
	protected function _copyCollection($db, $from, $to, $index = true) {
		if ($index) {
			$indexes = $db->selectCollection($from)->getIndexInfo();
			foreach ($indexes as $index) {
				$options = array();
				if (isset($index["unique"])) {
					$options["unique"] = $index["unique"];
				}
				if (isset($index["name"])) {
					$options["name"] = $index["name"];
				}
				if (isset($index["background"])) {
					$options["background"] = $index["background"];
				}
				if (isset($index["dropDups"])) {
					$options["dropDups"] = $index["dropDups"];
				}
				$db->selectCollection($to)->ensureIndex($index["key"], $options);
			}
		}
		$ret = $db->execute('function (coll, coll2) { return db[coll].copyTo(coll2);}', array( $from, $to ));
		return $ret["ok"];
	}		
	
	protected function _logFile($db, $collection) {
		$logDir = dirname(__ROOT__) . DS . "logs";
		return $logDir . DS . urlencode($this->_admin) . "-query-" . urlencode($db) . "-" . urlencode($collection) . ".php";
	}
}

/**
 * 将一个多维数组按照一个键的值排序
 *
 * @param array $array 数组
 * @param mixed $key string|array
 * @param boolean $asc 是否正排序
 * @return array
 */
function rock_array_sort(array $array, $key = null, $asc = true) {
	if (empty($array)) {
		return $array;
	}
	if (empty($key)) {
		$asc ? asort($array) : arsort($array);
	}
	else {
		$GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil] = $key;
		uasort($array, 
			$asc ? create_function('$p1,$p2', '$key=$GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil];$p1=rock_array_get($p1,$key);$p2=rock_array_get($p2,$key);if ($p1>$p2){return 1;}elseif($p1==$p2){return 0;}else{return -1;}')
			:
			create_function('$p1,$p2', '$key=$GLOBALS["rock_ARRAY_SORT_KEY_" . nil];$p1=rock_array_get($p1,$key);$p2=rock_array_get($p2,$key);if ($p1<$p2){return 1;}elseif($p1==$p2){return 0;}else{return -1;}')
		);
		unset($GLOBALS["ROCK_ARRAY_SORT_KEY_" . nil]);
	}	
	return $array;
}

/**
 * convert unicode to utf-8
 * 
 * copy from http://www.blogjava.net/xiaomage234/archive/2009/02/25/256576.html, and we just changed it's name
 *
 * @param string $escstr string to convert
 * @return string utf-8 string
 */
function unicodeToUtf8($escstr){
  preg_match_all("/\\\u[0-9A-Za-z]{4}|\\\.{2}|[0-9a-zA-Z.+-_]+/",$escstr,$matches); //prt($matches);
  $ar = &$matches[0];
  $c = "";
  foreach($ar as $val){
 if (substr($val,0,1)!="\\") { //如果是字母数字+-_.的ascii码
     $c .=$val;
 }
 elseif (substr($val,1,1)!="u") { //如果是非字母数字+-_.的ascii码
  $x = hexdec(substr($val,1,2));
     $c .=chr($x);
 }
 else { //如果是大于0xFF的码
  $val = intval(substr($val,2),16);
  if($val < 0x7F){        // 0000-007F
   $c .= chr($val);
  }elseif($val < 0x800) { // 0080-0800
   $c .= chr(0xC0 | ($val / 64));
   $c .= chr(0x80 | ($val % 64));
  }else{                // 0800-FFFF
   $c .= chr(0xE0 | (($val / 64) / 64));
   $c .= chr(0x80 | (($val / 64) % 64));
   $c .= chr(0x80 | ($val % 64));
  }
 }
  }
  return $c;
}

/**
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Pretty print some JSON
//modified by iwind
function json_format_html($json)
{
	$json = preg_replace("/\\\u[0-9a-f]{4,}/e", 'unicodeToUtf8("\\0")', $json);
    $tab = "&nbsp;&nbsp;";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

/*
 commented out by monk.e.boy 22nd May '08
 because my web server is PHP4, and
 json_* are PHP5 functions...

    $json_obj = json_decode($json);

    if($json_obj === false)
        return false;

    $json = json_encode($json_obj);
*/
    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string) {
                    $new_json .= $char . "<br/>" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "<br/>" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string) {
                    $new_json .= ",<br/>" . str_repeat($tab, $indent_level);
                }
                else {
                    $new_json .= $char;
                }
                break;
            case ':':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if($in_string) {
                    $new_json .= ": ";
                }
                else {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\') {
                    $in_string = !$in_string;
                    if ($in_string) {
                    	$new_json .= "<font color=\"#DD0000\">" . $char;
                    }
                    else {
                    	$new_json .= $char . "</font>";
                    }
       				break;
                }
                else if ($c == 0) {
                	$in_string = !$in_string;
                	$new_json .= "<font color=\"red\">" . $char;
                	break;
                }
            default:
            	if (!$in_string && trim($char) !== "") {
            		$char = "<font color=\"blue\">" . $char . "</font>";
            	}
            	else {
            		$char = htmlspecialchars($char);
            	}
                $new_json .= $char;
                break;
        }
    }
	
    $new_json = preg_replace_callback("{(<font color=\"blue\">([\da-zA-Z_\.]+)</font>)+}", create_function('$match','
    	$string = str_replace("<font color=\"blue\">", "", $match[0]);
    	$string = str_replace("</font>", "", $string);
    	return "<font color=\"blue\" class=\"no_string_var\">" . $string  . "</font>";
    '), $new_json);
    return $new_json;
}


function json_format($json)
{
    $tab = "  ";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

/*
 commented out by monk.e.boy 22nd May '08
 because my web server is PHP4, and
 json_* are PHP5 functions...

    $json_obj = json_decode($json);

    if($json_obj === false)
        return false;

    $json = json_encode($json_obj);
*/
    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
                if(!$in_string)
                {
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
                if(!$in_string)
                {
                    $new_json .= ",\n" . str_repeat($tab, $indent_level);
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ':':
                if(!$in_string)
                {
                    $new_json .= ": ";
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\')
                {
                    $in_string = !$in_string;
                }
            default:
                $new_json .= $char;
                break;
        }
    }

    return $new_json;
}

?>