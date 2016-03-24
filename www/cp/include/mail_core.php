<?php

	include_once ($INCLUDE_PATH."define.php");

	include_once ($INCLUDE_PATH."sql_core.php");
	include_once ($INCLUDE_PATH."control.php");

class Mail_Control {

	var $SQL;
	var $HC;

	var $PARENT_ZC;

	function Mail_Control($parent) {

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

	function add_mail_account($login, $passwd, $name, $forward_do, $forward, $reply_do, $reply_text) {

		global $Clients_dir, $Alone_dir, $MAIL_UID, $HOSTING_GID;

		$login = strtolower($login);
		$forward = strtolower($forward);

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $login, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in login. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($login, "@") == 0 || substr($login, 0, strpos($login, "@")) == "" || substr($login, strpos($login, "@")+1, strlen($login)) == "") {
			$_SESSION["adminpanel_error"] = "Such e-mail address is not valid !";
			return FALSE;
		}

		// Проверяем, есть ли такой e-mail ?
		// Если да - тревога !
		$tmp_data = $this->fetch_mail_by_login($login);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such e-mail already exist !";
			return FALSE;
		}

		$clear_login = substr($login, 0, strpos($login, "@"));
		$clear_email_domain = substr($login, strpos($login, "@")+1, strlen($login));
		$zone_domain = $clear_email_domain;

		// Попытка найти доменное имя:
		// Ищем в зонах, и в поддоменах клиентов
		$find_zone = TRUE;

		// ищем в доменных зонах
		$zone_data = $this->PARENT_ZC->fetch_zone_by_name($clear_email_domain);
		if ($zone_data == FALSE) {
			// ищем в поддоменах клиентов
			$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($clear_email_domain);
			if ($subdomain_data == TRUE) {
				$zone_data = $this->PARENT_ZC->fetch_zone($subdomain_data->id_zone_table);
				if ($zone_data == FALSE) {
					$_SESSION["adminpanel_error"] = "Can't fetch zone: ".$_SESSION["adminpanel_error"];
					return FALSE;
				}
				$zone_domain = $zone_data->name;
			}
			else {
				$find_zone = FALSE;
			}
		}

		if ($find_zone == FALSE) {
			$zone_data->uid = $MAIL_UID;
			$zone_data->gid = $HOSTING_GID;
		}

		$result = $this->PARENT_ZC->check_ID_existence($zone_data->uid, $zone_data->gid);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = "Failed on check ID existence: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Итак, если домен не нашелся, значит его попросту нет
		// В таком случае, необходимо создать структуру каталогов
		if ($find_zone == FALSE) {
			$result = $this->HC->client_create_dir_struct($Alone_dir);
			if ($result["error"] == TRUE) {
				$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
				$this->HC->disconnect();
				return FALSE;
			}

			$result = $this->HC->zone_create_dir_struct($Alone_dir, $zone_domain);
			if ($result["error"] == TRUE) {
				$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
				$this->HC->disconnect();
				return FALSE;
			}

			$zone_data->id_table = 0;
			$client_name = $Alone_dir;
		}
		else {
			$client_data = $this->PARENT_ZC->PARENT_CC->fetch_client_account($zone_data->id_client_table);
			if ($client_data == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't fetch client: ".$_SESSION["adminpanel_error"];
				return FALSE;
			}
			$client_name = $client_data->login;
		}

		$result = $this->HC->create_mail($client_name, $zone_domain, $login);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$maildir = $Clients_dir."/".$client_name."/".$zone_domain."/mail/".$login;
		$home = $maildir;

