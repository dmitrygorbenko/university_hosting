<?php

Class PGSQL {

	var $sock;
	var $server;
	var $port;
	var $database;
	var $login;
	var $password;
	var $stupid_scripts;

	function set_variables($vars) {
		$this->server = $vars["server"];
		$this->port = $vars["port"];
		$this->database = $vars["database"];
		$this->login = $vars["login"];
		$this->password = $vars["password"];
		$this->sock = 0;
		$this->stupid_scripts = 0;
	}

	function PGSQL() {
		$this->sock = 0;
		$this->server = "172.16.212.200";
		$this->port = "5432";
		$this->database = "hosting2";
		$this->login = "hosting_system";
		$this->password = "elecom";
		$this->stupid_scripts = 0;
	}

	function connect() {
		if ($this->sock != 0) {
			return;
		}

		$this->sock = 0;

		$str = "host=".$this->server." port=".$this->port;

		if ($this->database != "")
			$str .= " dbname=".$this->database;

		if ($this->login != "") {
			$str .= " user=".$this->login;
			if ($this->password != "")
				$str .= " password=".$this->password;
		}

		$sock = @pg_connect ($str);
		if (!$sock) {
			echo "Could not connect to database";
			die();
		}

		$this->sock = $sock;
	}

	function disconnect() {
		return;

		if ($this->sock != 0) {
			if ($this->stupid_scripts == 0) {
				@pg_close ($this->sock);
				$this->sock = 0;
			}
			else {
				$this->stupid_scripts--;
			}
		}
	}

	function use_database($dbname) {
		if ($query == "")
			return true;

		if ($this->database != $dbname) {
			$result = @pg_query($this->sock, "use ".$dbname);
			if (!$result) {
				echo @pg_last_error($this->sock);
			}
			else $this->database = $dbname;
		}
	}

	function get_error()
	{
		if ($this->sock)
			return pg_last_error($this->sock);
		else
			return "Connection is not avaliable";
	}

	function get_num_rows($result)
	{
		return @pg_num_rows($result);
	}

	function get_object($result)
	{
		return @pg_fetch_object($result);
	}

	function exec_query($query)
	{
		if ($query == "")
			return true;

//		echo $query."<br>";

		if ($this->sock == 0)
			return false;

		$resource = pg_query($this->sock, $query);
		return $resource;
	}
}

Class MYSQL {

	var $socket = 0;
	var $server = "localhost";
	var $login = "root";
	var $password = "elecom";

	function set_variables($vars) {
		$this->server = $vars["server"];
		$this->login = $vars["login"];
		$this->password = $vars["password"];
	}

	function connect() {
		$sock = @mysql_connect($this->server, $this->login, $this->password);
		if (!$sock) {
			echo "Could not connect to database: ".mysql_error();
			exit;
		}

		$this->sock = $sock;
	}

	function disconnect() {
		if ($this->sock != 0) {
			@mysql_close ($this->sock);
			$this->sock = 0;
		}
	}

	function get_error()
	{
		return @mysql_error();
	}

	function exec_query($query)
	{
		if ($query == "")
			return true;

		$resource = @mysql_query($query);
		return $resource;
	}
}

?>