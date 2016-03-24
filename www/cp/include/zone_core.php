<?php

	include_once ($INCLUDE_PATH."define.php");

	include_once ($INCLUDE_PATH."sql_core.php");
	include_once ($INCLUDE_PATH."control.php");

	include_once ($INCLUDE_PATH."mail_core.php");
	include_once ($INCLUDE_PATH."ftp_core.php");
	include_once ($INCLUDE_PATH."area_core.php");
	include_once ($INCLUDE_PATH."mysql_core.php");
	include_once ($INCLUDE_PATH."pgsql_core.php");
	include_once ($INCLUDE_PATH."subdomain_core.php");
	include_once ($INCLUDE_PATH."web_core.php");

class Zone_Control {

	var $SQL;
	var $HC;

	var $MC;
	var $FC;
	var $AC;
	var $MyC;
	var $PgC;
	var $SDC;
	var $WC;

	var $PARENT_CC;

	function Zone_Control($parent) {

		$this->PARENT_CC = &$parent;

		if (isset($parent->SQL))
			$this->SQL = &$parent->SQL;
		else
			$this->SQL = new PGSQL;

		if (isset($parent->HC))
			$this->HC = &$parent->HC;
		else
			$this->HC = new Hosting_Control;

		$this->MC = new Mail_Control(&$this);
		$this->FC = new Ftp_Control(&$this);
		$this->AC = new Area_Control(&$this);
		$this->MyC = new MySQL_Control(&$this);
		$this->PgC = new PosgtreSQL_Control(&$this);
		$this->SDC = new SubDomain_Control(&$this);
		$this->WC = new Web_Control(&$this);
	}