		$query = "INSERT INTO mail_table (".($zone_data->id_table!=0?"id_zone_table, ":"dummy, ")."domain, login, passwd, maildir, home, name, uid, gid) VALUES (";
		if ($zone_data->id_table != 0)
			$query .= "".$zone_data->id_table.", ";
		else
			$query .= "1, ";
		$query .= "'".$clear_email_domain."', ";
		$query .= "'".$login."', ";
		$query .= "'".$passwd."', ";
		$query .= "'".$maildir."', ";
		$query .= "'".$home."', ";
		$query .= "'".$name."', ";
		$query .= "".$zone_data->uid.", ";
		$query .= "".$zone_data->gid."";
		$query .= ")";

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$result = $this->SQL->exec_query("SELECT id_table FROM mail_table WHERE login='".$login."'");
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);

		$query = "INSERT INTO mail_pref_table (
			id_mail_table, reply_email, my_sign,
			timezone, rpp, refresh_time,
			change_html_tags, save_html_links,
			clean_trash, add_sign, save_to_sent,
			save_to_trash, save_only_seen) VALUES (";
		$query .= $data->id_table.", '".$login."', '',
			'+0200', 10, 10, 1, 0, 0, 0, 0, 1, 1)";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$query = "INSERT INTO mail_forward_table (".($zone_data->id_table!=0?"id_zone_table, ":"dummy, ")."email, forward_address, forward_do) VALUES (";
		if ($zone_data->id_table != 0)
			$query .= "".$zone_data->id_table.", ";
		else
			$query .= "1, ";
		$query .= "'".$login."', ";
		$query .= "'".$forward."', ";
		$query .= "'".($reply_do=="on"?"1":"0")."' ";
		$query .= ")";

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$query = "INSERT INTO mail_autoreply_table (".($zone_data->id_table!=0?"id_zone_table, ":"dummy, ")."email, reply, reply_do) VALUES (";
		if ($zone_data->id_table != 0)
			$query .= "".$zone_data->id_table.", ";
		else
			$query .= "1, ";
		$query .= "'".$login."', ";
		$query .= "'".$reply_text."', ";
		$query .= "'".($reply_do=="on"?"1":"0")."' ";
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

	function change_mail_account($id, $login, $passwd, $maildir, $home, $name, $uid, $gid, $forward_do, $forward, $reply_do, $reply_text) {

		global $Clients_dir, $Alone_dir, $MAIL_UID, $HOSTING_GID;

		$login = strtolower($login);
		$forward = strtolower($forward);

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $login, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in login. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($login, "@") == 0 || substr($login, 0, strpos($login, "@")) == "" || substr($login, strpos($login, "@")+1, strlen($login)) == "") {
			$_SESSION["adminpanel_error"] = "Such e-mail address is not valid !";
			return FALSE;
		}

		// Проверяем, есть ли такой e-mail ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->fetch_mail_by_login($login);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id) {
			$_SESSION["adminpanel_error"] = "Such e-mail already exist !";
			return FALSE;
		}

		$email_data = $this->fetch_mail_account($id);
		if ($email_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch mail account: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Let's begin...
		// OLD
		$old_data["email_id_table"] = $id;
		$old_data["email_login"] = $email_data->login;
		$old_data["email_clear_login"] = substr($email_data->login, 0, strpos($email_data->login, "@"));
		$old_data["email_clear_domain"] = substr($email_data->login, strpos($email_data->login, "@")+1, strlen($email_data->login));
		$old_data["email_home"] = $email_data->home;
		$old_data["email_maildir"] = $email_data->maildir;

		if ($email_data->id_zone_table != "") {
			// Если зона есть, значит это email зоны

			$find_old_zone = FALSE;

			$old_zone_data = $this->PARENT_ZC->fetch_zone_by_name($old_data["email_clear_domain"]);
			if ($old_zone_data != FALSE) {
				$find_old_zone = TRUE;
			}
			else {
				// ищем в поддоменах клиентов
				$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($old_data["email_clear_domain"]);
				if ($subdomain_data != FALSE) {
					$old_zone_data = $this->PARENT_ZC->fetch_zone($subdomain_data->id_zone_table);
					if ($old_zone_data != FALSE) {
						$find_old_zone = TRUE;
					}
					else {
						$_SESSION["adminpanel_error"] = "Can't fetch old zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
						return FALSE;
					}
				}
			}

			if ($find_old_zone == TRUE) {
				$old_client_data = $this->PARENT_ZC->PARENT_CC->fetch_client_account($old_zone_data->id_client_table);

				if ($old_client_data == FALSE) {
					$_SESSION["adminpanel_error"] = "Can't fetch old client (client_id was find in zone): ".$_SESSION["adminpanel_error"];
					return FALSE;
				}

				$old_data["zone_id_table"] = $old_zone_data->id_table;
				$old_data["zone_name"] = $old_zone_data->name;
				$old_data["client_id_table"] = $old_client_data->id_table;
				$old_data["client_name"] = $old_client_data->login;
				$old_data["email_dummy"] = false;
			}
			else {
				$_SESSION["adminpanel_error"] = "Can't find old zone: email_data has 'id_zone_table', but I can't find one";
				return FALSE;
			}
		}
		else {
			// Если зоны нет, значит это одинокий email
			$old_data["zone_id_table"] = 0;
			$old_data["zone_name"] = $old_data["email_clear_domain"];
			$old_data["client_id_table"] = 0;
			$old_data["client_name"] = $Alone_dir;
			$old_data["email_dummy"] = true;
		}

		// NEW
		$new_data["email_login"] = $login;
		$new_data["email_clear_login"] = substr($login, 0, strpos($login, "@"));
		$new_data["email_clear_domain"] = substr($login, strpos($login, "@")+1, strlen($login));

		// Now, repeat same but for new login (of course, if login is new)...
		if ($old_data["email_login"] != $new_data["email_login"]) {

			$find_new_zone = FALSE;

			$new_zone_data = $this->PARENT_ZC->fetch_zone_by_name($new_data["email_clear_domain"]);
			if ($new_zone_data != FALSE) {
				$find_new_zone = TRUE;
			}
			else {
				// ищем в поддоменах клиентов
				$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($new_data["email_clear_domain"]);
				if ($subdomain_data != FALSE) {
					$new_zone_data = $this->PARENT_ZC->fetch_zone($subdomain_data->id_zone_table);
					if ($new_zone_data != FALSE) {
						$find_new_zone = TRUE;
					}
					else {
						$_SESSION["adminpanel_error"] = "Can't fetch new zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
						return FALSE;
					}
				}
			}

			if ($find_new_zone == TRUE) {
				$new_client_data = $this->PARENT_ZC->PARENT_CC->fetch_client_account($new_zone_data->id_client_table);

				if ($new_client_data == FALSE) {
					$_SESSION["adminpanel_error"] = "Can't fetch new client (client_id was find in zone): ".$_SESSION["adminpanel_error"];
					return FALSE;
				}

				$new_data["zone_id_table"] = $new_zone_data->id_table;
				$new_data["zone_name"] = $new_zone_data->name;
				$new_data["client_id_table"] = $new_client_data->id_table;
				$new_data["client_name"] = $new_client_data->login;
				$new_data["email_dummy"] = false;
			}
			else {
				// Если зоны нет, значит это одинокий email
				$new_data["zone_id_table"] = 0;
				$new_data["zone_name"] = $new_data["email_clear_domain"];
				$new_data["client_id_table"] = 0;
				$new_data["client_name"] = $Alone_dir;
				$new_data["email_dummy"] = true;
			}
		}
		else {
			// Если логин старый, значит все копируем из старого...
			$new_data["zone_id_table"] = $old_data["zone_id_table"];
			$new_data["zone_name"] = $old_data["zone_name"];
			$new_data["client_id_table"] = $old_data["client_id_table"];
			$new_data["client_name"] = $old_data["client_name"];
			$new_data["email_dummy"] = $old_data["email_dummy"];
		}

		if($old_data["email_home"] == $home || $old_data["email_maildir"] == $maildir) {
			// Admin did not specified new home dir or maildir... build it again for corrent
			$new_data["email_home"] = $Clients_dir."/".$new_data["client_name"]."/".$new_data["zone_name"]."/mail/".$new_data["email_login"];
			$new_data["email_maildir"] = $Clients_dir."/".$new_data["client_name"]."/".$new_data["zone_name"]."/mail/".$new_data["email_login"];
		}
		else {
			$new_data["email_home"] = $home;
			$new_data["email_maildir"] = $maildir;
		}

		$result = $this->PARENT_ZC->check_ID_existence($uid, $gid);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = "Failed on check ID existence: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Now, if login, home or maildir is change, then update remote
		if ($old_data["email_login"] != $new_data["email_login"] ||
			$old_data["home"] != $new_data["home"] ||
			$old_data["maildir"] != $new_data["maildir"]) {

			$result = $this->HC->connect();

			if ($result["error"] == TRUE) {
				$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
				return FALSE;
			}

			$result = $this->HC->client_create_dir_struct($new_data["client_name"]);
			if ($result["error"] == TRUE) {
				$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
				$this->HC->disconnect();
				return FALSE;
			}

			$result = $this->HC->zone_create_dir_struct($new_data["client_name"], $new_data["zone_name"]);
			if ($result["error"] == TRUE) {
				$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
				$this->HC->disconnect();
				return FALSE;
			}

			$result = $this->HC->update_mail($old_data["client_name"], $old_data["zone_name"], $old_data["email_login"],
							$new_data["client_name"], $new_data["zone_name"], $new_data["email_login"]);
			if ($result["error"] == TRUE) {
				$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
				$this->HC->disconnect();
				return FALSE;
			}

			$this->HC->disconnect();
		}
/*
		echo "CHANGE EMAIL: ".($old_data["email_login"] != $new_data["email_login"]?"YES":"NO")."<br>";
		echo "<br>";
		echo "<pre>"; print_r($old_data); echo "</pre>";
		echo "<br>";
		echo "<pre>"; print_r($new_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		$query = "UPDATE mail_table SET ";
		if ($new_data["zone_id_table"] != 0)
			$query .= "id_zone_table=".$new_data["zone_id_table"].", dummy=0, ";
		else
			$query .= "id_zone_table=NULL, dummy=1, ";
		$query .= "domain='".$new_data["zone_name"]."', ";
		$query .= "login='".$new_data["email_login"]."', ";
		if ($passwd != "")
			$query .= "passwd='".$passwd."', ";
		$query .= "maildir='".$new_data["email_maildir"]."', ";
		$query .= "home='".$new_data["email_home"]."', ";
		$query .= "name='".$name."', ";
		$query .= "uid=".$uid.", ";
		$query .= "gid=".$gid."";
		$query .= " WHERE id_table=".$old_data["email_id_table"]."";

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$query = "UPDATE mail_forward_table SET ";
		if ($new_data["zone_id_table"] != 0)
			$query .= "id_zone_table=".$new_data["zone_id_table"].", dummy=0, ";
		else
			$query .= "id_zone_table=NULL, dummy=1, ";
		$query .= "email='".$new_data["email_login"]."', ";
		$query .= "forward_address='".$forward."', ";
		$query .= "forward_do='".($forward_do=="on"?"1":"0")."' ";
		$query .= " WHERE email='".$old_data["email_login"]."'";

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$query = "UPDATE mail_autoreply_table SET ";
		if ($new_data["zone_id_table"] != 0)
			$query .= "id_zone_table=".$new_data["zone_id_table"].", dummy=0, ";
		else
			$query .= "id_zone_table=NULL, dummy=1, ";
		$query .= "email='".$new_data["email_login"]."', ";
		$query .= "reply='".$reply_text."', ";
		$query .= "reply_do='".($reply_do=="on"?"1":"0")."' ";
		$query .= " WHERE email='".$old_data["email_login"]."'";

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function xxx_change_domain($id, $domain, $dir, $login) {

		$this->SQL->connect();

		$login = strtolower($login);

		$query = "UPDATE mail_table SET ";
		$query .= "domain='".$domain."', ";
		$query .= "login='".$login."', ";
		$query .= "maildir='".$dir."', ";
		$query .= "home='".$dir."' ";
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

	function xxx_remove_mail_account($id) {

		// Надо чистить логины, ибо в БД они храняться
		// в виде xxx@yyy.zzz, а нам надо только xxx
		$data = $this->fetch_mail_account($id);

		if ($data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch mail account: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$login = substr($data->login, 0, strpos($data->login, "@"));
		$email_domain = substr($data->login, strpos($data->login, "@")+1, strlen($data->login));
		$domain = $email_domain;

		// Попытка найти доменное имя:
		// Ищем в зонах, и в поддоменах клиентов
		$find_email_domain = TRUE;
		$client_data->id_table = 0;

		// ищем в доменных зонах
		$domain_data = $this->PARENT_CC->PARENT_ZC->fetch_zone_by_name($email_domain);
		if ($domain_data == FALSE) {
			// ищем в поддоменах клиентов
			$domain_data = $this->PARENT_CC->SDC->fetch_subdomain_by_fname($email_domain);
			if ($domain_data == FALSE) {
				$find_email_domain = FALSE;
			}
			else {
				$client_data = $this->PARENT_CC->fetch_client_account($domain_data->id_client_table);
				if ($client_data == FALSE) {
					// Бред, нет клиента и есть его номер
					$_SESSION["adminpanel_error"] = "Fatal error: no such client by this id !";
					return FALSE;
				}

				$domain = $client_data->domain;
			}
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->remove_mail($domain, $login.".".$email_domain);
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM mail_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function fetch_mail_account($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mt.*
			FROM mail_table mt
			WHERE mt.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function xxx_is_email_of_client($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.name AS domain
			FROM mail_table mt, client_table ct, zone_table zt
			WHERE mt.id_table=".$id."
			AND ct.id_table=mt.id_client_table
			AND zt.id_table=ct.id_zone_table");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_mail_by_login($login) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT mt.*
			FROM mail_table mt
			WHERE mt.login='".$login."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_mails() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mt.*
			FROM mail_table mt
			ORDER BY mt.login");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_alone_mails() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mt.*
			FROM mail_table mt
			WHERE mt.dummy=1
			ORDER BY mt.login");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function xxx_fetch_all_mails_of_client($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mt.*
			FROM mail_table mt, zone_table zt, client_table ct
			WHERE ct.id_table = ".$id_client."
			AND zt.id_client_table = ct.id_table
			AND mt.id_zone_table = zt.id_table
			ORDER BY mt.login");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_mails_of_zone($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mt.*
			FROM mail_table mt
			WHERE mt.id_zone_table = ".$id_zone."
			ORDER BY mt.login");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_zones_mail($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mt.*
			FROM mail_table mt
			WHERE mt.id_zone_table=".$id_zone);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_forward_of_mail($email) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT mft.*
			FROM mail_forward_table mft
			WHERE mft.email='".$email."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_reply_of_mail($email) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT mat.*
			FROM mail_autoreply_table mat
			WHERE mat.email='".$email."'");

		if ($result == FALSE) {
			$this->SQL->disconnect();
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function get_emails_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(mt.id_table) AS mail_count
			FROM mail_table mt");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function get_alone_emails_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(mt.id_table) AS mail_count
			FROM mail_table mt
			WHERE mt.dummy=1");

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
//		A L I A S
//####################################################

	function add_mail_alias($email, $alias) {

		$email = strtolower($email);
		$alias = strtolower($alias);

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $email, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in email. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($email, "@") == 0 || substr($email, 0, strpos($email, "@")) == "" || substr($email, strpos($email, "@")+1, strlen($email)) == "") {
			$_SESSION["adminpanel_error"] = "Such e-mail address is not valid !";
			return FALSE;
		}

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $alias, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in alias. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($alias, "@") == 0 || substr($alias, 0, strpos($alias, "@")) == "" || substr($alias, strpos($alias, "@")+1, strlen($alias)) == "") {
			$_SESSION["adminpanel_error"] = "Such alias address is not valid !";
			return FALSE;
		}

		// Проверяем, есть ли такой e-mail ?
		// Если да - тревога !
		$tmp_data = $this->fetch_mail_alias_by_email($email);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such e-mail alias already exist !";
			return FALSE;
		}

		// Let's begin...
		$alias_data["email_alias_email"] = $email;
		$alias_data["email_alias_alias"] = $alias;
		$alias_data["email_alias_clear_domain"] = substr($email, strpos($email, "@")+1, strlen($email));
		$alias_data["zone_id_table"] = 0;
		$alias_data["email_dummy"] = true;

		// А теперь попытаемся найти зону, на котороу ссылается email
		$find_zone = FALSE;

		$zone_data = $this->PARENT_ZC->fetch_zone_by_name($alias_data["email_alias_clear_domain"]);
		if ($zone_data != FALSE) {
			$find_zone = TRUE;
		}
		else {
			// ищем в поддоменах клиентов
			$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($alias_data["email_alias_clear_domain"]);
			if ($subdomain_data != FALSE) {
				$zone_data = $this->PARENT_ZC->fetch_zone($subdomain_data->id_zone_table);
				if ($zone_data != FALSE) {
					$find_zone = TRUE;
				}
				else {
					$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
					return FALSE;
				}
			}
		}

		if ($find_zone == TRUE) {
			$alias_data["zone_id_table"] = $zone_data->id_table;
			$alias_data["email_dummy"] = false;
		}
/*
		echo "<pre>"; print_r($alias_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		$query = "INSERT INTO mail_alias_table (".($alias_data["zone_id_table"]!=0?"id_zone_table, ":"dummy, ")." email, alias) VALUES (";
		if ($alias_data["zone_id_table"] != 0)
			$query .= "".$alias_data["zone_id_table"].", ";
		else
			$query .= "1, ";
		$query .= "'".$alias_data["email_alias_email"]."', ";
		$query .= "'".$alias_data["email_alias_alias"]."'";
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

	function change_mail_alias($id, $email, $alias) {

		$email = strtolower($email);
		$alias = strtolower($alias);

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $email, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in email. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($email, "@") == 0 || substr($email, 0, strpos($email, "@")) == "" || substr($email, strpos($email, "@")+1, strlen($email)) == "") {
			$_SESSION["adminpanel_error"] = "Such e-mail address is not valid !";
			return FALSE;
		}

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $alias, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in alias. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($alias, "@") == 0 || substr($alias, 0, strpos($alias, "@")) == "" || substr($alias, strpos($alias, "@")+1, strlen($alias)) == "") {
			$_SESSION["adminpanel_error"] = "Such alias address is not valid !";
			return FALSE;
		}

		// Проверяем, есть ли такой e-mail ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->fetch_mail_alias_by_email($email);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id) {
			$_SESSION["adminpanel_error"] = "Such e-mail alias already exist !";
			return FALSE;
		}

		$old_alias_data = $this->fetch_mail_alias($id);
		if ($old_alias_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch mail alias: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Let's begin...
		$new_alias_data["email_alias_email"] = $email;
		$new_alias_data["email_alias_alias"] = $alias;
		$new_alias_data["email_alias_clear_domain"] = substr($email, strpos($email, "@")+1, strlen($email));
		$new_alias_data["zone_id_table"] = 0;
		$new_alias_data["email_dummy"] = true;

		// А теперь попытаемся найти зону, на котороу ссылается email
		$find_zone = FALSE;

		$zone_data = $this->PARENT_ZC->fetch_zone_by_name($new_alias_data["email_alias_clear_domain"]);
		if ($zone_data != FALSE) {
			$find_zone = TRUE;
		}
		else {
			// ищем в поддоменах клиентов
			$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($new_alias_data["email_alias_clear_domain"]);
			if ($subdomain_data != FALSE) {
				$zone_data = $this->PARENT_ZC->fetch_zone($subdomain_data->id_zone_table);
				if ($zone_data != FALSE) {
					$find_zone = TRUE;
				}
				else {
					$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
					return FALSE;
				}
			}
		}

		if ($find_zone == TRUE) {
			$new_alias_data["zone_id_table"] = $zone_data->id_table;
			$new_alias_data["email_dummy"] = false;
		}
/*
		echo "<pre>"; print_r($new_alias_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		$query = "UPDATE mail_alias_table SET ";
		if ($new_alias_data["zone_id_table"] != 0)
			$query .= "id_zone_table=".$new_alias_data["zone_id_table"].", dummy=0, ";
		else
			$query .= "id_zone_table=NULL, dummy=1, ";
		$query .= "email='".$new_alias_data["email_alias_email"]."', ";
		$query .= "alias='".$new_alias_data["email_alias_alias"]."' ";
		$query .= " WHERE id_table=".$id;

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function remove_mail_alias($id) {

		$data = $this->fetch_mail_alias($id);
		if ($data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch mail alias: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM mail_alias_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function fetch_mail_alias($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			malt.*
			FROM mail_alias_table malt
			WHERE malt.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_mail_alias_by_email($email) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT malt.*
			FROM mail_alias_table malt
			WHERE malt.email='".$email."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_mail_aliases() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			malt.*
			FROM mail_alias_table malt
			ORDER BY malt.email");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_alone_mail_aliases() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			malt.*
			FROM mail_alias_table malt
			WHERE malt.dummy=1
			ORDER BY malt.email");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_mail_aliases_of_zone($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			malt.*
			FROM mail_alias_table malt
			WHERE malt.id_zone_table = ".$id_zone."
			ORDER BY malt.email");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function get_email_aliases_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(malt.id_table) AS alias_count
			FROM mail_alias_table malt");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function get_alone_email_aliases_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(malt.id_table) AS alias_count
			FROM mail_alias_table malt
			WHERE malt.dummy=1");

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
//		F O R W A R D E R
//####################################################

	function add_mail_forwarder($email, $forward_do, $forward) {

		$email = strtolower($email);
		$forward = strtolower($forward);

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $email, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in email. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($email, "@") == 0 || substr($email, 0, strpos($email, "@")) == "" || substr($email, strpos($email, "@")+1, strlen($email)) == "") {
			$_SESSION["adminpanel_error"] = "Such e-mail address is not valid !";
			return FALSE;
		}

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $forward, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in forward. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($forward, "@") == 0 || substr($forward, 0, strpos($forward, "@")) == "" || substr($forward, strpos($forward, "@")+1, strlen($forward)) == "") {
			$_SESSION["adminpanel_error"] = "Such forward address is not valid !";
			return FALSE;
		}

		// Проверяем, есть ли такой forwarder ?
		// Если да - тревога !
		$tmp_data = $this->fetch_mail_forwarder_by_email($email);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such e-mail forwarder already exist !";
			return FALSE;
		}

		// Let's begin...
		$forwarder_data["email_forwarder_email"] = $email;
		$forwarder_data["email_forwarder_forward_do"] = $forward_do;
		$forwarder_data["email_forwarder_forward"] = $forward;
		$forwarder_data["email_forwarder_clear_domain"] = substr($email, strpos($email, "@")+1, strlen($email));
		$forwarder_data["zone_id_table"] = 0;
		$forwarder_data["email_dummy"] = true;

		// А теперь попытаемся найти зону, на котороу ссылается email
		$find_zone = FALSE;

		$zone_data = $this->PARENT_ZC->fetch_zone_by_name($forwarder_data["email_forwarder_clear_domain"]);
		if ($zone_data != FALSE) {
			$find_zone = TRUE;
		}
		else {
			// ищем в поддоменах клиентов
			$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($forwarder_data["email_forwarder_clear_domain"]);
			if ($subdomain_data != FALSE) {
				$zone_data = $this->PARENT_ZC->fetch_zone($subdomain_data->id_zone_table);
				if ($zone_data != FALSE) {
					$find_zone = TRUE;
				}
				else {
					$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
					return FALSE;
				}
			}
		}

		if ($find_zone == TRUE) {
			$forwarder_data["zone_id_table"] = $zone_data->id_table;
			$forwarder_data["email_dummy"] = false;
		}
/*
		echo "<pre>"; print_r($forwarder_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		$query = "INSERT INTO mail_forward_table (".($forwarder_data["zone_id_table"]!=0?"id_zone_table, ":"dummy, ")." email, forward_do, forward_address) VALUES (";
		if ($forwarder_data["zone_id_table"] != 0)
			$query .= "".$forwarder_data["zone_id_table"].", ";
		else
			$query .= "1, ";
		$query .= "'".$forwarder_data["email_forwarder_email"]."', ";
		$query .= "'".($forwarder_data["email_forwarder_forward_do"]=="on"?"1":"0")."', ";
		$query .= "'".$forwarder_data["email_forwarder_forward"]."'";
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

	function change_mail_forwarder($id, $email, $forward_do, $forward) {

		$email = strtolower($email);
		$forward = strtolower($forward);

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $email, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in email. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($email, "@") == 0 || substr($email, 0, strpos($email, "@")) == "" || substr($email, strpos($email, "@")+1, strlen($email)) == "") {
			$_SESSION["adminpanel_error"] = "Such e-mail address is not valid !";
			return FALSE;
		}

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $forward, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in forward. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($forward, "@") == 0 || substr($forward, 0, strpos($forward, "@")) == "" || substr($forward, strpos($forward, "@")+1, strlen($forward)) == "") {
			$_SESSION["adminpanel_error"] = "Such forward address is not valid !";
			return FALSE;
		}

		// Проверяем, есть ли такой e-mail ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->fetch_mail_forwarder_by_email($email);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id) {
			$_SESSION["adminpanel_error"] = "Such e-mail forwarder already exist !";
			return FALSE;
		}

		$old_forwarder_data = $this->fetch_mail_forwarder($id);
		if ($old_forwarder_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch mail forwarder: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Let's begin...
		$new_forwarder_data["email_forwarder_email"] = $email;
		$new_forwarder_data["email_forwarder_forward_do"] = $forward_do;
		$new_forwarder_data["email_forwarder_forward"] = $forward;
		$new_forwarder_data["email_forwarder_clear_domain"] = substr($email, strpos($email, "@")+1, strlen($email));
		$new_forwarder_data["zone_id_table"] = 0;
		$new_forwarder_data["email_dummy"] = true;

		// А теперь попытаемся найти зону, на котороу ссылается email
		$find_zone = FALSE;

		$zone_data = $this->PARENT_ZC->fetch_zone_by_name($new_forwarder_data["email_forwarder_clear_domain"]);
		if ($zone_data != FALSE) {
			$find_zone = TRUE;
		}
		else {
			// ищем в поддоменах клиентов
			$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($new_forwarder_data["email_forwarder_clear_domain"]);
			if ($subdomain_data != FALSE) {
				$zone_data = $this->PARENT_ZC->fetch_zone($subdomain_data->id_zone_table);
				if ($zone_data != FALSE) {
					$find_zone = TRUE;
				}
				else {
					$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
					return FALSE;
				}
			}
		}

		if ($find_zone == TRUE) {
			$new_forwarder_data["zone_id_table"] = $zone_data->id_table;
			$new_forwarder_data["email_dummy"] = false;
		}
/*
		echo "<pre>"; print_r($new_forwarder_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		$query = "UPDATE mail_forward_table SET ";
		if ($new_forwarder_data["zone_id_table"] != 0)
			$query .= "id_zone_table=".$new_forwarder_data["zone_id_table"].", dummy=0, ";
		else
			$query .= "id_zone_table=NULL, dummy=1, ";
		$query .= "email='".$new_forwarder_data["email_forwarder_email"]."', ";
		$query .= "forward_do='".($new_forwarder_data["email_forwarder_forward_do"]=="on"?"1":"0")."', ";
		$query .= "forward_address='".$new_forwarder_data["email_forwarder_forward"]."' ";
		$query .= " WHERE id_table=".$id;

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function remove_mail_forwarder($id) {

		$data = $this->fetch_mail_forwarder($id);
		if ($data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch mail forwarder: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM mail_forward_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function fetch_mail_forwarder($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mft.*
			FROM mail_forward_table mft
			WHERE mft.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_mail_forwarder_by_email($email) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT mft.*
			FROM mail_forward_table mft
			WHERE mft.email='".$email."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_mail_forwarders() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mft.*
			FROM mail_forward_table mft
			ORDER BY mft.email");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_alone_mail_forwarders() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mft.*
			FROM mail_forward_table mft
			WHERE mft.dummy=1
			ORDER BY mft.email");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_mail_forwarders_of_zone($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mft.*
			FROM mail_forward_table mft
			WHERE mft.id_zone_table = ".$id_zone."
			ORDER BY mft.email");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function get_email_forwarders_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(mft.id_table) AS forwarders_count
			FROM mail_forward_table mft");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function get_alone_email_forwarders_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(mft.id_table) AS forwarders_count
			FROM mail_forward_table mft
			WHERE mft.dummy=1");

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
//		A U T O R E P L Y
//####################################################

	function add_mail_autoreply($email, $reply_do, $reply_text) {

		$email = strtolower($email);

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $email, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in email. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($email, "@") == 0 || substr($email, 0, strpos($email, "@")) == "" || substr($email, strpos($email, "@")+1, strlen($email)) == "") {
			$_SESSION["adminpanel_error"] = "Such e-mail address is not valid !";
			return FALSE;
		}

		// Проверяем, есть ли такой autoreply ?
		// Если да - тревога !
		$tmp_data = $this->fetch_mail_autoreply_by_email($email);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such e-mail autoreply already exist !";
			return FALSE;
		}

		// Let's begin...
		$autoreply_data["email_autoreply_email"] = $email;
		$autoreply_data["email_autoreply_reply_do"] = $reply_do;
		$autoreply_data["email_autoreply_reply_text"] = $reply_text;
		$autoreply_data["email_autoreply_clear_domain"] = substr($email, strpos($email, "@")+1, strlen($email));
		$autoreply_data["zone_id_table"] = 0;
		$autoreply_data["email_dummy"] = true;

		// А теперь попытаемся найти зону, на котороу ссылается email
		$find_zone = FALSE;

		$zone_data = $this->PARENT_ZC->fetch_zone_by_name($autoreply_data["email_autoreply_clear_domain"]);
		if ($zone_data != FALSE) {
			$find_zone = TRUE;
		}
		else {
			// ищем в поддоменах клиентов
			$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($autoreply_data["email_autoreply_clear_domain"]);
			if ($subdomain_data != FALSE) {
				$zone_data = $this->PARENT_ZC->fetch_zone($subdomain_data->id_zone_table);
				if ($zone_data != FALSE) {
					$find_zone = TRUE;
				}
				else {
					$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
					return FALSE;
				}
			}
		}

		if ($find_zone == TRUE) {
			$autoreply_data["zone_id_table"] = $zone_data->id_table;
			$autoreply_data["email_dummy"] = false;
		}
/*
		echo "<pre>"; print_r($autoreply_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		$query = "INSERT INTO mail_autoreply_table (".($autoreply_data["zone_id_table"]!=0?"id_zone_table, ":"dummy, ")." email, reply_do, reply) VALUES (";
		if ($autoreply_data["zone_id_table"] != 0)
			$query .= "".$autoreply_data["zone_id_table"].", ";
		else
			$query .= "1, ";
		$query .= "'".$autoreply_data["email_autoreply_email"]."', ";
		$query .= "'".($autoreply_data["email_autoreply_reply_do"]=="on"?"1":"0")."', ";
		$query .= "'".$autoreply_data["email_autoreply_reply_text"]."'";
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

	function change_mail_autoreply($id, $email, $reply_do, $reply_text) {

		$email = strtolower($email);

		if (eregi("[^a-zA-Z0-9\\.@_\\-]", $email, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in email. Allowed only A-Z, a-z, 0-9, @, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (strpos($email, "@") == 0 || substr($email, 0, strpos($email, "@")) == "" || substr($email, strpos($email, "@")+1, strlen($email)) == "") {
			$_SESSION["adminpanel_error"] = "Such e-mail address is not valid !";
			return FALSE;
		}

		// Проверяем, есть ли такой e-mail ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->fetch_mail_autoreply_by_email($email);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id) {
			$_SESSION["adminpanel_error"] = "Such e-mail auto answer already exist !";
			return FALSE;
		}

		$old_autoreply_data = $this->fetch_mail_autoreply($id);
		if ($old_autoreply_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch mail auto answer: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Let's begin...
		$new_autoreply_data["email_autoreply_email"] = $email;
		$new_autoreply_data["email_autoreply_reply_do"] = $reply_do;
		$new_autoreply_data["email_autoreply_reply_text"] = $reply_text;
		$new_autoreply_data["email_autoreply_clear_domain"] = substr($email, strpos($email, "@")+1, strlen($email));
		$new_autoreply_data["zone_id_table"] = 0;
		$new_autoreply_data["email_dummy"] = true;

		// А теперь попытаемся найти зону, на котороу ссылается email
		$find_zone = FALSE;

		$zone_data = $this->PARENT_ZC->fetch_zone_by_name($new_autoreply_data["email_autoreply_clear_domain"]);
		if ($zone_data != FALSE) {
			$find_zone = TRUE;
		}
		else {
			// ищем в поддоменах клиентов
			$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($new_autoreply_data["email_autoreply_clear_domain"]);
			if ($subdomain_data != FALSE) {
				$zone_data = $this->PARENT_ZC->fetch_zone($subdomain_data->id_zone_table);
				if ($zone_data != FALSE) {
					$find_zone = TRUE;
				}
				else {
					$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
					return FALSE;
				}
			}
		}

		if ($find_zone == TRUE) {
			$new_autoreply_data["zone_id_table"] = $zone_data->id_table;
			$new_autoreply_data["email_dummy"] = false;
		}
/*
		echo "<pre>"; print_r($new_autoreply_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		$query = "UPDATE mail_autoreply_table SET ";
		if ($new_autoreply_data["zone_id_table"] != 0)
			$query .= "id_zone_table=".$new_autoreply_data["zone_id_table"].", dummy=0, ";
		else
			$query .= "id_zone_table=NULL, dummy=1, ";
		$query .= "email='".$new_autoreply_data["email_autoreply_email"]."', ";
		$query .= "reply_do='".($new_autoreply_data["email_autoreply_reply_do"]=="on"?"1":"0")."', ";
		$query .= "reply='".$new_autoreply_data["email_autoreply_reply_text"]."' ";
		$query .= " WHERE id_table=".$id;

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function remove_mail_autoreply($id) {

		$data = $this->fetch_mail_autoreply($id);
		if ($data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch mail auto answer: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM mail_autoreply_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function fetch_mail_autoreply($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mat.*
			FROM mail_autoreply_table mat
			WHERE mat.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_mail_autoreply_by_email($email) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT mat.*
			FROM mail_autoreply_table mat
			WHERE mat.email='".$email."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_mail_autoreplies() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mat.*
			FROM mail_autoreply_table mat
			ORDER BY mat.email");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_alone_mail_autoreplies() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mat.*
			FROM mail_autoreply_table mat
			WHERE mat.dummy=1
			ORDER BY mat.email");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_mail_autoreplies_of_zone($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mat.*
			FROM mail_autoreply_table mat
			WHERE mat.id_zone_table = ".$id_zone."
			ORDER BY mat.email");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function get_email_autoreplies_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(mat.id_table) AS autoreplies_count
			FROM mail_autoreply_table mat");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function get_alone_email_autoreplies_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(mat.id_table) AS autoreplies_count
			FROM mail_autoreply_table mat
			WHERE mat.dummy=1");

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
//		M A I L L I S T
//####################################################

	function add_maillist($zone_id, $title, $member_list) {

		$member_list = strtolower($member_list);

		$list_data["emaillist_member_list"] = $member_list;
		$list_data["emaillist_title"] = $title;

		if ($zone_id != "none") {
			$tmp_data = $this->PARENT_ZC->fetch_zone($zone_id);
			if ($tmp_data == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't find such zone !";
				return FALSE;
			}

			$list_data["zone_id_table"] = $zone_id;
			$list_data["emaillist_dummy"] = false;
		}
		else {
			$list_data["zone_id_table"] = 0;
			$list_data["emaillist_dummy"] = true;
		}

		$tmp_data = $this->fetch_maillist_by_title($title);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such mailing list already exist !";
			return FALSE;
		}

		// Let's begin...

		$this->SQL->connect();

		$query = "INSERT INTO mailing_list_table (".($list_data["zone_id_table"]!=0?"id_zone_table, ":"dummy, ")." title, list_member) VALUES (";
		if ($list_data["zone_id_table"] != 0)
			$query .= "".$list_data["zone_id_table"].", ";
		else
			$query .= "1, ";
		$query .= "'".$list_data["emaillist_title"]."', ";
		$query .= "'".$list_data["emaillist_member_list"]."' ";
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

	function change_maillist($id, $zone_id, $title, $member_list) {

		$member_list = strtolower($member_list);

		$list_data["emaillist_member_list"] = $member_list;
		$list_data["emaillist_title"] = $title;

		if ($id_zone != "none") {
			$tmp_data = $this->PARENT_ZC->fetch_zone($zone_id);
			if ($tmp_data == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't find such zone !";
				return FALSE;
			}

			$list_data["zone_id_table"] = $zone_id;
			$list_data["emaillist_dummy"] = false;
		}
		else {
			$list_data["zone_id_table"] = 0;
			$list_data["emaillist_dummy"] = true;
		}

		$tmp_data = $this->fetch_maillist($id);
		if ($tmp_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch mailing list: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Let's begin...

		$this->SQL->connect();

		$query = "UPDATE mailing_list_table SET ";
		if ($list_data["zone_id_table"] != 0)
			$query .= "id_zone_table=".$list_data["zone_id_table"].", dummy=0, ";
		else
			$query .= "id_zone_table=NULL, dummy=1, ";
		$query .= "title='".$list_data["emaillist_title"]."', ";
		$query .= "list_member='".$list_data["emaillist_member_list"]."' ";
		$query .= " WHERE id_table=".$id;

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function remove_maillist($id) {

		$data = $this->fetch_maillist($id);
		if ($data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch mailing list: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM mailing_list_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function fetch_maillist($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mlt.*
			FROM mailing_list_table mlt
			WHERE mlt.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_maillist_by_title($title) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT mlt.*
			FROM mailing_list_table mlt
			WHERE mlt.title='".$title."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_maillists() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mlt.*
			FROM mailing_list_table mlt
			ORDER BY mlt.title");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_alone_maillists() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mlt.*
			FROM mailing_list_table mlt
			WHERE mlt.dummy=1
			ORDER BY mlt.title");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_maillists_of_zone($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mlt.*
			FROM mailing_list_table mlt
			WHERE mlt.id_zone_table = ".$id_zone."
			ORDER BY mlt.title");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function get_maillists_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(mlt.id_table) AS maillist_count
			FROM mailing_list_table mlt");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function get_alone_maillists_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(mlt.id_table) AS maillist_count
			FROM mailing_list_table mlt
			WHERE mlt.dummy=1");

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
//		C L I E N T
//####################################################

	function xxx_client_add_mail_account($id_client, $domain, $login, $email_domain, $passwd, $name, $quota, $forward) {

		global $Clients_dir, $COURIER_UID, $HOSTING_GID;

		$login = strtolower($login);

		// Проверяем, есть ли такой e-mail ?
		// Если да - тревога !

		$email_data = $this->fetch_mail_by_login($login."@".$email_domain);
		if ($email_data != FALSE) {
			$_SESSION["clientpanel_error"] = "Такой e-mail уже существует !";
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->create_mail($domain, $login.".".$email_domain, $quota);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$maildir = $Clients_dir.$domain."/mail/".$login.".".$email_domain;
		$home = $maildir;
		$uid = $COURIER_UID;
		$gid = $HOSTING_GID;

		$this->SQL->connect();

		$query = "INSERT INTO mail_table (
			id_client_table, domain, login, passwd, maildir,
			home, name, uid, gid, forward ".(($quota!="")?", quota":"").")
			VALUES (";
		$query .= "".$id_client.", ";
		$query .= "'".$email_domain."', ";
		$query .= "'".$login."@".$email_domain."', ";
		$query .= "'".$passwd."', ";
		$query .= "'".$maildir."', ";
		$query .= "'".$home."', ";
		$query .= "'".$name."', ";
		$query .= "".$uid.", ";
		$query .= "".$gid.", ";
		$query .= "'".$forward."'";
		if ($quota != "")
			$query .= ", '".$quota."'";

		$query .= ")";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$result = $this->SQL->exec_query("SELECT
			id_table
			FROM mail_table
			WHERE login='".$login."@".$email_domain."'
			AND id_client_table='".$id_client."'");
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);

		$query = "INSERT INTO mail_pref_table (
			id_mail_table, reply_email, my_sign,
			timezone, rpp, refresh_time,
			change_html_tags, save_html_links,
			clean_trash, add_sign, save_to_sent,
			save_to_trash, save_only_seen) VALUES (";
		$query .= $data->id_table.", '".$login."@".$email_domain."',
			'', '+0200', 10, 10, 1, 0, 0, 0, 0, 1, 1)";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function xxx_client_change_mail_account($domain, $login, $passwd, $name, $forward) {

		$login = strtolower($login);

		$email_info = $this->client_get_mail_info($domain, $login);

		if ($email_info == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			return FALSE;
		}

		$this->SQL->connect();

		$query = "UPDATE mail_table SET ";
		$query .= "name='".$name."', ";
		$query .= "forward='".$forward."' ";
		if ($passwd != "")
			$query .= ", passwd='".$passwd."' ";

		$query .= " WHERE id_table=".$email_info->id_table;

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function xxx_client_remove_mail_account($domain, $id) {

		$email_info = $this->client_get_mail_info_2($domain, $id);

		if ($email_info == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			return FALSE;
		}

		$login = substr($email_info->login, 0, strpos($email_info->login, "@"));
		$email_domain = substr($email_info->login, strpos($email_info->login, "@")+1, strlen($email_info->login));

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->remove_mail($domain, $login.".".$email_domain);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM mail_table WHERE id_table=".$id." AND id_table='".$email_info->id_table."'");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}
		$this->SQL->disconnect();
		return TRUE;
	}

	function xxx_client_get_mail_info($domain, $login) {

		$login = strtolower($login);

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mt.id_table, mt.login, mt.name, mt.passwd, mt.quota, mt.forward
			FROM mail_table mt, client_table ct, zone_table zt
			WHERE mt.login='".$login."'
			AND mt.id_client_table=ct.id_table
			AND ct.id_zone_table=zt.id_table
			AND zt.name='".$domain."'");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function xxx_client_get_mail_info_2($domain, $id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mt.id_table, mt.login, mt.name, mt.passwd, mt.quota, mt.forward
			FROM mail_table mt, client_table ct, zone_table zt
			WHERE mt.id_table='".$id."'
			AND mt.id_client_table=ct.id_table
			AND ct.id_zone_table=zt.id_table
			AND zt.name='".$domain."'");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function xxx_client_get_mails_of_domain($domain) {

		$SQL = new PGSQL;
		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			mt.id_table, mt.login, mt.name, mt.quota, mt.forward
			FROM mail_table mt, client_table ct, zone_table zt
			WHERE zt.name='".$domain."'
			AND ct.id_zone_table=zt.id_table
			AND mt.id_client_table=ct.id_table
			ORDER BY login");

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