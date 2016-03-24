<?php

	include_once ($INCLUDE_PATH."define.php");

	include_once ($INCLUDE_PATH."sql_core.php");
	include_once ($INCLUDE_PATH."control.php");

	include_once ($INCLUDE_PATH."zone_core.php");

class Client_Control {

	var $SQL;
	var $HC;

	var $ZC;
	var $MC;
	var $FC;
	var $AC;
	var $MyC;
	var $PgC;
	var $SDC;
	var $WC;

	var $PARENT_HM;

	function Client_Control($parent) {

		$this->PARENT_HM = &$parent;

		if (isset($parent->SQL))
			$this->SQL = &$parent->SQL;
		else
			$this->SQL = new PGSQL;

		if (isset($parent->HC))
			$this->HC = &$parent->HC;
		else
			$this->HC = new Hosting_Control;


		$this->ZC = new Zone_Control(&$this);

		$this->MC = $this->ZC->MC;
		$this->FC = $this->ZC->FC;
		$this->AC = $this->ZC->AC;
		$this->MyC = $this->ZC->MyC;
		$this->PgC = $this->ZC->PgC;
		$this->SDC = $this->ZC->SDC;
		$this->WC = $this->ZC->WC;
	}

	function add_client_account($active, $login, $passwd, $email, $firstname, $lastname, $company, $region, $postal, $city, $address, $phone, $fax, $add_info) {

		if (eregi("[^a-zA-Z0-9_\\-]", $login, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in login. Allowed only A-Z, a-z, 0-9, -, _";
			return FALSE;
		}

		$this->SQL->connect();

		// Проверяем, есть ли такой логин ?
		// Если есть - тревога !
		$tmp_data = $this->fetch_client_account_by_login($login);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such login already exist !";
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->client_create_dir_struct($login);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}
		$this->HC->disconnect();

		// Теперь создаем нового клиента
		$query = "INSERT INTO client_table (
				active, login, passwd, email, firstname,
				lastname, company, region, postal,
				city, address, phone, fax, add_info, create_time) VALUES (";
		$query .= "'".($active?"1":"0")."', ";
		$query .= "'".$login."', ";
		$query .= "'".$passwd."', ";
		$query .= "'".$email."', ";
		$query .= "'".$firstname."', ";
		$query .= "'".$lastname."', ";
		$query .= "'".$company."', ";
		$query .= "'".$region."', ";
		$query .= "'".$postal."', ";
		$query .= "'".$city."', ";
		$query .= "'".$address."', ";
		$query .= "'".$phone."', ";
		$query .= "'".$fax."', ";
		$query .= "'".$add_info."', ";
		$query .= "'".date("Y-m-d G:i:s")."' ";
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

	function change_client_account($id, $active, $login, $passwd, $email, $firstname, $lastname, $company, $region, $postal, $city, $address, $phone, $fax, $add_info) {

		if (eregi("[^a-zA-Z0-9_\\-]", $login, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in login. Allowed only A-Z, a-z, 0-9, -, _";
			return FALSE;
		}

		// Проверяем, есть ли такой логин ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->fetch_client_account_by_login($login);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id) {
			$_SESSION["adminpanel_error"] = "Such login already exist !";
			return FALSE;
		}

		// Извлекаем старые данные из таблицы клиента
		$old_client_data = $this->fetch_client_account($id);
		if ($old_client_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch client account: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->client_rename_dir_struct($old_client_data->login, $login);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}
		$this->HC->disconnect();

		$this->SQL->connect();
		// Теперь надо изменить основные данные клиента.

		$query = "UPDATE client_table SET ";
		$query .= "login='".$login."', ";
		if ($passwd != "")
			$query .= "passwd='".$passwd."', ";
		$query .= "email='".$email."', ";
		$query .= "firstname='".$firstname."', ";
		$query .= "lastname='".$lastname."', ";
		$query .= "company='".$company."', ";
		$query .= "region='".$region."', ";
		$query .= "postal='".$postal."', ";
		$query .= "city='".$city."', ";
		$query .= "address='".$address."', ";
		$query .= "phone='".$phone."', ";
		$query .= "fax='".$fax."', ";
		$query .= "add_info='".$add_info."', ";
		$query .= "active='".($active?"1":"0")."' ";
		$query .= " WHERE id_table=".$id."";

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function remove_client_account($id) {

		$this->SQL->connect();

		$data = $this->fetch_client_account($id);
		if ($data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch client account: ".$_SESSION["adminpanel_error"];
			$this->SQL->disconnect();
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->client_remove_dir_struct($data->login);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}
		$this->HC->disconnect();

		$result = $this->SQL->exec_query("DELETE FROM client_table WHERE login='".$data->login."'");
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function fetch_all_client_account() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
				ct.*, (
						SELECT COUNT(zt.id_table)
						FROM zone_table zt
						WHERE zt.id_client_table = ct.id_table
					) AS zone_count
				FROM client_table ct
				ORDER BY ct.login");


		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_client_account_light() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
				ct.*
				FROM
				client_table ct
				ORDER BY ct.login");


		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_client_account($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ct.*
			FROM client_table ct
			WHERE ct.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_client_zones($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*
			FROM zone_table zt
			WHERE zt.id_client_table=".$id_client."
			ORDER BY zt.name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function xxx_fetch_client_account_by_domain($domain) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
		ct.*, zt.name AS domain
		FROM client_table ct, zone_table zt
		WHERE zt.name='".$domain."'
		AND zt.id_client_table=ct.id_table");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_client_account_by_login($login) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
		ct.* FROM client_table ct
		WHERE ct.login='".$login."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function xxx_is_client_exist_by_domain($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ct.*, zt.name AS domain
			FROM client_table ct, zone_table zt
			WHERE ct.id_zone_table=".$id."
			AND zt.id_client_table=ct.id_table");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

// ********************************************************************************************
// ********************************************************************************************
//
//	HERE GOES CODE FOR UNPAYED CLIENTS
//
// ********************************************************************************************
// ********************************************************************************************

	function candidate_activate_account($id) {

		// Check clear
		$data = $this->candidate_fetch_client_account($id);
		if ($data == FALSE) {
			echo "Can't fetch client account: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Now, send data to client_create()
		if ($this->add_client_account($data->service_type, $data->domain_type, $data->fdname, "1", $data->login, $data->passwd, $data->email, $data->firstname, $data->lastname, $data->company, $data->region, $data->postal, $data->city, $data->address, $data->phone, $data->fax, $data->add_info, "system") != TRUE) {
			echo "Can't create client: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		//Delete this account from database...
		if ($this->candidate_remove_client_account($id) != TRUE) {
			echo "Can't remove activated account: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Now, we should delete all other queries fo this domain
		$others = $this->candidate_fetch_client_account_by_fdname($data->fdname);
		if ($others == FALSE) {
			echo "Can't fetch other accounts: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		for ($i = 0; $i < $this->SQL->get_num_rows($others); $i++) {
			$data = $this->SQL->get_object($others);

			if ($this->candidate_remove_client_account($data->id_table) != TRUE) {
				echo "Can't remove others account: ".$_SESSION["adminpanel_error"];
				return FALSE;
			}
		}

		return TRUE;
	}

	function candidate_remove_client_account($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM candidate_client_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function candidate_fetch_client_account($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			*
			FROM candidate_client_table
			WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function candidate_fetch_client_account_by_fdname($fdname) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			*
			FROM candidate_client_table
			WHERE fdname='".$fdname."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function candidate_fetch_all_client_account() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, fdname, service_type, query_time, firstname, lastname
			FROM candidate_client_table
			ORDER BY fdname, query_time");

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