	function add_zone($id_client, $zone_name, $zone_type, $service_type, $disk_space, $subdomain_max_count, $email_max_count, $email_alias_max_count, $email_reply_max_count, $ftp_max_count, $mysql_max_count, $pgsql_max_count, $cp, $ftp_access, $popimap_access, $smtp_access, $php, $cgi_perl, $ssi, $mysql, $pgsql, $stat, $error_pages, $secure_dir, $support, $backup) {

		global $Hosting_Service_Type;

		$zone_name = strtolower($zone_name);

		if (eregi("[^a-zA-Z0-9\\.]", $zone_name, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in zone_name. Allowed only A-Z, a-z, 0-9 and dot(.)";
			return FALSE;
		}

		// Есть ли такая зона уже ?
		// Если есть - тревога !
		$tmp_data = $this->fetch_zone_by_name($zone_name);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such zone already exist";
			return FALSE;
		}

		$system_login = domain_to_login($zone_name);

		// Извлекаем данные из таблицы клиента
		$client_data = $this->PARENT_CC->fetch_client_account($id_client);
		if ($client_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch client account: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->zone_create_dir_struct($client_data->login, $zone_name);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$result = $this->HC->create_system_user($system_login);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$ids = explode(" ", $result["result"]);
		$zone_uid = $ids[0];
		$zone_gid = $ids[1];

		$result = $this->HC->create_dns_zone($zone_name);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$our_zones = $this->fetch_all_our_zones();
		if ($our_zones == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch all zones: ".$_SESSION["adminpanel_error"];
			return;
		}

		$id_our_zone_table = 0;
		for ($i = 0; $i < $this->SQL->get_num_rows($our_zones); $i++) {
			$data = $this->SQL->get_object($our_zones);

			$a = substr($zone_name, strlen($zone_name) - strlen($data->name), strlen($zone_name));
			if ($a == $data->name) {
				$id_our_zone_table = $data->id_table;
				break;
			}
		}

		if ($service_type == "custom")
			$insert_service_type = "free";
		else
			$insert_service_type = $service_type;

		// create zone
		$query = "INSERT INTO zone_table (id_client_table, name, zone_type, service_type, id_our_zone_table, uid, gid) VALUES (";
		$query .= "".$id_client.", ";
		$query .= "'".$zone_name."', ";
		$query .= "'".$zone_type."', ";
		$query .= "'".$insert_service_type."', ";
		$query .= "".$id_our_zone_table.", ";
		$query .= "".$zone_uid.", ";
		$query .= "".$zone_gid." ";
		$query .= ")";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		// try to retrive zone id_table
		$result = $this->SQL->exec_query("SELECT
				id_table
				FROM zone_table
				WHERE name='".$zone_name."'
				AND id_client_table=".$id_client);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$id_zone = $data->id_table;

		// if admin himselft spicefied services
		if ($service_type != "custom") {

			$query = "INSERT INTO zone_service_table (
				id_zone_table, disk_space, subdomain_max_count,
				email_max_count, email_alias_max_count, email_reply_max_count,
				ftp_max_count, mysql_max_count, pgsql_max_count,
				cp, ftp_access, popimap_access, smtp_access,
				php, cgi_perl, ssi, mysql, pgsql, stat,
				error_pages, secure_dir, support, backup) VALUES (";
			$query .= "".$id_zone.", ";
			$query .= "".$Hosting_Service_Type[$service_type]["disk_space"].", ";
			$query .= "".$Hosting_Service_Type[$service_type]["subdomain_max_count"].", ";
			$query .= "".$Hosting_Service_Type[$service_type]["email_max_count"].", ";
			$query .= "".$Hosting_Service_Type[$service_type]["email_alias_max_count"].", ";
			$query .= "".$Hosting_Service_Type[$service_type]["email_reply_max_count"].", ";
			$query .= "".$Hosting_Service_Type[$service_type]["ftp_max_count"].", ";
			$query .= "".$Hosting_Service_Type[$service_type]["mysql_max_count"].", ";
			$query .= "".$Hosting_Service_Type[$service_type]["pgsql_max_count"].", ";
			$query .= "'".$Hosting_Service_Type[$service_type]["cp"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["ftp_access"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["popimap_access"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["smtp_access"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["php"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["cgi_perl"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["ssi"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["mysql"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["pgsql"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["stat"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["error_pages"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["secure_dir"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["support"]."', ";
			$query .= "'".$Hosting_Service_Type[$service_type]["backup"]."') ";

			$result = $this->SQL->exec_query($query);
			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] = $this->SQL->get_error();
				$this->SQL->disconnect();
				return FALSE;
			}
		}
		else {

			$query = "INSERT INTO zone_service_table (
				id_zone_table, disk_space, subdomain_max_count,
				email_max_count, email_alias_max_count, email_reply_max_count,
				ftp_max_count, mysql_max_count, pgsql_max_count,
				cp, ftp_access, popimap_access, smtp_access,
				php, cgi_perl, ssi, mysql, pgsql, stat,
				error_pages, secure_dir, support, backup) VALUES (";
			$query .= "".$id_zone.", ";
			$query .= "".$disk_space.", ";
			$query .= "".$subdomain_max_count.", ";
			$query .= "".$email_max_count.", ";
			$query .= "".$email_alias_max_count.", ";
			$query .= "".$email_reply_max_count.", ";
			$query .= "".$ftp_max_count.", ";
			$query .= "".$mysql_max_count.", ";
			$query .= "".$pgsql_max_count.", ";
			$query .= "'".($cp=="on"?"1":"0")."', ";
			$query .= "'".($ftp_access=="on"?"1":"0")."', ";
			$query .= "'".($popimap_access=="on"?"1":"0")."', ";
			$query .= "'".($smtp_access=="on"?"1":"0")."', ";
			$query .= "'".($php=="on"?"1":"0")."', ";
			$query .= "'".($cgi_perl=="on"?"1":"0")."', ";
			$query .= "'".($ssi=="on"?"1":"0")."', ";
			$query .= "'".($mysql=="on"?"1":"0")."', ";
			$query .= "'".($pgsql=="on"?"1":"0")."', ";
			$query .= "'".($stat=="on"?"1":"0")."', ";
			$query .= "'".($error_pages=="on"?"1":"0")."', ";
			$query .= "'".($secure_dir=="on"?"1":"0")."', ";
			$query .= "'".($support=="on"?"1":"0")."', ";
			$query .= "'".($backup=="on"?"1":"0")."') ";

			$result = $this->SQL->exec_query($query);
			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] = $this->SQL->get_error();
				$this->SQL->disconnect();
				return FALSE;
			}
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function change_zone($id, $new_zone_data) {

		$new_zone_data["zone_name"] = strtolower($new_zone_data["zone_name"]);

		if (eregi("[^a-zA-Z0-9\\.]", $new_zone_data["zone_name"], $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in zone_name. Allowed only A-Z, a-z, 0-9 and dot(.)";
			return FALSE;
		}

		// Есть ли такая зона уже ?
		// Если есть и она не старая - тревога !
		$tmp_data = $this->fetch_zone_by_name($new_zone_data["zone_name"]);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id) {
			$_SESSION["adminpanel_error"] = "Such zone already exist";
			return FALSE;
		}


		// Prepearing data...

		$old_zone_data = $this->fetch_zone($id);
		if ($old_zone_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch zone: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$old_zone_service_data = $this->fetch_zone_service($id);
		if ($old_zone_service_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch old service of zone: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$old_zone_data->service = $old_zone_service_data;
		$old_zone_data->system_login = domain_to_login($old_zone_data->name);

		$new_zone_data["system_login"] = domain_to_login($new_zone_data["zone_name"]);

		$client_data = $this->PARENT_CC->fetch_client_account($old_zone_data->id_client_table);
		if ($client_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch client account: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		if ($old_zone_data->name != $new_zone_data["zone_name"]) {
			$_SESSION["adminpanel_error"] = "Change zone name: such capability does not supported yet !";
			return FALSE;
		}

		// Well, now begin to make changes
		// Firs of all, we will change zone restrictions

		$result = $this->change_zone_restrictions($old_zone_data, $new_zone_data);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't change zone restrictions: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Now, change ID of zone
		$result = $this->change_zone_ID($old_zone_data, $new_zone_data);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't change zone ID: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$query = "UPDATE zone_table SET ";
		$query .= "name='".$new_zone_data["zone_name"]."', ";
		$query .= "zone_type='".$new_zone_data["zone_type"]."', ";
		$query .= "service_type='".$new_zone_data["service_type"]."' ";
		$query .= "WHERE id_table=".$old_zone_data->id_table;

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't update table: ".$this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function change_zone_restrictions($old_zone_data, $new_zone_data) {

		// Here we will change zone resctions and zone's Webdirs

		// Well, if Administrator set new `Service Type`, then we should
		// ignore restriction chechboxs.

		// IF He leave unchanged `Service Type`, when wee should
		// apply restriction chechbox settings

		global $Hosting_Service_Type;

		$this->SQL->connect();

/*
		echo "OLD:<BR><pre>";
		print_r($old_zone_data);
		echo "</pre>";

		echo "<BR>NEW:<BR><pre>";
		print_r($new_zone_data);
		echo "</pre>";
*/
		if ($old_zone_data->service_type != $new_zone_data["service_type"]) {

			$new_cgi_perl = $Hosting_Service_Type[$new_zone_data["service_type"]]["cgi_perl"]=="on"?"1":"0";
			$new_ssi = $Hosting_Service_Type[$new_zone_data["service_type"]]["ssi"]=="on"?"1":"0";

			$query = "UPDATE zone_service_table SET ";
			$query .= "disk_space=".$Hosting_Service_Type[$new_zone_data["service_type"]]["disk_space"].", ";
			$query .= "subdomain_max_count=".$Hosting_Service_Type[$new_zone_data["service_type"]]["subdomain_max_count"].", ";
			$query .= "email_max_count=".$Hosting_Service_Type[$new_zone_data["service_type"]]["email_max_count"].", ";
			$query .= "email_alias_max_count=".$Hosting_Service_Type[$new_zone_data["service_type"]]["email_alias_max_count"].", ";
			$query .= "email_reply_max_count=".$Hosting_Service_Type[$new_zone_data["service_type"]]["email_reply_max_count"].", ";
			$query .= "ftp_max_count=".$Hosting_Service_Type[$new_zone_data["service_type"]]["ftp_max_count"].", ";
			$query .= "mysql_max_count=".$Hosting_Service_Type[$new_zone_data["service_type"]]["mysql_max_count"].", ";
			$query .= "pgsql_max_count=".$Hosting_Service_Type[$new_zone_data["service_type"]]["pgsql_max_count"].", ";
			$query .= "cp='".($Hosting_Service_Type[$new_zone_data["service_type"]]["cp"]=="on"?"1":"0")."', ";
			$query .= "ftp_access='".($Hosting_Service_Type[$new_zone_data["service_type"]]["ftp_access"]=="on"?"1":"0")."', ";
			$query .= "popimap_access='".($Hosting_Service_Type[$new_zone_data["service_type"]]["popimap_access"]=="on"?"1":"0")."', ";
			$query .= "smtp_access='".($Hosting_Service_Type[$new_zone_data["service_type"]]["smtp_access"]=="on"?"1":"0")."', ";
			$query .= "php='".($Hosting_Service_Type[$new_zone_data["service_type"]]["php"]=="on"?"1":"0")."', ";
			$query .= "cgi_perl='".($Hosting_Service_Type[$new_zone_data["service_type"]]["cgi_perl"]=="on"?"1":"0")."', ";
			$query .= "ssi='".($Hosting_Service_Type[$new_zone_data["service_type"]]["ssi"]=="on"?"1":"0")."', ";
			$query .= "mysql='".($Hosting_Service_Type[$new_zone_data["service_type"]]["mysql"]=="on"?"1":"0")."', ";
			$query .= "pgsql='".($Hosting_Service_Type[$new_zone_data["service_type"]]["pgsql"]=="on"?"1":"0")."', ";
			$query .= "stat='".($Hosting_Service_Type[$new_zone_data["service_type"]]["stat"]=="on"?"1":"0")."', ";
			$query .= "error_pages='".($Hosting_Service_Type[$new_zone_data["service_type"]]["error_pages"]=="on"?"1":"0")."', ";
			$query .= "secure_dir='".($Hosting_Service_Type[$new_zone_data["service_type"]]["secure_dir"]=="on"?"1":"0")."', ";
			$query .= "support='".($Hosting_Service_Type[$new_zone_data["service_type"]]["support"]=="on"?"1":"0")."', ";
			$query .= "backup='".($Hosting_Service_Type[$new_zone_data["service_type"]]["backup"]=="on"?"1":"0")."' ";
			$query .= "WHERE id_zone_table=".$old_zone_data->id_table."";
		}
		else {
			$new_cgi_perl = $new_zone_data["cgi_perl"]=="on"?"1":"0";
			$new_ssi = $new_zone_data["ssi"]=="on"?"1":"0";

			$query = "UPDATE zone_service_table SET ";
			$query .= "disk_space=".$new_zone_data["disk_space"].", ";
			$query .= "subdomain_max_count=".$new_zone_data["subdomain_max_count"].", ";
			$query .= "email_max_count=".$new_zone_data["email_max_count"].", ";
			$query .= "email_alias_max_count=".$new_zone_data["email_alias_max_count"].", ";
			$query .= "email_reply_max_count=".$new_zone_data["email_reply_max_count"].", ";
			$query .= "ftp_max_count=".$new_zone_data["ftp_max_count"].", ";
			$query .= "mysql_max_count=".$new_zone_data["mysql_max_count"].", ";
			$query .= "pgsql_max_count=".$new_zone_data["pgsql_max_count"].", ";
			$query .= "cp='".($new_zone_data["cp"]=="on"?"1":"0")."', ";
			$query .= "ftp_access='".($new_zone_data["ftp_access"]=="on"?"1":"0")."', ";
			$query .= "popimap_access='".($new_zone_data["popimap_access"]=="on"?"1":"0")."', ";
			$query .= "smtp_access='".($new_zone_data["smtp_access"]=="on"?"1":"0")."', ";
			$query .= "php='".($new_zone_data["php"]=="on"?"1":"0")."', ";
			$query .= "cgi_perl='".($new_zone_data["cgi_perl"]=="on"?"1":"0")."', ";
			$query .= "ssi='".($new_zone_data["ssi"]=="on"?"1":"0")."', ";
			$query .= "mysql='".($new_zone_data["mysql"]=="on"?"1":"0")."', ";
			$query .= "pgsql='".($new_zone_data["pgsql"]=="on"?"1":"0")."', ";
			$query .= "stat='".($new_zone_data["stat"]=="on"?"1":"0")."', ";
			$query .= "error_pages='".($new_zone_data["error_pages"]=="on"?"1":"0")."', ";
			$query .= "secure_dir='".($new_zone_data["secure_dir"]=="on"?"1":"0")."', ";
			$query .= "support='".($new_zone_data["support"]=="on"?"1":"0")."', ";
			$query .= "backup='".($new_zone_data["backup"]=="on"?"1":"0")."' ";
			$query .= "WHERE id_zone_table=".$old_zone_data->id_table."";
		}

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't update table: ".$this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$webdirs = $this->WC->fetch_all_webdirs_of_zone($old_zone_data->id_table);
		if ($webdirs == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch all webdirs of zone".$_SESSION["adminpanel_error"];
			$this->SQL->disconnect();
			return FALSE;
		}

		for ($i = 0; $i < count($webdirs); $i++) {

			$webdir_data = $this->WC->SQL->get_object($webdirs);

			$result = $this->WC->change_webdir($webdir_data->id_table,
						$webdir_data->domain_lite,
						$webdir_data->id_zone_table,
						$webdir_data->rootdir);

			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't change webdir (".$webdir_data->domain."): ".$_SESSION["adminpanel_error"];
				$this->SQL->disconnect();
				return FALSE;
			}
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function change_zone_ID($old_zone_data, $new_zone_data) {

		// Here we will change zone ID, i.e. change UID and GID of zone's files
		// Also we will change ID of zone's ftp and mail accounts

/*
		echo "OLD:<BR><pre>";
		print_r($old_zone_data);
		echo "</pre>";

		echo "<BR>NEW:<BR><pre>";
		print_r($new_zone_data);
		echo "</pre>";
*/
		if ($old_zone_data->uid == $new_zone_data["uid"] &&
			$old_zone_data->gid == $new_zone_data["gid"]) {
			// They are the same... skip this stage
			return TRUE;
		}

		// chech new ID for existence
		$result = $this->check_ID_existence($new_zone_data["uid"], $new_zone_data["gid"]);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = "Failed on check ID existence: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// first of all, try to change ID of ftp
		$ftps = $this->FC->fetch_all_ftps_of_zone($old_zone_data->id_table);
		if ($ftps == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch all ftps of zone".$_SESSION["adminpanel_error"];
			$this->SQL->disconnect();
			return FALSE;
		}

		for ($i = 0; $i < count($ftps); $i++) {

			$ftp_data = $this->FC->SQL->get_object($ftps);

			if ($ftp_data->uid != $old_zone_data->uid ||
				$ftp_data->gid != $old_zone_data->gid) {
				// Account has another predefined ID - skip him
				continue;
			}

			$result = $this->FC->change_ftp_account($ftp_data->id_table,
						$ftp_data->login,
						$ftp_data->id_zone_table,
						"",
						$ftp_data->rootdir,
						$new_zone_data["uid"],
						$new_zone_data["gid"]);

			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't change ftp account (".$ftp_data->login."): ".$_SESSION["adminpanel_error"];
				$this->SQL->disconnect();
				return FALSE;
			}
		}

		// at second, try to change ID of mails
		$mails = $this->MC->fetch_all_mails_of_zone($old_zone_data->id_table);
		if ($mails == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch all mails of zone".$_SESSION["adminpanel_error"];
			$this->SQL->disconnect();
			return FALSE;
		}

		for ($i = 0; $i < count($mails); $i++) {

			$mail_data = $this->MC->SQL->get_object($mails);

			if ($mail_data->uid != $old_zone_data->uid ||
				$mail_data->gid != $old_zone_data->gid) {
				// Account has another predefined ID - skip him
				continue;
			}

			$forward_data = $this->MC->fetch_forward_of_mail($mail_data->login);
			if ($forward_data == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't fetch forward info of mail (".$mail_data->login."): ".$_SESSION["adminpanel_error"];
				$this->SQL->disconnect();
				return FALSE;
			}

			$reply_data = $this->MC->fetch_reply_of_mail($mail_data->login);
			if ($reply_data == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't fetch reply info of mail (".$mail_data->login."): ".$_SESSION["adminpanel_error"];
				$this->SQL->disconnect();
				return FALSE;
			}

			$result = $this->MC->change_mail_account($mail_data->id_table,
						$mail_data->login,
						"",
						$mail_data->maildir,
						$mail_data->home,
						$mail_data->name,
						$new_zone_data["uid"],
						$new_zone_data["gid"],
						$forward_data->forward_do=="t"?"on":"",
						$forward_data->forward_address,
						$reply_data->reply_do=="t"?"on":"",
						$reply_data->reply);

			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't change mail account (".$mail_data->login."): ".$_SESSION["adminpanel_error"];
				$this->SQL->disconnect();
				return FALSE;
			}
		}
/*
		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->update_system_user($old_zone_data->system_login, $new_zone_data["system_login"], $new_zone_data["uid"], $new_zone_data["gid"]);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Can't update system user: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$result = $this->HC->change_zone_ID($old_zone_data->name, $new_zone_data["uid"], $new_zone_data["gid"]);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Can't change zone ID: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();
*/

		$this->SQL->connect();

		$query = "UPDATE zone_table SET ";
		$query .= "uid=".$new_zone_data["uid"].", ";
		$query .= "gid=".$new_zone_data["gid"]." ";
		$query .= "WHERE id_table=".$old_zone_data->id_table;

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't update table: ".$this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function check_ID_existence($uid, $gid) {

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->fetch_ids();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Can't fetch ids: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$ids = array();

		$str = explode("\n", $result["result"]);
		for ($i = 0; $i < count($str); $i++) {
			$id = explode(" ", $str[$i]);

			$tmp["uid"] = $id[1];
			$tmp["gid"] = $id[2];

			array_push($ids, $tmp);
		}

		$this->HC->disconnect();

		$exists = FALSE;
		for ($i = 0; $i < count($ids); $i++)
			if ($ids[$i]["uid"] == $uid &&
				$ids[$i]["gid"] == $gid) {
				$exists = TRUE;
				break;
			}

		if ($exists == FALSE) {
			$_SESSION["adminpanel_error"] = "Such combination of Uid and Gid doesn't exists";
			return FALSE;
		}

		return TRUE;
	}

	function remove_zone($id) {

		$old_zone_data = $this->fetch_zone($id);
		if ($old_zone_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch zone: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$old_zone_data->system_login = domain_to_login($old_zone_data->name);

		// Извлекаем данные из таблицы клиента
		$client_data = $this->PARENT_CC->fetch_client_account($old_zone_data->id_client_table);
		if ($client_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch client account: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->zone_remove_dir_struct($client_data->login, $old_zone_data->name);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$result = $this->HC->remove_dns_zone($old_zone_data->name);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$result = $this->HC->remove_system_user($old_zone_data->system_login);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$result = $this->WC->remove_redirector_by_domain($old_zone_data->name);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] .= "Can't remove redirector: ";
			return FALSE;
		}

		$result = $this->WC->remove_webdir_by_domain($old_zone_data->name);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] .= "Can't remove webdir: ";
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM zone_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function fetch_zone($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*
			FROM zone_table zt
			WHERE zt.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_zone_by_name($name) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*
			FROM zone_table zt
			WHERE zt.name='".$name."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_zones() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, zst.disk_space AS quota
			FROM zone_table zt, client_table ct, zone_service_table zst
			WHERE ct.id_table = zt.id_client_table
			AND zst.id_zone_table = zt.id_table
			ORDER BY name, zone_type, service_type");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_of_client($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, zst.disk_space AS quota
			FROM zone_table zt, client_table ct, zone_service_table zst
			WHERE ct.id_table = zt.id_client_table
			AND zst.id_zone_table = zt.id_table
			AND ct.id_table = ".$id_client."
			ORDER BY name, zone_type, service_type");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_zone_quota($id_zone) {

/*		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*
			FROM zone_table zt
			WHERE zt.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();
*/
		$data = 1003783;

		return $data;
	}

	function fetch_zone_service($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zst.*
			FROM zone_service_table zst
			WHERE zst.id_zone_table=".$id_zone);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();

			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_zones_for_mail() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(mt.id_table)
					FROM mail_table mt
					WHERE mt.id_zone_table = zt.id_table
				) AS mail_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_of_client_for_mail($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(mt.id_table)
					FROM mail_table mt
					WHERE mt.id_zone_table = zt.id_table
				) AS mail_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			AND ct.id_table=".$id_client."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_for_mail_alias() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(malt.id_table)
					FROM mail_alias_table malt
					WHERE malt.id_zone_table = zt.id_table
				) AS alias_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_of_client_for_mail_alias($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(malt.id_table)
					FROM mail_alias_table malt
					WHERE malt.id_zone_table = zt.id_table
				) AS alias_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			AND ct.id_table=".$id_client."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_for_mail_forwarders() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(mft.id_table)
					FROM mail_forward_table mft
					WHERE mft.id_zone_table = zt.id_table
				) AS forwarder_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_of_client_for_mail_forwarders($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(mft.id_table)
					FROM mail_forward_table mft
					WHERE mft.id_zone_table = zt.id_table
				) AS forwarder_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			AND ct.id_table=".$id_client."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_for_mail_autoreplies() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(mat.id_table)
					FROM mail_autoreply_table mat
					WHERE mat.id_zone_table = zt.id_table
				) AS autoreply_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_of_client_for_mail_autoreplies($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(mat.id_table)
					FROM mail_autoreply_table mat
					WHERE mat.id_zone_table = zt.id_table
				) AS autoreply_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			AND ct.id_table=".$id_client."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_for_maillist() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(mlt.id_table)
					FROM mailing_list_table mlt
					WHERE mlt.id_zone_table = zt.id_table
				) AS maillist_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_of_client_for_maillist($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(mlt.id_table)
					FROM mailing_list_table mlt
					WHERE mlt.id_zone_table = zt.id_table
				) AS maillist_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			AND ct.id_table=".$id_client."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_for_ftp() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(ft.id_table)
					FROM ftp_table ft
					WHERE ft.id_zone_table = zt.id_table
				) AS ftp_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_of_client_for_ftp($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(ft.id_table)
					FROM ftp_table ft
					WHERE ft.id_zone_table = zt.id_table
				) AS ftp_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			AND ct.id_table=".$id_client."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_for_redirect() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(rt.id_table)
					FROM redirect_table rt
					WHERE rt.id_zone_table = zt.id_table
				) AS redirector_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_of_client_for_redirect($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(rt.id_table)
					FROM redirect_table rt
					WHERE rt.id_zone_table = zt.id_table
				) AS redirector_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			AND ct.id_table=".$id_client."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_for_webdir() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(wt.id_table)
					FROM webdir_table wt
					WHERE wt.id_zone_table = zt.id_table
				) AS webdir_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_of_client_for_webdir($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.*, ct.login AS client, (
					SELECT COUNT(wt.id_table)
					FROM webdir_table wt
					WHERE wt.id_zone_table = zt.id_table
				) AS webdir_count
			FROM zone_table zt, client_table ct
			WHERE ct.id_table = zt.id_client_table
			AND ct.id_table=".$id_client."
			ORDER BY name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

//////////////////////////////////////////////////
//		NEXT IS OUR ZONES
//////////////////////////////////////////////////

	function add_our_zone($name) {

		$name = strtolower($name);

		// Есть ли такая зона уже ?
		// Если есть - тревога !
		$tmp_data = $this->fetch_our_zone_by_name($name);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such our zone already exist";
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->create_dns_zone($name);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "INSERT INTO our_zone_table (name) VALUES (";
		$query .= "'".$name."' )";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function change_our_zone($id, $name) {

		$name = strtolower($name);

		// Есть ли такая зона уже ?
		// Если есть и она не старая - тревога !
		$tmp_data = $this->fetch_our_zone_by_name($name);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id) {
			$_SESSION["adminpanel_error"] = "Such our zone already exist";
			return FALSE;
		}

		// Это самый смелый поступок -
		// изменять доменную зону. Ну что ж, прошу...

		$old_zone_data = $this->fetch_our_zone($id);
		if ($old_zone_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch our zone: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->rename_dns_zone($old_zone_data->name, $name);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		// Теперь обработаем те домены, которые
		// принадлежали этой зоне
/*
		// Возмем все домены
		$domains_result = $this->DC->fetch_all_domains();
		if ($domains_result == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch all domains: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// И если какая-нибудь находится в зоне,
		// которую мы щас будем менять - изменим
		// сначала этот домен
		for ($i = 0; $i < $this->ZC->SQL->get_num_rows($domains_result); $i++) {
			$domain_data = $this->ZC->SQL->get_object($domains_result);

			if ($domain_data->zone_id == $id) {
				// Да ! Это тот самый домен !
				// Обновим его !
				if ($this->DC->change_domain($domain_data->id_table, $name, $domain_data->name, TRUE) != TRUE) {
					echo "Domain: ".$domain_data->name." (id: ".$domain_data->id_table.")<br>Can't change domain: ".$_SESSION["adminpanel_error"]."<br>";
				}
			}
		}
*/
		// Теперь просто обновим таблицу

		$this->SQL->connect();

		$query = "UPDATE our_zone_table SET ";
		$query .= "name='".$name."' ";
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

	function remove_our_zone($id) {

		$data = $this->fetch_our_zone($id);
		if ($data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch our zone: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->remove_dns_zone($data->name);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$result = $this->WC->remove_redirector_by_domain($data->name);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] .= "Can't remove redirector: ";
			return FALSE;
		}

		$result = $this->WC->remove_webdir_by_domain($data->name);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] .= "Can't remove webdir: ";
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM our_zone_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function fetch_our_zone_by_name($name) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT * FROM our_zone_table WHERE name='".$name."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_our_zone($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT * FROM our_zone_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_our_zones() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
				*
				FROM
				our_zone_table ozt,
				(
					(
						SELECT ozt.id_table, COUNT(zt.*)
						FROM our_zone_table ozt, zone_table zt
						WHERE ozt.id_table=zt.id_our_zone_table
						GROUP BY ozt.id_table
					)
					UNION
					(
						SELECT ozt.id_table, 0
						FROM our_zone_table ozt
						WHERE ozt.id_table NOT IN
						(
							SELECT id_our_zone_table FROM zone_table
						)
					)
				) ozt2
				WHERE ozt.id_table= ozt2.id_table ");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_our_zone_hosting_zones($id_our_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			*
			FROM zone_table
			WHERE id_our_zone_table=".$id_our_zone);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}


//////////////////////////////////////////////////
//		NEXT IS MANAGE OUR ZONES
//////////////////////////////////////////////////


	function fetch_all_subdomains_of_our_zone($id_our_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ozst.*
			FROM our_zone_subdomain_table ozst
			WHERE ozst.id_our_zone_table = ".$id_our_zone."
			ORDER BY f_name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_our_zone_info($id_our_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ozt.name
			FROM our_zone_table ozt
			WHERE ozt.id_table = ".$id_our_zone);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		$zone_data = $this->SQL->get_object($result);

		$this->HC->connect();
		$result2 = $this->HC->info_zone_info($zone_data->name, "MX");
		$this->HC->disconnect();

		if ($result2["error"]) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result2["mess"];
			return FALSE;
		}

		$res = Array();

		$s1 = explode("\n", $result2["result"]);
		for($i = 0; $i < count($s1); $i++) {
			if ($s1[$i] == "")
				continue;
			$s2 = explode(" ", $s1[$i]);
			$res2["prior"] = $s2[0];
			$res2["name"] = $s2[1];
			array_push($res, $res2);
		}

		return $res;
	}

	function fetch_our_zone_plain_text($id_our_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ozt.name
			FROM our_zone_table ozt
			WHERE ozt.id_table = ".$id_our_zone);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		$zone_data = $this->SQL->get_object($result);

		$this->HC->connect();
		$result2 = $this->HC->info_zone($zone_data->name);
		$this->HC->disconnect();

		if ($result2["error"]) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result2["mess"];
			return FALSE;
		}

		return $result2["result"];
	}

	// if we update zone manually
	function add_our_zone_subdomain_in_db($id_our_zone, $domain, $type, $prior, $record) {

		$domain = strtolower($domain);
		$record = strtolower($record);

		if (eregi("[^a-zA-Z0-9\\.]", $domain, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in domain. Allowed only A-Z, a-z, 0-9 and dot(.)";
			return FALSE;
		}

		$this->SQL->connect();

		$zone_data = $this->PARENT_ZC->fetch_our_zone($id_our_zone);
		if ($zone_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch our zone: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Проверяем, есть ли такой redirect ?
		$tmp_data = $this->WC->fetch_redirector_by_domain($domain.".".$zone_data->name);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Subdomain '".$domain.".".$zone_data->name."': Such redirect already exist !";
			return FALSE;
		}

		$tmp_data = $this->WC->fetch_webdir_by_domain($domain.".".$zone_data->name);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Subdomain '".$domain.".".$zone_data->name."': Such webdir already exist !";
			return FALSE;
		}

		$result = $this->SQL->exec_query("INSERT INTO
			our_zone_subdomain_table (id_zone_table, f_name, name, type,".($type=="MX"?" prior,":"")." record)
			VALUES (
			".$id_zone.",
			'".$domain.".".$zone_data->name."',
			'".$domain."',
			'".$type."',
			".($type=="MX"?$prior.",":"")."
			'".$record."')");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function change_our_zone_subdomain_manually($zone_name, $zone_content) {

		$zone_name = strtolower($zone_name);

		$zone_content = base64_encode($zone_content);

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->update_zone_manual($zone_name, $zone_content);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		return TRUE;
	}

	// if we update zone manually
	function remove_all_subdomains_of_our_zone_in_db($id_our_zone) {

		$this->SQL->connect();

		// first, remove all redirectors and webdirs what refer to this domain
		$subdomains = $this->fetch_all_subdomains_of_our_zone($id_our_zone);
		if ($subdomains == FALSE) {
			echo "Can't fetch all subdomains of our zone: ".$_SESSION["adminpanel_error"];
			$this->SQL->disconnect();
			return FALSE;
		}

		for ($i = 0; $i < $this->SQL->get_num_rows($subdomains); $i++) {
			$data = $this->SQL->get_object($subdomains);

			$result = $this->WC->remove_redirector_by_domain($data->f_name);
			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] .= "Can't remove redirector: ";
				return FALSE;
			}

			$result = $this->WC->remove_webdir_by_domain($data->f_name);
			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] .= "Can't remove webdir: ";
				return FALSE;
			}
		}

		// now, flush table
		$result = $this->SQL->exec_query("DELETE FROM our_zone_subdomain_table WHERE id_our_zone_table=".$id_our_zone);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function create_mx_record_in_dns($zone_name, $prior, $record) {

		$zone_name = strtolower($zone_name);
		$record = strtolower($record);

		$this->HC->connect();

		$result = $this->HC->create_zone_info($zone_name, "MX", $prior, $record);

		$this->HC->disconnect();

		if ($result["error"]) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		return TRUE;
	}

	function update_mx_record_in_dns($zone_name, $old_prior, $prior, $record) {

		$zone_name = strtolower($zone_name);
		$record = strtolower($record);

		$this->HC->connect();

		$result = $this->HC->update_zone_info($zone_name, "MX", $old_prior, "MX", $prior, $record);

		$this->HC->disconnect();

		if ($result["error"]) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		return TRUE;
	}

	function remove_mx_record_in_dns($zone_name, $prior) {

		$this->HC->connect();

		$result = $this->HC->remove_zone_info($zone_name, "MX", $prior, "");

		$this->HC->disconnect();

		if ($result["error"]) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		return TRUE;
	}

	function create_domain_record($id_our_zone, $zone_name, $domain, $type, $prior, $record) {

		$zone_name = strtolower($zone_name);
		$record = strtolower($record);

		// Есть ли  уже такой домен ?
		$tmp_data = $this->fetch_detailed_our_zone_subdomain($id_our_zone, $domain, $type, $prior, $record);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such domain already exists";
			return FALSE;
		}

		// Проверяем, есть ли такой redirect ?
		$tmp_data = $this->PARENT_ZC->WC->fetch_redirector_by_domain($domain.".".$zone_name);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Subdomain '".$domain.".".$zone_name."': Redirect for such domain already exist !";
			return FALSE;
		}

		// Проверяем, есть ли такой webdir ?
		$tmp_data = $this->PARENT_ZC->WC->fetch_webdir_by_domain($domain.".".$zone_name);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Subdomain '".$domain.".".$zone_name."': Webdir for such domain already exist !";
			return FALSE;
		}

		$result = $this->HC->connect();

		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->create_dns_domain($zone_name, $domain, $type, $prior, $record);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "INSERT INTO our_zone_subdomain_table (id_our_zone_table, f_name, name, type, prior, record) VALUES (";
		$query .= "".$id_our_zone.", ";
		$query .= "'".$domain.".".$zone_name."', ";
		$query .= "'".$domain."', ";
		$query .= "'".$type."', ";
		$query .= "".$prior.", ";
		$query .= "'".$record."' ";
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

	function update_domain_record($id_our_zone, $zone_name, $id_domain, $old_domain, $old_type, $old_prior, $domain, $type, $prior, $record) {

		$zone_name = strtolower($zone_name);
		$old_domain = strtolower($old_domain);
		$domain = strtolower($domain);
		$record = strtolower($record);

		// Есть ли  уже такой домен ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->fetch_detailed_our_zone_subdomain($id_our_zone, $domain, $type, $prior, $record);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such domain already exists";
			return FALSE;
		}

		// Проверяем, есть ли такой redirect ?
		$tmp_data = $this->PARENT_ZC->WC->fetch_redirector_by_domain($domain.".".$zone_name);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Subdomain '".$domain.".".$zone_name."': Redirect for such domain already exist !";
			return FALSE;
		}

		// Проверяем, есть ли такой webdir ?
		$tmp_data = $this->PARENT_ZC->WC->fetch_webdir_by_domain($domain.".".$zone_name);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Subdomain '".$domain.".".$zone_name."': Webdir for such domain already exist !";
			return FALSE;
		}

		$result = $this->HC->connect();

		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->rename_dns_domain($zone_name, $old_domain, $old_type, $old_prior, $domain, $type, $prior, $record);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "UPDATE our_zone_subdomain_table SET ";
		$query .= "id_our_zone_table=".$id_our_zone.", ";
		$query .= "f_name='".$domain.".".$zone_name."', ";
		$query .= "name='".$domain."', ";
		$query .= "type='".$type."', ";
		$query .= "prior=".$prior.", ";
		$query .= "record='".$record."' ";
		$query .= " WHERE id_table=".$id_domain."";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function remove_domain_record($zone_name, $id_domain, $domain, $type, $prior) {

		$zone_name = strtolower($zone_name);
		$domain = strtolower($domain);

		$result = $this->HC->connect();

		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->remove_dns_domain($zone_name, $domain, $type, $prior);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$result = $this->WC->remove_redirector_by_domain($zone_name.".".$domain);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] .= "Can't remove redirector: ";
			return FALSE;
		}

		$result = $this->WC->remove_webdir_by_domain($zone_name.".".$domain);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] .= "Can't remove webdir: ";
			return FALSE;
		}

		$this->SQL->connect();

		$query = "DELETE FROM our_zone_subdomain_table ";
		$query .= "WHERE id_table=".$id_domain;

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function fetch_detailed_our_zone_subdomain($id_our_zone, $domain, $type, $prior, $record) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ozdt.*
			FROM our_zone_subdomain_table ozdt
			WHERE ozdt.id_our_zone_table = ".$id_our_zone."
			AND ozdt.name='".$domain."'
			AND ozdt.type='".$type."'
			AND ozdt.prior=".$prior."
			AND ozdt.record='".$record."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_our_zone_subdomain_by_domain($subdomain) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ozdt.*
			FROM our_zone_subdomain_table ozdt
			WHERE ozdt.f_name='".$subdomain."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

}

?>