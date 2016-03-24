<?php

	include_once ($INCLUDE_PATH."define.php");

	include_once ($INCLUDE_PATH."sql_core.php");
	include_once ($INCLUDE_PATH."mysql_core.php");
	include_once ($INCLUDE_PATH."control.php");

	include_once ($INCLUDE_PATH."client_core.php");

class Hosting_Manage {

	var $SQL;
	var $HC;

	var $CC;
	var $ZC;
	var $MC;
	var $FC;
	var $AC;
	var $MyC;
	var $PgC;
	var $SDC;
	var $WC;

	function Hosting_Manage() {

		$this->SQL = new PGSQL;
		$this->HC = new Hosting_Control;

		$this->CC = new Client_Control(&$this);

		$this->ZC = $this->CC->ZC;
		$this->MC = $this->CC->ZC->MC;
		$this->FC = $this->CC->ZC->FC;
		$this->AC = $this->CC->ZC->AC;
		$this->MyC = $this->CC->ZC->MyC;
		$this->PgC = $this->CC->ZC->PgC;
		$this->SDC = $this->CC->ZC->SDC;
		$this->WC = $this->CC->ZC->WC;
	}
}

?>