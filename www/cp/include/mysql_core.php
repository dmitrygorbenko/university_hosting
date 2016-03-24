<?php
	include_once ($INCLUDE_PATH."define.php");
	include_once ($INCLUDE_PATH."sql_core.php");
	include_once ($INCLUDE_PATH."control.php");

class MySQL_Control {

	var $SQL;
	var $MySQL;
	var $HC;

	var $PARENT_ZC;

	function MySQL_Control($parent) {

		$this->PARENT_CC = &$parent;

		if (isset($parent->SQL))
			$this->SQL = &$parent->SQL;
		else
			$this->SQL = new PGSQL;

		if (isset($parent->HC))
			$this->HC = &$parent->HC;
		else
			$this->HC = new Hosting_Control;

		$this->MySQL = new MYSQL;
	}

	function fetch_all_zones_mysqldb($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mysqlt.*
			FROM mysql_table mysqlt
			WHERE mysqlt.id_zone_table=".$id_zone);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}
}


?>