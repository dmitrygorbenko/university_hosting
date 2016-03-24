<?php
	include_once ($INCLUDE_PATH."define.php");

	include_once ($INCLUDE_PATH."sql_core.php");
	include_once ($INCLUDE_PATH."control.php");

class Web_Control {

	var $SQL;
	var $HC;

	var $PARENT_ZC;

	function Web_Control($parent) {

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

	function add_redirector($domain, $pointer, $title, $frameset) {

		$domain = strtolower($domain);
		$pointer = strtolower($pointer);

		if (eregi("[^a-zA-Z0-9\\._\\-]", $domain, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in domain. Allowed only A-Z, a-z, 0-9, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (eregi("[^a-zA-Z0-9\\._\\-]", $pointer, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in 'Redirect To'. Allowed only A-Z, a-z, 0-9, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		// Проверяем, есть ли такой domain ?
		// Если нет - тревога !
		$domain_find = FALSE;

		$tmp_data = $this->PARENT_ZC->fetch_zone_by_name($domain);
		if ($tmp_data != FALSE) {
			$domain_find = TRUE;
		}
		else {
			$tmp_data = $this->PARENT_ZC->fetch_our_zone_by_name($domain);
			if ($tmp_data != FALSE) {
				$domain_find = TRUE;
			}
			else {
				$tmp_data = $this->PARENT_ZC->fetch_our_zone_subdomain_by_domain($domain);
				if ($tmp_data != FALSE) {
					$domain_find = TRUE;
				}
				else {
					$tmp_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($domain);
					if ($tmp_data != FALSE) {
						$domain_find = TRUE;
					}
				}
			}
		}

		if ($domain_find == FALSE) {
			$_SESSION["adminpanel_error"] = "Where is no such domain (".$domain.") in out database !";
			return FALSE;
		}

		// Проверяем, есть ли такой redirector ?
		// Если да - тревога !
		$tmp_data = $this->fetch_redirector_by_domain($domain);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such redirect already exist !";
			return FALSE;
		}

		// Let's begin...
		$redirector_data["redirector_domain"] = $domain;
		$redirector_data["redirector_pointer"] = $pointer;
		$redirector_data["redirector_frameset"] = $frameset;
		$redirector_data["redirector_title"] = $title;
		$redirector_data["zone_id_table"] = 0;
		$redirector_data["redirector_dummy"] = true;

		// А теперь попытаемся найти зону, на котороу ссылается redirect
		$find_zone = FALSE;

		$zone_data = $this->PARENT_ZC->fetch_zone_by_name($redirector_data["redirector_domain"]);
		if ($zone_data != FALSE) {
			$find_zone = TRUE;
		}
		else {
			// ищем в поддоменах клиентов
			$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($redirector_data["redirector_domain"]);
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
			$redirector_data["zone_id_table"] = $zone_data->id_table;
			$redirector_data["redirector_dummy"] = false;
		}
/*
		echo "<pre>"; print_r($redirector_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		$query = "INSERT INTO redirect_table (".($redirector_data["zone_id_table"]!=0?"id_zone_table, ":"dummy, ")."  domain, pointer, title, frameset) VALUES (";
		if ($redirector_data["zone_id_table"] != 0)
			$query .= "".$redirector_data["zone_id_table"].", ";
		else
			$query .= "1, ";
		$query .= "'".$redirector_data["redirector_domain"]."', ";
		$query .= "'".$redirector_data["redirector_pointer"]."', ";
		$query .= "'".$redirector_data["redirector_title"]."', ";
		$query .= "'".($redirector_data["redirector_frameset"]?"1":"0")."' ";
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

	function change_redirector($id, $domain, $pointer, $title, $frameset) {

		$domain = strtolower($domain);
		$pointer = strtolower($pointer);

		if (eregi("[^a-zA-Z0-9\\._\\-]", $domain, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in domain. Allowed only A-Z, a-z, 0-9, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		if (eregi("[^a-zA-Z0-9\\._\\-]", $pointer, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in 'Redirect To'. Allowed only A-Z, a-z, 0-9, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		// Проверяем, есть ли такой domain ?
		// Если нет - тревога !
		$domain_find = FALSE;

		$tmp_data = $this->PARENT_ZC->fetch_zone_by_name($domain);
		if ($tmp_data != FALSE) {
			$domain_find = TRUE;
		}
		else {
			$tmp_data = $this->PARENT_ZC->fetch_our_zone_by_name($domain);
			if ($tmp_data != FALSE) {
				$domain_find = TRUE;
			}
			else {
				$tmp_data = $this->PARENT_ZC->fetch_our_zone_subdomain_by_domain($domain);
				if ($tmp_data != FALSE) {
					$domain_find = TRUE;
				}
				else {
					$tmp_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($domain);
					if ($tmp_data != FALSE) {
						$domain_find = TRUE;
					}
				}
			}
		}

		if ($domain_find == FALSE) {
			$_SESSION["adminpanel_error"] = "Where is no such domain (".$domain.") in out database !";
			return FALSE;
		}

		// Проверяем, есть ли такой redirector ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->fetch_redirector_by_domain($domain);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id) {
			$_SESSION["adminpanel_error"] = "Such redirector already exist !";
			return FALSE;
		}

		$redirector_data = $this->fetch_redirector($id);
		if ($redirector_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch redirector: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Let's begin...

		// OLD
		$old_redirector_data["redirector_id"] = $redirector_data->id_table;

		// NEW
		$new_redirector_data["redirector_domain"] = $domain;
		$new_redirector_data["redirector_pointer"] = $pointer;
		$new_redirector_data["redirector_frameset"] = $frameset;
		$new_redirector_data["redirector_title"] = $title;
		$new_redirector_data["zone_id_table"] = 0;
		$new_redirector_data["redirector_dummy"] = true;

		// А теперь попытаемся найти зону, на котороу ссылается domain
		$find_new_zone = FALSE;

		$zone_data = $this->PARENT_ZC->fetch_zone_by_name($new_redirector_data["redirector_domain"]);
		if ($zone_data != FALSE) {
			$find_new_zone = TRUE;
		}
		else {
			// ищем в поддоменах клиентов
			$subdomain_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($new_redirector_data["redirector_domain"]);
			if ($subdomain_data != FALSE) {
				$zone_data = $this->PARENT_ZC->fetch_zone($subdomain_data->id_zone_table);
				if ($zone_data != FALSE) {
					$find_new_zone = TRUE;
				}
				else {
					$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
					return FALSE;
				}
			}
		}

		if ($find_new_zone == TRUE) {
			$new_redirector_data["zone_id_table"] = $zone_data->id_table;
			$new_redirector_data["redirector_dummy"] = false;
		}
/*
		echo "<pre>"; print_r($new_redirector_data); echo "</pre>";
		exit;
*/
		$this->SQL->connect();

		$query = "UPDATE redirect_table SET ";
		if ($new_redirector_data["zone_id_table"] != 0)
			$query .= "id_zone_table=".$new_redirector_data["zone_id_table"].", dummy=0, ";
		else
			$query .= "id_zone_table=NULL, dummy=1, ";
		$query .= "domain='".$new_redirector_data["redirector_domain"]."', ";
		$query .= "pointer='".$new_redirector_data["redirector_pointer"]."', ";
		$query .= "title='".$new_redirector_data["redirector_title"]."', ";
		$query .= "frameset='".($new_redirector_data["redirector_frameset"]?"1":"0")."'";
		$query .= " WHERE id_table=".$old_redirector_data["redirector_id"]."";

		$result = $this->SQL->exec_query($query);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function remove_redirector($id) {

		$tmp_data = $this->fetch_redirector($id);
		if ($tmp_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch redirector: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM redirect_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function remove_redirector_by_domain($domain) {

		$redirector_data = $this->fetch_redirector_by_domain($domain);
		if ($redirector_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch redirector: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM redirect_table WHERE domain='".$domain."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function fetch_redirector($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			rt.*
			FROM redirect_table rt
			WHERE rt.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);

		$this->SQL->disconnect();

		return $data;
	}

	function fetch_redirector_by_domain($domain) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			rt.*
			FROM redirect_table rt
			WHERE rt.domain='".$domain."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);

		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_redirectors() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			rt.*
			FROM redirect_table rt
			ORDER BY rt.domain");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_alone_redirectors() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			rt.*
			FROM redirect_table rt
			WHERE rt.dummy=1
			ORDER BY rt.domain");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_redirectors_of_zone($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			rt.*
			FROM redirect_table rt
			WHERE rt.id_zone_table = ".$id_zone."
			ORDER BY rt.domain");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function xxx_fetch_all_zones_redirect($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			ft.*
			FROM redirect_table ft
			WHERE ft.id_zone_table=".$id_zone);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function get_redirectors_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(rt.id_table) AS redirector_count
			FROM redirect_table rt");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function get_alone_redirectors_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(rt.id_table) AS redirector_count
			FROM redirect_table rt
			WHERE rt.dummy=1");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

//######################################################
//		HERE GOES WEBDIR CODE
//######################################################

	function add_webdir($domain, $id_zone, $rootdir) {

		global $Clients_dir;

		$domain = strtolower($domain);

		if (eregi("[^a-zA-Z0-9\\._\\-]", $domain, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in domain. Allowed only A-Z, a-z, 0-9, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		// Let's begin...
		$webdir_data["webdir_domain"] = $domain;
		$webdir_data["webdir_full_domain"] = $domain;
		$webdir_data["webdir_rootdir"] = $rootdir;
		$webdir_data["zone_id_table"] = $id_zone;
		$webdir_data["zone_name"] = "";
		$webdir_data["zone_cgi_perl"] = "";
		$webdir_data["zone_ssi"] = "";
		$webdir_data["webdir_dummy"] = true;

		if ($webdir_data["zone_id_table"] != "none") {
			$zone_data = $this->PARENT_ZC->fetch_zone($webdir_data["zone_id_table"]);
			if ($zone_data != FALSE) {
				$find_zone = TRUE;
			}
			else {
				$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
				return FALSE;
			}

			$service = $this->PARENT_ZC->fetch_zone_service($webdir_data["zone_id_table"]);
			if ($service == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't fetch zone's service: ".$_SESSION["adminpanel_error"];
				return FALSE;
			}

			$webdir_data["webdir_dummy"] = false;
			$webdir_data["webdir_full_domain"] = $domain.".".$zone_data->name;
			$webdir_data["zone_name"] = $zone_data->name;
			$webdir_data["zone_cgi_perl"] = $service->cgi_perl=="t"?"1":"0";
			$webdir_data["zone_ssi"] = $service->ssi=="t"?"1":"0";
		}

		// Проверяем, есть ли такой webdir ?
		// Если да - тревога !
		$tmp_data = $this->fetch_webdir_by_domain($webdir_data["webdir_full_domain"]);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Such redirect already exist !";
			return FALSE;
		}

		// Проверяем, есть ли такой domain ?
		// Если нет - тревога !
		$domain_find = FALSE;

		$tmp_data = $this->PARENT_ZC->fetch_zone_by_name($webdir_data["webdir_full_domain"]);
		if ($tmp_data != FALSE) {
			$domain_find = TRUE;
		}
		else {
			$tmp_data = $this->PARENT_ZC->fetch_our_zone_by_name($webdir_data["webdir_full_domain"]);
			if ($tmp_data != FALSE) {
				$domain_find = TRUE;
			}
			else {
				$tmp_data = $this->PARENT_ZC->fetch_our_zone_subdomain_by_domain($webdir_data["webdir_full_domain"]);
				if ($tmp_data != FALSE) {
					$domain_find = TRUE;
				}
				else {
					$tmp_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($webdir_data["webdir_full_domain"]);
					if ($tmp_data != FALSE) {
						$domain_find = TRUE;
					}
				}
			}
		}

		if ($domain_find == FALSE) {
			$_SESSION["adminpanel_error"] = "Where is no such domain (".$webdir_data["webdir_full_domain"].") in out database !";
			return FALSE;
		}
/*
		echo "<pre>"; print_r($webdir_data); echo "</pre>";
		exit;
*/

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Can't connect to Remote Server: ".$result["mess"];
			return FALSE;
		}

		if ($webdir_data["zone_id_table"] != "none")
			$result = $this->HC->webdir_create("client", $webdir_data["zone_name"], $webdir_data["webdir_domain"], $webdir_data["zone_cgi_perl"], $webdir_data["zone_ssi"], $webdir_data["webdir_rootdir"]);
		else
			$result = $this->HC->webdir_create("system", $webdir_data["zone_name"], $webdir_data["webdir_domain"], $webdir_data["zone_cgi_perl"], $webdir_data["zone_ssi"], $webdir_data["webdir_rootdir"]);

		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Can't create webdir: ".$_SESSION["adminpanel_error"];
			$this->HC->disconnect();
			return FALSE;
		}
		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "INSERT INTO webdir_table (".($webdir_data["zone_id_table"]!="none"?"id_zone_table, ":"dummy, ")."  domain_lite, domain, rootdir) VALUES (";
		if ($webdir_data["zone_id_table"] != "none")
			$query .= "".$webdir_data["zone_id_table"].", ";
		else
			$query .= "1, ";
		$query .= "'".$webdir_data["webdir_domain"]."', ";
		$query .= "'".$webdir_data["webdir_full_domain"]."', ";
		$query .= "'".$webdir_data["webdir_rootdir"]."' ";
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

	function change_webdir($id, $domain, $id_zone, $rootdir) {

		global $Clients_dir;

		$domain = strtolower($domain);

		if (eregi("[^a-zA-Z0-9\\._\\-]", $domain, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in domain. Allowed only A-Z, a-z, 0-9, dot(.), \"_\" and \"-\"";
			return FALSE;
		}

		$tmp_data = $this->fetch_webdir($id);
		if ($tmp_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch old webdir !";
			return FALSE;
		}

		// Let's begin...
		$old_webdir_data["webdir_domain"] = $tmp_data->domain_lite;
		$old_webdir_data["webdir_full_domain"] = $tmp_data->domain;
		$old_webdir_data["webdir_rootdir"] = $tmp_data->rootdir;

		if ($tmp_data->id_zone_table != "") {
			$zone_data = $this->PARENT_ZC->fetch_zone($tmp_data->id_zone_table);
			if ($zone_data == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't fetch zone of old webdir !";
				return FALSE;
			}

			$old_webdir_data["zone_id_table"] = $tmp_data->id_zone_table;
			$old_webdir_data["zone_name"] = $zone_data->name;
			$old_webdir_data["webdir_dummy"] = false;
			$old_webdir_data["webdir_mode"] = "client";
		}
		else {
			$old_webdir_data["zone_id_table"] = "none";
			$old_webdir_data["zone_name"] = "";
			$old_webdir_data["webdir_dummy"] = true;
			$old_webdir_data["webdir_mode"] = "system";
		}

		// And now new...
		$new_webdir_data["webdir_domain"] = $domain;
		$new_webdir_data["webdir_full_domain"] = $domain;
		$new_webdir_data["webdir_rootdir"] = $rootdir;
		$new_webdir_data["zone_id_table"] = $id_zone;
		$new_webdir_data["zone_name"] = "";
		$new_webdir_data["zone_cgi_perl"] = "";
		$new_webdir_data["zone_ssi"] = "";
		$new_webdir_data["webdir_dummy"] = true;
		$new_webdir_data["webdir_mode"] = "system";

		if ($new_webdir_data["zone_id_table"] != "none") {
			$zone_data = $this->PARENT_ZC->fetch_zone($new_webdir_data["zone_id_table"]);
			if ($zone_data != FALSE) {
				$find_zone = TRUE;
			}
			else {
				$_SESSION["adminpanel_error"] = "Can't fetch zone (zone_id was find in subdomain): ".$_SESSION["adminpanel_error"];
				return FALSE;
			}

			$service = $this->PARENT_ZC->fetch_zone_service($new_webdir_data["zone_id_table"]);
			if ($service == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't fetch zone's service: ".$_SESSION["adminpanel_error"];
				return FALSE;
			}

			$new_webdir_data["webdir_dummy"] = false;
			$new_webdir_data["webdir_mode"] = "client";
			$new_webdir_data["webdir_full_domain"] = $domain.".".$zone_data->name;
			$new_webdir_data["zone_name"] = $zone_data->name;
			$new_webdir_data["zone_cgi_perl"] = $service->cgi_perl=="t"?"1":"0";
			$new_webdir_data["zone_ssi"] = $service->ssi=="t"?"1":"0";
		}

		// Проверяем, есть ли такой webdir ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->fetch_webdir_by_domain($new_webdir_data["webdir_full_domain"]);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id) {
			$_SESSION["adminpanel_error"] = "Such redirector already exist !";
			return FALSE;
		}

		// Проверяем, есть ли такой domain ?
		// Если нет - тревога !
		$new_domain_find = FALSE;

		$tmp_data = $this->PARENT_ZC->fetch_zone_by_name($new_webdir_data["webdir_full_domain"]);
		if ($tmp_data != FALSE) {
			$new_domain_find = TRUE;
		}
		else {
			$tmp_data = $this->PARENT_ZC->fetch_our_zone_by_name($new_webdir_data["webdir_full_domain"]);
			if ($tmp_data != FALSE) {
				$new_domain_find = TRUE;
			}
			else {
				$tmp_data = $this->PARENT_ZC->fetch_our_zone_subdomain_by_domain($new_webdir_data["webdir_full_domain"]);
				if ($tmp_data != FALSE) {
					$new_domain_find = TRUE;
				}
				else {
					$tmp_data = $this->PARENT_ZC->SDC->fetch_subdomain_by_fname($new_webdir_data["webdir_full_domain"]);
					if ($tmp_data != FALSE) {
						$new_domain_find = TRUE;
					}
				}
			}
		}

		if ($new_domain_find == FALSE) {
			$_SESSION["adminpanel_error"] = "Where is no such domain (".$new_webdir_data["webdir_full_domain"].") in out database !";
			return FALSE;
		}
/*
		echo "<BR>OLD:<BR>";
		echo "<pre>"; print_r($old_webdir_data); echo "</pre>";
		echo "<BR>NEW:<BR>";
		echo "<pre>"; print_r($new_webdir_data); echo "</pre>";
		exit;
*/
		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Can't connect to Remote Server: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->webdir_update(
			$old_webdir_data["webdir_mode"],
			$old_webdir_data["zone_name"],
			$old_webdir_data["webdir_domain"],
			$new_webdir_data["webdir_mode"],
			$new_webdir_data["zone_name"],
			$new_webdir_data["webdir_domain"],
			$new_webdir_data["zone_cgi_perl"],
			$new_webdir_data["zone_ssi"],
			$new_webdir_data["webdir_rootdir"]);

		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Can't update webdir: ".$_SESSION["adminpanel_error"];
			$this->HC->disconnect();
			return FALSE;
		}
		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "UPDATE webdir_table SET ";
		if ($new_webdir_data["zone_id_table"] != "none")
			$query .= "id_zone_table=".$new_webdir_data["zone_id_table"].", dummy=0, ";
		else
			$query .= "id_zone_table=NULL, dummy=1, ";
		$query .= "domain_lite='".$new_webdir_data["webdir_domain"]."', ";
		$query .= "domain='".$new_webdir_data["webdir_full_domain"]."', ";
		$query .= "rootdir='".$new_webdir_data["webdir_rootdir"]."' ";
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

	function remove_webdir($id) {

		$this->SQL->connect();

		$webdir_data = $this->fetch_webdir($id);
		if ($webdir_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch webdir: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Can't connect to Remote Server: ".$result["mess"];
			return FALSE;
		}

		if ($webdir_data->id_zone_table != "") {

			$zone_data = $this->PARENT_ZC->fetch_zone($webdir_data->id_zone_table);
			if ($zone_data == FALSE) {
				$_SESSION["adminpanel_error"] = "Can't fetch zone: ".$_SESSION["adminpanel_error"];
				return FALSE;
			}

			$result = $this->HC->webdir_remove("client", $zone_data->name, $webdir_data->domain_lite);
		}
		else
			$result = $this->HC->webdir_remove("system", "", $webdir_data->domain_lite);

		if ($result["error"] == TRUE) {
			$_SESSION["adminpanel_error"] = "Can't create webdir: ".$_SESSION["adminpanel_error"];
			$this->HC->disconnect();
			return FALSE;
		}
		$this->HC->disconnect();

		$result = $this->SQL->exec_query("DELETE FROM webdir_table WHERE id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function remove_webdir_by_domain($domain) {

		$webdir_data = $this->fetch_webdir_by_domain($domain);
		if ($webdir_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch webdir: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		$this->SQL->connect();

		$result = $this->SQL->exec_query("DELETE FROM webdir_table WHERE domain='".$domain."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function fetch_webdir($id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			wt.*
			FROM webdir_table wt
			WHERE wt.id_table=".$id);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);

		$this->SQL->disconnect();

		return $data;
	}

	function fetch_webdir_by_domain($domain) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			wt.*
			FROM webdir_table wt
			WHERE wt.domain='".$domain."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);

		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_webdirs() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			wt.*
			FROM webdir_table wt
			ORDER BY wt.domain");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_alone_webdirs() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			wt.*
			FROM webdir_table wt
			WHERE wt.dummy=1
			ORDER BY wt.domain");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_all_webdirs_of_zone($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			wt.*
			FROM webdir_table wt
			WHERE wt.id_zone_table = ".$id_zone."
			ORDER BY wt.domain");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function get_webdirs_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(wt.id_table) AS webdir_count
			FROM webdir_table wt");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function get_alone_webdirs_count() {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			COUNT(wt.id_table) AS webdir_count
			FROM webdir_table wt
			WHERE wt.dummy=1");

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