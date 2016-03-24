<?php
	include_once ($INCLUDE_PATH."define.php");

	include_once ($INCLUDE_PATH."sql_core.php");
	include_once ($INCLUDE_PATH."control.php");

class Ftp_Control {

	var $SQL;
	var $HC;

	var $PARENT_ZC;

	function Ftp_Control($parent) {

		$this->PARENT_ZC = &$parent;

		if (isset($parent->SQL))
			$this->SQL = &$parent->SQL;
		else
			$this->SQL = new PGSQL;

		if (isset($parent->HC))
			$this->HC = &$parent->HC;
		else
			$this->HC = new Hosting_Control;
	}

	function add_ftp_account($login, $id_zone, $passwd, $rootdir, $uid, $gid) {

		global $Clients_dir, $PROFTPD_UID, $HOSTING_GID;

		$login = strtolower($login);

		if (eregi("[^a-zA-Z0-9\\._\\-]", $login, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in login. Allowed only A-Z, a-z, 0-9, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		// Проверяем, есть ли такой логин ?
		// Если да - тревога !
		$tmp_data = $this->fetch_ftp_by_login($login);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such login already exist !";
			return FALSE;
		}

		// Let's begin...
		$ftp_data["ftp_login"] = $login;
		$ftp_data["ftp_id_zone"] = $id_zone;
		$ftp_data["ftp_rootdir"] = $rootdir;
		$ftp_data["ftp_passwd"] = $passwd;
		$ftp_data["ftp_uid"] = $uid;
		$ftp_data["ftp_gid"] = $gid;
		$ftp_data["zone_id_table"] = 0;
		$ftp_data["ftp_dummy"] = true;

		// А теперь попытаемся найти зону, на котороу ссылается email
		$find_zone = FALSE;

		if ($ftp_data["ftp_id_zone"] != "none") {
			$zone_data = $this->PARENT_ZC->fetch_zone($ftp_data["ftp_id_zone"]);
			if ($zone_data != FALSE) {
				$find_zone = TRUE;
			}
			else {
				$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
				return FALSE;
			}
		}

		if ($find_zone == TRUE) {
			$ftp_data["zone_id_table"] = $zone_data->id_table;
			$ftp_data["ftp_uid"] = $zone_data->uid;
			$ftp_data["ftp_gid"] = $zone_data->gid;
			$ftp_data["ftp_dummy"] = false;

			if (strchr($ftp_data["ftp_rootdir"], "~") != FALSE) {
				$client_data = $this->PARENT_ZC->PARENT_CC->fetch_client_account($zone_data->id_client_table);
				if ($client_data == FALSE) {
					$_SESSION["adminpanel_error"] = "Can't fetch client: ".$_SESSION["adminpanel_error"];
					return FALSE;
				}

				$www_dir = $Clients_dir."/".$client_data->login."/".$zone_data->name."/www/";
				$ftp_data["ftp_rootdir"] = strtr($ftp_data["ftp_rootdir"], array("~" => $www_dir));
				$ftp_data["ftp_rootdir"] = strtr($ftp_data["ftp_rootdir"], array("//" => "/"));
			}
		}
		else {
			if ($ftp_data["ftp_uid"] == "" || $ftp_data["ftp_gid"] == "" ) {
				$_SESSION["adminpanel_error"] = "You have to fill ID fields";
				return FALSE;
			}

			$result = $this->PARENT_ZC->check_ID_existence($ftp_data["ftp_uid"], $ftp_data["ftp_gid"]);
			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] = "Failed on check ID existence: ".$_SESSION["adminpanel_error"];
				return FALSE;
			}
		}
/*
		echo "<pre>"; print_r($ftp_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		// Login, password, uid's
		$query = "INSERT INTO ftp_table (".($ftp_data["zone_id_table"]!=0?"id_zone_table, ":"dummy, ")."  login, passwd, rootdir, uid, gid) VALUES (";
		if ($ftp_data["zone_id_table"] != 0)
			$query .= "".$ftp_data["zone_id_table"].", ";
		else
			$query .= "1, ";
		$query .= "'".$ftp_data["ftp_login"]."', ";
		$query .= "'".$ftp_data["ftp_passwd"]."', ";
		$query .= "'".$ftp_data["ftp_rootdir"]."', ";
		$query .= "'".$ftp_data["ftp_uid"]."', ";
		$query .= "'".$ftp_data["ftp_gid"]."' ";
		$query .= ")";

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function change_ftp_account($id, $login, $id_zone, $passwd, $rootdir, $uid, $gid) {

		global $Clients_dir, $PROFTPD_UID, $HOSTING_GID;

		$login = strtolower($login);

		if (eregi("[^a-zA-Z0-9\\._\\-]", $login, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in login. Allowed only A-Z, a-z, 0-9, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		// Проверяем, есть ли такой ftp user ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->fetch_ftp_by_login($login);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id) {
			$_SESSION["adminpanel_error"] = "Such ftp user already exist !";
			return FALSE;
		}

		$ftp_data = $this->fetch_ftp_account($id);
		if ($ftp_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch ftp user: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Let's begin...

		// OLD
		$old_ftp_data["ftp_id"] = $ftp_data->id_table;

		// NEW
		$new_ftp_data["ftp_login"] = $login;
		$new_ftp_data["ftp_id_zone"] = $id_zone;
		$new_ftp_data["ftp_rootdir"] = $rootdir;
		$new_ftp_data["ftp_passwd"] = $passwd;
		$new_ftp_data["ftp_uid"] = $uid;
		$new_ftp_data["ftp_gid"] = $gid;
		$new_ftp_data["zone_id_table"] = 0;
		$new_ftp_data["ftp_dummy"] = true;

		// А теперь попытаемся найти зону, на котороу ссылается email
		$find_new_zone = FALSE;

		if ($new_ftp_data["ftp_id_zone"] != "none") {
			$zone_data = $this->PARENT_ZC->fetch_zone($new_ftp_data["ftp_id_zone"]);
			if ($zone_data != FALSE) {
				$find_new_zone = TRUE;
			}
			else {
				$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
				return FALSE;
			}
		}

		if ($find_new_zone == TRUE) {
			$new_ftp_data["zone_id_table"] = $zone_data->id_table;
			$new_ftp_data["ftp_uid"] = $zone_data->uid;
			$new_ftp_data["ftp_gid"] = $zone_data->gid;
			$new_ftp_data["ftp_dummy"] = false;

			if (strchr($new_ftp_data["ftp_rootdir"], "~") != FALSE) {
				$client_data = $this->PARENT_ZC->PARENT_CC->fetch_client_account($zone_data->id_client_table);
				if ($client_data == FALSE) {
					$_SESSION["adminpanel_error"] = "Can't fetch client: ".$_SESSION["adminpanel_error"];
					return FALSE;
				}

				$www_dir = $Clients_dir."/".$client_data->login."/".$zone_data->name."/www/";
				$new_ftp_data["ftp_rootdir"] = strtr($new_ftp_data["ftp_rootdir"], array("~" => $www_dir));
				$new_ftp_data["ftp_rootdir"] = strtr($new_ftp_data["ftp_rootdir"], array("//" => "/"));
			}
		}
		else {

			if ($new_ftp_data["ftp_uid"] == "" || $new_ftp_data["ftp_gid"] == "" ) {
				$_SESSION["adminpanel_error"] = "You have to fill ID fields";
				return FALSE;
			}

			$result = $this->PARENT_ZC->check_ID_existence($new_ftp_data["ftp_uid"], $new_ftp_data["ftp_gid"]);
			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] = "Failed on check ID existence: ".$_SESSION["adminpanel_error"];
				return FALSE;
			}
		}
/*
		echo "<pre>"; print_r($new_ftp_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		$query = "UPDATE ftp_table SET ";
		if ($new_ftp_data["zone_id_table"] != 0)
			$query .= "id_zone_table=".$new_ftp_data["zone_id_table"].", dummy=0, ";
		else
			$query .= "id_zone_table=NULL, dummy=1, ";
		$query .= "login='".$new_ftp_data["ftp_login"]."', ";
		if ($passwd != "")
			$query .= "passwd='".$new_ftp_data["ftp_passwd"]."', ";
		$query .= "rootdir='".$new_ftp_data["ftp_rootdir"]."', ";
		$query .= "uid=".$new_ftp_data["ftp_uid"].", ";
		$query .= "gid=".$new_ftp_data["ftp_gid"]."";
		$query .= " WHERE id_table=".$old_ftp_data["ftp_id"]."";

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function remove_ftp_account($id) {

		$tmp_data = $this->fetch_ftp_account($id);
		if ($tmp_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch ftp user: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM ftp_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function xxx_fetch_all_ftp_accounts() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ft.id_table, ft.id_ftp_center_table AS center, ft.login, ft.homedir,
			fl.bytes_in_avail, ftal.bytes_in_used
			FROM ftp_table ft, ftp_limit fl, ftp_tallies ftal
			WHERE fl.id_ftp_center_table=ft.id_ftp_center_table
			AND ftal.id_ftp_center_table=ft.id_ftp_center_table ORDER BY login");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_ftp_account($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ft.*
			FROM ftp_table ft
			WHERE ft.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);

		$this->SQL->disconnect();

		return $data;
	}

	function fetch_ftp_by_login($login) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ft.*
			FROM ftp_table ft
			WHERE ft.login='".$login."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);

		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_ftps() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ft.*
			FROM ftp_table ft
			ORDER BY ft.login");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_alone_ftps() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ft.*
			FROM ftp_table ft
			WHERE ft.dummy=1
			ORDER BY ft.login");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_ftps_of_zone($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ft.*
			FROM ftp_table ft
			WHERE ft.id_zone_table = ".$id_zone."
			ORDER BY ft.login");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_ftp($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ft.*
			FROM ftp_table ft
			WHERE ft.id_zone_table=".$id_zone);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function get_ftps_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(ft.id_table) AS ftp_count
			FROM ftp_table ft");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function get_alone_ftps_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(ft.id_table) AS ftp_count
			FROM ftp_table ft
			WHERE ft.dummy=1");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

//####################################################
//||||||||||||||||||||||||||||||||||||||||||||||||||||
//####################################################

	function xxx_client_add_ftp_account($id_client, $login, $passwd, $homedir) {

		global $FTP_UID, $HOSTING_GID;

		$login = strtolower($login);

		// Проверяем, есть ли такой логин ?
		// Если да - тревога !

		$ftp_data = $this->fetch_ftp_by_login($login);

		if ($ftp_data != FALSE) {
			$_SESSION["clientpanel_error"] = "Такой логин уже существует !";
			return FALSE;
		}

		$this->SQL->connect();

		$query = "SELECT id_table FROM ftp_center_table WHERE id_client_table=".$id_client;

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}
		$center_data = $this->SQL->get_object($result);

		// Теперь, создаем собственно и сам e-mail

		$query = "INSERT INTO ftp_table (id_ftp_center_table, login, passwd, uid, gid, homedir) VALUES (";
		$query .= "".$center_data->id_table.", ";
		$query .= "'".$login."', ";
		$query .= "'".$passwd."', ";
		$query .= "".$FTP_UID.", ";
		$query .= "".$HOSTING_GID.", ";
		$query .= "'".$homedir."'";
		$query .= ")";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function xxx_client_change_ftp_account($id_client, $login, $passwd, $homedir) {

		$ftp_data = $this->client_fetch_ftp_account($id_client, $login);

		if ($ftp_data != TRUE) {
			$_SESSION["clientpanel_error"] = "Не могу выбрать данные: ".$_SESSION["clientpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$query = "UPDATE ftp_table SET ";
		if ($passwd != "")
			$query .= "passwd='".$passwd."', ";
		$query .= "homedir='".$homedir."' ";
		$query .= " WHERE id_ftp_center_table=".$ftp_data->center." AND login='".$login."'";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function xxx_client_remove_ftp_account($id_client, $id) {

		// Узнаем данные этого логина

		$ftp_data = $this->client_fetch_ftp_account_2($id_client, $id);

		if ($ftp_data != TRUE) {
			$_SESSION["clientpanel_error"] = "Не могу выбрать данные: ".$_SESSION["clientpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE
		FROM ftp_table
		WHERE id_ftp_center_table=".$ftp_data->center." AND id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function xxx_client_fetch_all_ftp_accounts($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ft.id_table, ft.login, ft.homedir
			FROM ftp_table ft, ftp_center_table f_cen
			WHERE f_cen.id_client_table=".$id_client."
			AND f_cen.id_table=ft.id_ftp_center_table
			ORDER BY login");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function xxx_client_fetch_ftp_account($id_client, $login) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
		ft.login, ft.homedir, f_cen.id_table AS center
		FROM ftp_table ft, ftp_center_table f_cen
		WHERE f_cen.id_client_table=".$id_client."
		AND f_cen.id_table=ft.id_ftp_center_table
		AND ft.login='".$login."'");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function xxx_client_fetch_ftp_account_2($id_client, $id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
		ft.login, ft.homedir, f_cen.id_table AS center
		FROM ftp_table ft, ftp_center_table f_cen
		WHERE f_cen.id_client_table=".$id_client."
		AND f_cen.id_table=ft.id_ftp_center_table
		AND ft.id_table='".$id."'");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function xxx_client_fetch_ftp_quota($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
		fl.bytes_in_avail AS quota, ftal.bytes_in_used AS used
		FROM ftp_center_table f_cen, ftp_limit fl, ftp_tallies ftal
		WHERE f_cen.id_client_table=".$id_client."
		AND f_cen.id_table=fl.id_ftp_center_table
		AND f_cen.id_table=ftal.id_ftp_center_table");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

}

?>