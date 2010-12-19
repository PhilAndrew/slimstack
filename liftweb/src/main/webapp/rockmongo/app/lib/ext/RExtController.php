<?php

define("__BASE__", rtrim(str_replace(DS, "/", dirname($_SERVER["PHP_SELF"])), "/"));//当前主目录路径

class RExtController extends RController {
	function redirect($action, array $params = array(), $js = false) {
		$this->redirectUrl($this->path($action, $params), $js);
		exit();
	}
	
	function redirectUrl($url, $js = false) {
		if ($js) {
			echo '<script language="Javascript">window.location="' . $url . '"</script>';
			exit();
		}
		header("location:{$url}");
		exit();
	}
	
	function path($action, array $params = array()) {
		if (!strstr($action, ".")) {
			$action = $this->name() . "." . $action;
		}
		$url = $_SERVER["PHP_SELF"] . "?action=" . $action;
		if (!empty($params)) {
			$url .= "&" . http_build_query($params);
		}
		return $url;
	}
	
	/**
	 * Is POST request?
	 *
	 * @return boolean
	 */
	function isPost() {
		return ($_SERVER["REQUEST_METHOD"] == "POST");
	}
	
	/**
	 * Is from AJAX request?
	 *
	 * @return boolean
	 */
	function isAjax() {
		return (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest");
	}
}

/**
 * output a variable
 *
 * @param mixed $var a variable
 */
function h($var) {
	echo $var;
}

/**
 * output a I18N message
 *
 * @param string $var message key
 */
function hm($var) {
	echo rock_lang($var);
}

/**
 * construct a url from action and it's parameters
 *
 * @param string $action action name
 * @param array $params parameters
 * @return string
 */
function url($action, array $params = array()) {
	unset($params["action"]);
	if (!strstr($action, ".")) {
		$action = __CONTROLLER__ . "." . $action;
	}
	$url = $_SERVER["PHP_SELF"] . "?action=" . $action;
	if (!empty($params)) {
		$url .= "&" . http_build_query($params);
	}
	return $url;
}

/**
 * render navigation
 * 
 * @param string $db database name
 * @param string|null $collection collection name
 * @param boolean $extend if extend the parameters
 */
function render_navigation($db, $collection = null, $extend = true) {
	$dbpath = url("db", array("db" => $db));
	$navigation = '<a href="' . url("databases") . '"><img src="images/world.png" width="14" align="absmiddle"/> ' . rock_lang("databases") . '</a> &raquo; <a href="' .$dbpath . '"><img src="images/database.png" width="14" align="absmiddle"/> ' . $db . "</a>";
	if(!is_null($collection)) {
		$navigation .= " &raquo; <a href=\"" . url("collection", $extend ? xn() : array( "db" => $db, "collection" => $collection )) . "\">";
		if (preg_match("/\.(files|chunks)/", $collection)) {
			$navigation .= '<img src="images/grid.png" width="14" align="absmiddle"/> ';
		}
		else {
			$navigation .= '<img src="images/table.png" width="14" align="absmiddle"/> ';
		}
		$navigation .= $collection . "</a>";
	}
	echo $navigation;
}

/**
 * render server operations
 *
 * @param string|null $current current operation code
 */
function render_server_ops($current = null) {
	$ops = array(
		array( "code" => "server", "url" => url("server"), "name" => rock_lang("server")),
		array( "code" => "status", "url" => url("status"), "name" => rock_lang("status")),
		array( "code" => "databases", "url" => url("databases"), "name" => rock_lang("databases")),
		array( "code" => "processlist", "url" => url("processlist"), "name" => rock_lang("processlist")),
		array( "code" => "command", "url" => url("command", array("db"=>xn("db"))), "name" => rock_lang("command")),
		array( "code" => "execute", "url" => url("execute", array("db"=>xn("db"))), "name" => rock_lang("execute")),
		array( "code" => "replication", "url" => url("replication"), "name" => rock_lang("master_slave")),
	);
	
	$string = "";
	$count = count($ops);
	foreach ($ops as $index => $op) {
		$string .= '<a href="' . $op["url"] . '"';
		if ($current == $op["code"]) {
			$string .= ' class="current"';
		}
		$string .= ">" . $op["name"] . "</a>"; 
		if ($index < $count - 1) {
			$string .= " | ";
		}
	}
	echo $string;
}

function select_data_types($name, $selected = null) {
	$types = array (
		"integer" => "Integer",
		"float" => "Float",
		"string" => "String",
		"boolean" => "Boolean",
		"null" => "NULL",
		"mixed" => "Mixed"
	);
	$select = "<select name=\"" . $name . "\">";
	foreach ($types as $type => $rep) {
		$option = "<option value=\"{$type}\"";
		if ($type == $selected) {
			$option .= " selected=\"selected\"";
		}
		$option .= ">{$rep}</option>";
		$select .= $option;
	}
	$select .= "</select>";
	return $select;
}

?>