<?php

class Hosting_Control {

	var $server = Array();
	var $client = Array();
	var $f_res = Array();
	var $debug = FALSE;

	function Hosting_Control() {
		$this->server["ip"] = "172.16.212.200";
		$this->server["port"] = "2698";

		$this->client["ip"] = "172.16.212.200";
		$this->client["port"] = "2697";
		$this->client["sock"] = 0;
	}

	function Init() {
		$this->server["ip"] = "172.16.212.200";
		$this->server["port"] = "2698";

		$this->client["ip"] = "172.16.212.200";
		$this->client["port"] = "2697";
		$this->client["sock"] = 0;
	}

	function get_line() {
		if ($this->client["sock"] == 0)
			return "";

		return chop(fgets($this->client["sock"], 1024));
	}

	function send_command($cmd) {
		if ($this->client["sock"] == 0)
			return;

		fwrite($this->client["sock"], $cmd."\n");
	}

	function connect() {

		global $remote_server_login;

		if ($this->client["sock"] != 0) {
			$this->f_res["error"] = FALSE;
			return $this->f_res;
		}

		$this->client["sock"] = @fsockopen($this->server["ip"], $this->server["port"], $errno, $errstr, 0);

		if (!$this->client["sock"]) {
			$this->f_res["mess"] = "Server not found";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		@stream_set_timeout($this->client["sock"], 10);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			fclose($this->client["sock"]);
			$this->client["sock"] = 0;

			$this->f_res["mess"] = "Server not ready to receive commands";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("LOGIN ".$remote_server_login);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			fclose($this->client["sock"]);
			$this->client["sock"] = 0;

			$this->f_res["mess"] = "Failed to login";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function disconnect() {

		if ($this->client["sock"] == 0)
			return;

		$this->send_command("LOGOUT");
		$tmp = $this->get_line();

		fclose($this->client["sock"]);

		$this->client["sock"] = 0;

		if ($this->debug) echo "Disconnected !!!<br>";
	}

	function client_create_dir_struct($client_login) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("CLIENT CREATE ".$client_login);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not create client directory structure";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function client_rename_dir_struct($old_client_login, $client_login) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("CLIENT UPDATE ".$old_client_login." ".$client_login);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not rename client directory structure";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function client_remove_dir_struct($client_login) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("CLIENT DELETE ".$client_login);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not remove client directory structure";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function zone_create_dir_struct($client, $zone) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("ZONE CREATE ".$client." ".$zone);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not create zone directory structure";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function zone_rename_dir_struct($client, $old_zone, $zone) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("ZONE UPDATE ".$client." ".$old_zone." ".$zone);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not rename zone directory structure";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function zone_remove_dir_struct($client, $zone) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("ZONE DELETE ".$client." ".$zone);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not remove zone directory structure";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function create_system_user($login) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["result"] = "";

		$this->send_command("PWD CREATE ".$login);

		while(1) {

			$buffer = $this->get_line();

			if (ereg("^\\+OK", $buffer)) {
				break;
			}

			if (ereg("^\\-NO", $buffer) || $buffer == "") {
				$this->f_res["mess"] = "Can not create system user";
				$this->f_res["error"] = TRUE;
				return $this->f_res;
			}

			$this->f_res["result"] .= "\n".$buffer;
		}

		$this->f_res["result"] = base64_decode($this->f_res["result"]);

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function update_system_user($old_login, $new_login, $new_uid, $new_gid) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["result"] = "";

		$this->send_command("PWD UPDATE ".$old_login." ".$new_login." ".$new_uid." ".$new_gid);

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not update system user";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function remove_system_user($login) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["result"] = "";

		$this->send_command("PWD DELETE ".$login);

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not remove system user";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function create_mail($client, $zone, $login) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("MAIL CREATE ".$client." ".$zone." ".$login);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not create e-mail account";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function update_mail($client, $zone, $login, $new_client, $new_zone, $new_login) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		if ($quota == "")
			$quota = "0";

		$this->send_command("MAIL UPDATE ".$client." ".$zone." ".$login." ".$new_client." ".$new_zone." ".$new_login);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not rename mail";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function remove_mail($client, $zone, $login) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("MAIL DELETE ".$client." ".$zone." ".$login);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not remove mail";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function create_dns_zone($zone) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("DNS CREATE ZONE ".$zone);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not create dns zone";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function rename_dns_zone($zone, $new_zone) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("DNS UPDATE ZONE ".$zone." ".$new_zone);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not update dns zone";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function remove_dns_zone($zone) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("DNS DELETE ZONE ".$zone);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not remove dns zone";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function create_dns_domain($zone, $domain, $type, $prior, $record) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		if ($type == "MX")
			$this->send_command("DNS CREATE DOMAIN ".$zone." ".$domain." ".$type." ".$prior." ".$record);
		else
			$this->send_command("DNS CREATE DOMAIN ".$zone." ".$domain." ".$type." ".$record);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not create dns domain";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function rename_dns_domain($zone, $domain, $type, $prior, $new_domain, $new_type, $new_prior, $new_record) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		if ($type == "MX")
			$cmd = "DNS UPDATE DOMAIN ".$zone." ".$domain." ".$type." ".$prior;
		else
			$cmd = "DNS UPDATE DOMAIN ".$zone." ".$domain." ".$type;

		if ($new_type == "MX")
			$cmd .= " ".$new_domain." ".$new_type." ".$new_prior." ".$new_record;
		else
			$cmd .= " ".$new_domain." ".$new_type." ".$new_record;

		$this->send_command($cmd);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not update dns domain";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function remove_dns_domain($zone, $domain, $type, $prior) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		if ($type == "MX")
			$this->send_command("DNS DELETE DOMAIN ".$zone." ".$domain." ".$type." ".$prior);
		else
			$this->send_command("DNS DELETE DOMAIN ".$zone." ".$domain." ".$type);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not remove dns domain";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function info_zone_info($zone, $type) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["result"] = "";

		$this->send_command("DNS INFO ZONE_INFO ".$zone." ".$type);

		while(1) {

			$buffer = $this->get_line();

			if (ereg("^\\+OK", $buffer)) {
				break;
			}

			if (ereg("^\\-NO", $buffer) || $buffer == "") {
				$this->f_res["mess"] = "Can not fetch zone info";
				$this->f_res["error"] = TRUE;
				return $this->f_res;
			}

			$this->f_res["result"] .= "\n".$buffer;
		}

		$this->f_res["result"] = base64_decode($this->f_res["result"]);

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function info_zone($zone) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["result"] = "";

		$this->send_command("DNS INFO ZONE ".$zone);

		while(1) {

			$buffer = $this->get_line();

			if (ereg("^\\+OK", $buffer)) {
				break;
			}

			if (ereg("^\\-NO", $buffer) || $buffer == "") {
				$this->f_res["mess"] = "Can not fetch zone plain text";
				$this->f_res["error"] = TRUE;
				return $this->f_res;
			}

			$this->f_res["result"] .= "\n".$buffer;
		}

		$this->f_res["result"] = base64_decode($this->f_res["result"]);

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function create_zone_info($zone, $type, $pri, $rr) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		if ($type == "MX")
			$this->send_command("DNS CREATE ZONE_INFO ".$zone." ".$type." ".$pri." ".$rr);
		else
			$this->send_command("DNS CREATE ZONE_INFO ".$zone." ".$type." ".$rr);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not create zone info";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function update_zone_info($zone, $old_type, $old_pri, $new_type, $new_pri, $new_rr) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("DNS UPDATE ZONE_INFO ".$zone." ".$old_type." ".$old_pri." ".$new_type." ".$new_pri." ".$new_rr);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not update zone info";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function update_zone_manual($zone, $content) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("DNS UPDATE ZONE_MANUAL ".$zone." ".$content);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not update zone manual";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function remove_zone_info($zone, $type, $pri, $rr) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		if ($type == "MX")
			$this->send_command("DNS DELETE ZONE_INFO ".$zone." ".$type." ".$pri);
		else
			$this->send_command("DNS DELETE ZONE_INFO ".$zone." ".$type." ".$rr);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not remove zone info";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function protect_create_user($domain, $name, $passwd, $group_name) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		if ($group_name != "")
			$this->send_command("PRO CREATE USER ".$domain." ".$name." ".$passwd." ".$group_name);
		else
			$this->send_command("PRO CREATE USER ".$domain." ".$name." ".$passwd);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not create user";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function protect_update_user($domain, $old_name, $name, $passwd, $group_name) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		if ($group_name != "")
			$this->send_command("PRO UPDATE USER ".$domain." ".$old_name." ".$name." ".$passwd." ".$group_name);
		else
			$this->send_command("PRO UPDATE USER ".$domain." ".$old_name." ".$name." ".$passwd);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not update user";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function protect_remove_user($domain, $name) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("PRO DELETE USER ".$domain." ".$name);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not remove user";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function protect_create_group($domain, $name) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("PRO CREATE GROUP ".$domain." ".$name);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not create group";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function protect_update_group($domain, $old_name, $name) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("PRO UPDATE GROUP ".$domain." ".$old_name." ".$name);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not update group";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function protect_remove_group($domain, $name) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("PRO DELETE GROUP ".$domain." ".$name);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not remove group";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function protect_create_area($domain, $realm, $object_type, $object_path, $method_type, $method_name) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("PRO CREATE AREA ".$domain." ".$realm." ".$object_type." ".$object_path." ".$method_type." ".$method_name);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not create area";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function protect_update_area($domain, $old_object_type, $old_object_path, $realm, $object_type, $object_path, $method_type, $method_name) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("PRO UPDATE AREA ".$domain." ".$old_object_type." ".$old_object_path." ".$realm." ".$object_type." ".$object_path." ".$method_type." ".$method_name);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not update area";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function protect_remove_area($domain, $object_type, $object_path) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("PRO DELETE AREA ".$domain." ".$object_type." ".$object_path);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not update area";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function backup_restore($domain) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("BACKUP ".$domain);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not restore backup";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function service_control($action, $service) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->send_command("SERVICE ".$action." ".$service);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not complete requested action";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function fetch_ids() {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["result"] = "";

		$this->send_command("PWD INFO");

		while(1) {

			$buffer = $this->get_line();

			if (ereg("^\\+OK", $buffer)) {
				break;
			}

			if (ereg("^\\-NO", $buffer) || $buffer == "") {
				$this->f_res["mess"] = "Can not get info";
				$this->f_res["error"] = TRUE;
				return $this->f_res;
			}

			$this->f_res["result"] .= "\n".$buffer;
		}

		$this->f_res["result"] = base64_decode($this->f_res["result"]);

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function webdir_create($mode, $zone, $webdir, $cgi_perl, $ssi, $rootdir) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		if ($mode == "client")
			$this->send_command("APACHE CREATE_WEBDIR client ".$zone." ".$webdir." ".$cgi_perl." ".$ssi." ".$rootdir);
		else
			$this->send_command("APACHE CREATE_WEBDIR system ".$webdir." ".$rootdir);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not create webdir";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function webdir_update($old_mode, $old_zone, $old_webdir, $new_mode, $new_zone, $new_webdir, $new_cgi_perl, $new_ssi, $new_rootdir) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$first = "";
		$second = "";

		if ($old_mode == "client")
			$first = "APACHE UPDATE_WEBDIR client ".$old_zone." ".$old_webdir." ";
		else
			$first = "APACHE UPDATE_WEBDIR system ".$old_webdir." ";

		if ($new_mode == "client")
			$second = "client ".$new_zone." ".$new_webdir." ".$new_cgi_perl." ".$new_ssi." ".$new_rootdir;
		else
			$second = "system ".$new_webdir." ".$new_rootdir;

		$this->send_command($first.$second);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not update webdir";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

	function webdir_remove($mode, $zone, $webdir) {
		if ($this->client["sock"] == 0) {
			$this->f_res["mess"] = "Not connected to Remote Server !";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		if ($mode == "client")
			$this->send_command("APACHE DELETE_WEBDIR client ".$zone." ".$webdir);
		else
			$this->send_command("APACHE DEELTE_WEBDIR system ".$webdir);

		$buffer = $this->get_line();

		if (!ereg("^\\+OK", $buffer)) {
			$this->f_res["mess"] = "Can not remove webdir";
			$this->f_res["error"] = TRUE;
			return $this->f_res;
		}

		$this->f_res["error"] = FALSE;
		return $this->f_res;
	}

}

?>
