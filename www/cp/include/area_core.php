<?php

	include_once ($INCLUDE_PATH."define.php");

	include_once ($INCLUDE_PATH."sql_core.php");
	include_once ($INCLUDE_PATH."control.php");

class Area_Control {

	var $SQL;
	var $HC;

	var $PARENT_CC;

	function Area_Control($parent) {

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

	function fetch_all_zones_areas($id_zone) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			apt.*
			FROM area_protect_table apt
			WHERE apt.id_zone_table=".$id_zone);

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_area_users($id_zone) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			*
			FROM area_users_table aut
			WHERE aut.id_zone_table=".$id_zone);

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_area_groups($id_zone) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			*
			FROM area_groups_table agt
			WHERE agt.id_zone_table=".$id_zone);

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

//*******************************************************************************
//*******************************************************************************
//*******************************************************************************
//*******************************************************************************

	function client_add_user($id_client, $domain, $name, $passwd, $group_id) {

		// Проверяем, есть ли такой пользователь ?
		// Если да - тревога !

		$tmp_data = $this->client_get_user_area_of_client_by_name($id_client, $name);
		if ($tmp_data != FALSE) {
			$_SESSION["clientpanel_error"] = "Такой пользователь уже существует !";
			return FALSE;
		}

		if ($group_id != "") {
			$group_data = $this->client_get_group_area_of_client_by_id($id_client, $group_id);
			if ($group_data == FALSE) {
				$_SESSION["clientpanel_error"] = "Не могу выбрать группу !";
				return FALSE;
			}
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		if ($group_id != "")
			$result = $this->HC->protect_create_user($domain, $name, $passwd, $group_data->name);
		else
			$result = $this->HC->protect_create_user($domain, $name, $passwd, "");
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();


		$this->SQL->connect();

		$query = "INSERT INTO area_users_table (
			id_client_table, name, passwd ".($group_id!=""?", id_area_groups_table":"")." )
			VALUES (";
		$query .= "".$id_client.", ";
		$query .= "'".$name."', ";
		$query .= "'".$passwd."' ";
		if ($group_id != "")
			$query .= ", ".$group_id." ";
		$query .= " )";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function client_change_user($id_client, $domain, $id, $name, $passwd, $group_id) {

		// Проверяем, есть ли такой пользователь ?
		// Если нет - тревога !

		$tmp_data = $this->client_get_user_area_of_client_by_id($id_client, $id);
		if ($tmp_data == FALSE) {
			$_SESSION["clientpanel_error"] = "Такой пользователь не существует !";
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		if ($group_id != "") {
			$group_data = $this->client_get_group_area_of_client_by_id($id_client, $group_id);
			if ($group_data == FALSE) {
				$_SESSION["clientpanel_error"] = "Не могу выбрать группу !";
				return FALSE;
			}
		}

		if ($passwd == "")
			$passwd = $tmp_data->passwd;

		$result = $this->HC->protect_update_user($domain, $tmp_data->name, $name, $passwd, $group_data->name);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "UPDATE area_users_table SET ";
		$query .= "name='".$name."', ";
		if ($group_id != "")
			$query .= "id_area_groups_table=".$group_id.", ";
		$query .= "passwd='".$passwd."' ";
		$query .= " WHERE id_table=".$id;

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function client_remove_user($id_client, $domain, $id) {

		$tmp_data = $this->client_get_user_area_of_client_by_id($id_client, $id);
		if ($tmp_data == FALSE) {
			$_SESSION["clientpanel_error"] = "Такой пользователь не существует !";
			return FALSE;
		}

		// Check if user control area
		$area_data = $this->client_get_area_of_client_by_user_id($id_client, $id);
		if ($area_data == FALSE) {
			$_SESSION["clientpanel_error"] = "Ошибка SQL запроса при выборке областей";
			return FALSE;
		}
		for ($i = 0; $i < $this->SQL->get_num_rows($area_data); $i++) {
			$data = $this->SQL->get_object($area_data);

			if ($data->id_table != "")
				$this->client_remove_area($id_client, $domain, $data->id_table);
		}

		// Remove user...
		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->protect_remove_user($domain, $tmp_data->name);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "DELETE FROM area_users_table ";
		$query .= "WHERE id_table=".$id." ";
		$query .= "AND id_client_table=".$id_client;

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function client_get_user_area_of_client($id_client) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, name, passwd, id_area_groups_table AS id_group
			FROM area_users_table
			WHERE id_client_table=".$id_client."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function client_get_user_area_of_client_by_name($id_client, $name) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, name, passwd,  id_area_groups_table AS id_group
			FROM area_users_table
			WHERE id_client_table=".$id_client."
			AND name='".$name."'
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function client_get_user_area_of_client_by_id($id_client, $id) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, name, passwd,  id_area_groups_table AS id_group
			FROM area_users_table
			WHERE id_client_table=".$id_client."
			AND id_table=".$id."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function client_add_group($id_client, $domain, $name) {

		// Проверяем, есть ли же такая группа ?
		// Если да - тревога !

		$tmp_data = $this->client_get_group_area_of_client_by_name($id_client, $name);
		if ($tmp_data != FALSE) {
			$_SESSION["clientpanel_error"] = "Такая группа уже существует !";
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->protect_create_group($domain, $name);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "INSERT INTO area_groups_table (
			id_client_table, name )
			VALUES (";
		$query .= "'".$id_client."', ";
		$query .= "'".$name."' )";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function client_change_group($id_client, $domain, $id, $name) {

		// Проверяем, есть ли такая группа ?
		// Если нет - тревога !

		$tmp_data = $this->client_get_group_area_of_client_by_id($id_client, $id);
		if ($tmp_data == FALSE) {
			$_SESSION["clientpanel_error"] = "Такая группа не существует !";
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->protect_update_group($domain, $tmp_data->name, $name);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "UPDATE area_groups_table SET ";
		$query .= "name='".$name."' ";
		$query .= " WHERE id_table=".$id;

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function client_remove_group($id_client, $domain, $id) {

		$tmp_data = $this->client_get_group_area_of_client_by_id($id_client, $id);
		if ($tmp_data == FALSE) {
			$_SESSION["clientpanel_error"] = "Такая группа не существует !";
			return FALSE;
		}

		// Check if group control area
		$area_data = $this->client_get_area_of_client_by_group_id($id_client, $id);
		if ($area_data == FALSE) {
			$_SESSION["clientpanel_error"] = "Ошибка SQL запроса при выборке областей";
			return FALSE;
		}
		for ($i = 0; $i < $this->SQL->get_num_rows($area_data); $i++) {
			$data = $this->SQL->get_object($area_data);

			if ($data->id_table != "")
				$this->client_remove_area($id_client, $domain, $data->id_table);
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->protect_remove_group($domain, $tmp_data->name);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "DELETE FROM area_groups_table ";
		$query .= "WHERE id_table=".$id." ";
		$query .= "AND id_client_table=".$id_client;

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function client_get_group_area_of_client($id_client) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, name
			FROM area_groups_table
			WHERE id_client_table=".$id_client."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function client_get_members_group_area_of_client($id_client, $id_group) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			name
			FROM area_users_table
			WHERE id_client_table=".$id_client."
			AND id_area_groups_table=".$id_group."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function client_get_group_area_of_client_by_name($id_client, $name) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, name
			FROM area_groups_table
			WHERE id_client_table=".$id_client."
			AND name='".$name."'
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function client_get_group_area_of_client_by_id($id_client, $id) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, name
			FROM area_groups_table
			WHERE id_client_table=".$id_client."
			AND id_table=".$id."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function client_protect_area($id_client, $domain, $realm, $object_type, $object_path, $method_type, $user_id, $group_id) {
		// Проверяем, есть ли такая область ?
		// Если да - тревога !

		$tmp_data = $this->client_get_area_of_client_by_name($id_client, $realm);
		if ($tmp_data != FALSE) {
			$_SESSION["clientpanel_error"] = "Такая область уже существует !";
			return FALSE;
		}

		if ($method_type == "user") {
			$user_data = $this->client_get_user_area_of_client_by_id($id_client, $user_id);
			if ($user_data == FALSE) {
				$_SESSION["clientpanel_error"] = "Такой пользователь не существует !";
				return FALSE;
			}

			$method_name = $user_data->name;
		}
		else if ($method_type == "group") {
			$group_data = $this->client_get_group_area_of_client_by_id($id_client, $group_id);
			if ($group_data == FALSE) {
				$_SESSION["clientpanel_error"] = "Такая группа не существует !";
				return FALSE;
			}

			$method_name = $group_data->name;
		}
		else {
			$_SESSION["clientpanel_error"] = "Неизвестный метод !";
			return FALSE;
		}

		$trans = array("+" => "++");
		$safe_realm = strtr($realm, $trans);
		$safe_realm = strtr($safe_realm, " ", "+");

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->protect_create_area($domain, $safe_realm, $object_type, $object_path, $method_type, $method_name);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		if ($method_type == "user") {
			$query = "INSERT INTO area_protect_table (
				id_client_table, title, method_type, item_type, item, id_area_user_table )
				VALUES (";
			$query .= "".$id_client.", ";
			$query .= "'".$realm."', ";
			$query .= "'".$method_type."', ";
			$query .= "'".$object_type."', ";
			$query .= "'".$object_path."', ";
			$query .= "".$user_id." ";
			$query .= " )";
		}
		elseif ($method_type == "group") {
			$query = "INSERT INTO area_protect_table (
				id_client_table, title, method_type, item_type, item, id_area_group_table )
				VALUES (";
			$query .= "".$id_client.", ";
			$query .= "'".$realm."', ";
			$query .= "'".$method_type."', ";
			$query .= "'".$object_type."', ";
			$query .= "'".$object_path."', ";
			$query .= "".$group_id." ";
			$query .= " )";
		}

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function client_change_area($id_client, $domain, $id, $realm) {

		// Проверяем, есть ли такая область ?
		// Если нет - тревога !

		$tmp_data = $this->client_get_area_of_client_by_id($id_client, $id);
		if ($tmp_data == FALSE) {
			$_SESSION["clientpanel_error"] = "Такая область не существует !";
			return FALSE;
		}

		$trans = array("+" => "++");
		$safe_realm = strtr($realm, $trans);
		$safe_realm = strtr($safe_realm, " ", "+");

		if ($tmp_data->method_type == "user") {
			$user_data = $this->client_get_user_area_of_client_by_id($id_client, $tmp_data->id_area_user_table);
			if ($user_data == FALSE) {
				$_SESSION["clientpanel_error"] = "Такой пользователь не существует !";
				return FALSE;
			}

			$method_name = $user_data->name;
		}
		else if ($tmp_data->method_type == "group") {
			$group_data = $this->client_get_group_area_of_client_by_id($id_client, $tmp_data->id_area_group_table);
			if ($group_data == FALSE) {
				$_SESSION["clientpanel_error"] = "Такая группа не существует !";
				return FALSE;
			}

			$method_name = $group_data->name;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->protect_update_area($domain, $tmp_data->item_type, $tmp_data->item, $safe_realm, $tmp_data->item_type, $tmp_data->item, $tmp_data->method_type, $method_name);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "UPDATE area_protect_table SET ";
		$query .= "title='".$realm."' ";
		$query .= " WHERE id_table=".$id;

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
			}

		$this->SQL->disconnect();
		return TRUE;
	}

	function client_remove_area($id_client, $domain, $id) {

		$tmp_data = $this->client_get_area_of_client_by_id($id_client, $id);
		if ($tmp_data == FALSE) {
			$_SESSION["clientpanel_error"] = "Такая область не существует !";
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->protect_remove_area($domain, $tmp_data->item_type, $tmp_data->item);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "DELETE FROM area_protect_table ";
		$query .= "WHERE id_table=".$id." ";
		$query .= "AND id_client_table=".$id_client;

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function client_remove_all_area($id_client, $domain) {

		$areas = $this->client_get_area_of_client($id_client);

		if ($areas == FALSE) {
			$_SESSION["clientpanel_error"] = "Не могу получить информацию: ".$_SESSION["clientpanel_error"];
			return FALSE;
		}

		for ($i = 0; $i < $this->SQL->get_num_rows($areas); $i++) {
			$data = $this->SQL->get_object($areas);

			if ($this->client_remove_area($id_client, $domain, $data->id_table) != TRUE) {
				$_SESSION["clientpanel_error"] = "Не могу удалить область";
				return FALSE;
			}
		}

		return TRUE;
	}

	function client_get_area_of_client($id_client) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, title, item, method_type, item_type, id_area_user_table, id_area_group_table
			FROM area_protect_table
			WHERE id_client_table=".$id_client."
			ORDER BY id_table");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function client_get_area_of_client_by_name($id_client, $name) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, title, item, method_type, item_type, id_area_user_table, id_area_group_table
			FROM area_protect_table
			WHERE id_client_table=".$id_client."
			AND title='".$name."'
			ORDER BY id_table");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function client_get_area_of_client_by_id($id_client, $id) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, title, item, method_type, item_type, id_area_user_table, id_area_group_table
			FROM area_protect_table
			WHERE id_client_table=".$id_client."
			AND id_table=".$id."
			ORDER BY id_table");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function client_get_area_of_client_by_user_id($id_client, $user_id) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, title, item, method_type, item_type, id_area_user_table, id_area_group_table
			FROM area_protect_table
			WHERE id_client_table=".$id_client."
			AND id_area_user_table=".$user_id."
			ORDER BY id_table");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function client_get_area_of_client_by_group_id($id_client, $group_id) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			id_table, title, item, method_type, item_type, id_area_user_table, id_area_group_table
			FROM area_protect_table
			WHERE id_client_table=".$id_client."
			AND id_area_group_table=".$group_id."
			ORDER BY id_table");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

}

?>