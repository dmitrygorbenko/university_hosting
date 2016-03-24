<?php
	include_once ($INCLUDE_PATH."define.php");
	include_once ($INCLUDE_PATH."sql_core.php");
	include_once ($INCLUDE_PATH."control.php");

class PosgtreSQL_Control {

	var $SQL;
	var $HC;

	var $PARENT_ZC;

	function PosgtreSQL_Control($parent) {

		$this->PARENT_CC = &$parent;

		if (isset($parent->SQL))
			$this->SQL = &$parent->SQL;
		else
			$this->SQL = new PGSQL;

		if (isset($parent->HC))
			$this->HC = &$parent->HC;
		else
			$this->HC = new Hosting_Control;

	}

	function fetch_all_zones_pgsqldb($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			pgsqlt.*
			FROM pgsql_table pgsqlt
			WHERE pgsqlt.id_zone_table=".$id_zone);

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