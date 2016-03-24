<?php

	include_once ($INCLUDE_PATH."define.php");

	include_once ($INCLUDE_PATH."sql_core.php");
	include_once ($INCLUDE_PATH."control.php");

class SubDomain_Control {

	var $SQL;
	var $HC;

	var $PARENT_ZC;

	function SubDomain_Control($parent) {

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

	// if we update zone manually
	function add_subdomain_in_db($id_zone, $domain, $type, $prior, $record) {

		$domain = strtolower($domain);
		$record = strtolower($record);

		if (eregi("[^a-zA-Z0-9\\.]", $domain, $regs)) {
			$_SESSION["adminpanel_error"] = "Restricted symbols in domain. Allowed only A-Z, a-z, 0-9 and dot(.)";
			return FALSE;
		}

		$this->SQL->connect();

		$zone_data = $this->PARENT_ZC->fetch_zone($id_zone);
		if ($zone_data == FALSE) {
			$_SESSION["adminpanel_error"] = "Can't fetch zone: ".$_SESSION["adminpanel_error"];
			return FALSE;
		}

		// Проверяем, есть ли такой redirect ?
		$tmp_data = $this->PARENT_ZC->WC->fetch_redirector_by_domain($domain.".".$zone_data->name);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Subdomain '".$domain.".".$zone_data->name."': Such redirect already exist !";
			return FALSE;
		}

		// Проверяем, есть ли такой webdir ?
		$tmp_data = $this->PARENT_ZC->WC->fetch_webdir_by_domain($domain.".".$zone_data->name);
		if ($tmp_data != FALSE) {
			$_SESSION["adminpanel_error"] = "Subdomain '".$domain.".".$zone_data->name."': Such webdir already exist !";
			return FALSE;
		}

		$result = $this->SQL->exec_query("INSERT INTO
			subdomain_table (id_zone_table, f_name, name, type,".($type=="MX"?" prior,":"")." record)
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

	function change_subdomain_manually($zone_name, $zone_content) {

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
	function remove_all_subdomains_of_zone_in_db($id_zone) {

		$this->SQL->connect();

		// first, remove all redirectors and webdirs what refer to this domain
		$subdomains = $this->fetch_all_subdomains_of_zone($id_zone);
		if ($subdomains == FALSE) {
			echo "Can't fetch all subdomains of zone: ".$_SESSION["adminpanel_error"];
			$this->SQL->disconnect();
			return FALSE;
		}

		for ($i = 0; $i < $this->SQL->get_num_rows($subdomains); $i++) {
			$data = $this->SQL->get_object($subdomains);

			$result = $this->PARENT_ZC->WC->remove_redirector_by_domain($data->f_name);
			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] .= "Can't remove redirector: ";
				return FALSE;
			}

			$result = $this->PARENT_ZC->WC->remove_webdir_by_domain($data->f_name);
			if ($result == FALSE) {
				$_SESSION["adminpanel_error"] .= "Can't remove webdir: ";
				return FALSE;
			}
		}

		// now, flush table
		$result = $this->SQL->exec_query("DELETE FROM subdomain_table WHERE id_zone_table=".$id_zone);

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return TRUE;
	}

	function create_domain_record($id_zone, $zone_name, $domain, $type, $prior, $record) {

		$zone_name = strtolower($zone_name);
		$domain = strtolower($domain);
		$record = strtolower($record);

		// Есть ли  уже такой домен ?
		$tmp_data = $this->fetch_detailed_subdomain($id_zone, $domain, $type, $prior, $record);
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

		$query = "INSERT INTO subdomain_table (id_zone_table, f_name, name, type, prior, record) VALUES (";
		$query .= "".$id_zone.", ";
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

	function update_domain_record($id_zone, $zone_name, $id_domain, $old_domain, $old_type, $old_prior, $domain, $type, $prior, $record) {

		$zone_name = strtolower($zone_name);
		$old_domain = strtolower($old_domain);
		$domain = strtolower($domain);
		$record = strtolower($record);

		// Есть ли  уже такой домен ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->fetch_detailed_subdomain($id_zone, $domain, $type, $prior, $record);
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

		$query = "UPDATE subdomain_table SET ";
		$query .= "id_zone_table=".$id_zone.", ";
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

		$result = $this->PARENT_ZC->WC->remove_redirector_by_domain($zone_name.".".$domain);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] .= "Can't remove redirector: ";
			return FALSE;
		}

		$result = $this->PARENT_ZC->WC->remove_webdir_by_domain($zone_name.".".$domain);
		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] .= "Can't remove webdir: ";
			return FALSE;
		}

		$this->SQL->connect();

		$query = "DELETE FROM subdomain_table ";
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

	function fetch_detailed_subdomain($id_zone, $domain, $type, $prior, $record) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			sdt.*
			FROM subdomain_table sdt
			WHERE sdt.id_zone_table = ".$id_zone."
			AND sdt.name='".$domain."'
			AND sdt.type='".$type."'
			AND sdt.prior=".$prior."
			AND sdt.record='".$record."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_subdomain_by_fname($fname) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT sdt.*
			FROM subdomain_table sdt
			WHERE sdt.f_name='".$fname."'");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function fetch_all_subdomains_of_zone($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			sdt.*
			FROM subdomain_table sdt
			WHERE sdt.id_zone_table = ".$id_zone."
			ORDER BY f_name");

		if ($result == FALSE) {
			$_SESSION["adminpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function fetch_zone_info($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.name
			FROM zone_table zt
			WHERE zt.id_table = ".$id_zone);

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

	function fetch_zone_plain_text($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.name
			FROM zone_table zt
			WHERE zt.id_table = ".$id_zone);

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

// ***********************************************************************
// ***********************************************************************
// ***********************************************************************
// ***********************************************************************
/*
	function xxx_client_fetch_zone_info($id_client) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			zt.name
			FROM zone_table zt
			WHERE zt.id_table = ".$id_zone);

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		$data = $this->SQL->get_object($result);

		$this->HC->connect();
		$result2 = $this->HC->info_zone_info($data->name, "MX");
		$this->HC->disconnect();

		if ($result2["error"]) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result2["mess"];
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


	function xxx_client_change_zone_mx($domain, $old_pri, $new_pri, $new_rr) {
		$this->HC->connect();
		$result = $this->HC->update_zone_info($domain, "MX", $old_pri, "MX", $new_pri, $new_rr);
		$this->HC->disconnect();

		if ($result["error"]) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		return TRUE;
	}

	function xxx_client_create_zone_mx($domain, $new_pri, $new_rr) {
		$this->HC->connect();
		$result = $this->HC->create_zone_info($domain, "MX", $new_pri, $new_rr);
		$this->HC->disconnect();

		if ($result["error"]) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		return TRUE;
	}

	function xxx_client_remove_zone_mx($domain, $pri) {
		$this->HC->connect();
		$result = $this->HC->remove_zone_info($domain, "MX", $pri, "");
		$this->HC->disconnect();

		if ($result["error"]) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		return TRUE;
	}

	function xxx_client_create_subdomain($id_client, $zone, $new_domain, $new_type, $new_prior, $new_record) {

		// Есть ли  уже такой домен ?
		$tmp_data = $this->client_fetch_subdomain_by_name($id_client, $new_domain);
		if ($tmp_data != FALSE) {
			$_SESSION["clientpanel_error"] = "Такой домен уже существует";
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->create_dns_domain($zone, $new_domain, $new_type, $new_prior, $new_record);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "INSERT INTO subdomain_table (id_client_table, f_name, name, type, prior, record) VALUES (";
		$query .= "".$id_client.", ";
		$query .= "'".$new_domain.".".$zone."', ";
		$query .= "'".$new_domain."', ";
		$query .= "'".$new_type."', ";
		$query .= "".$new_prior.", ";
		$query .= "'".$new_record."' ";
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

	function xxx_client_change_subdomain($id_client, $zone, $id_subdomain, $old_domain, $old_type, $old_prior, $new_domain, $new_type, $new_prior, $new_record) {

		// Есть ли  уже такой домен ?
		// Если есть и он не старый - тревога !
		$tmp_data = $this->client_fetch_subdomain_by_name($id_client, $new_domain);
		if ($tmp_data != FALSE && $tmp_data->id_table != $id_subdomain && $tmp_data->type == $new_type) {
			$_SESSION["clientpanel_error"] = "Такой домен уже существует";
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->rename_dns_domain($zone, $old_domain, $old_type, $old_prior, $new_domain, $new_type, $new_prior, $new_record);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "UPDATE subdomain_table SET ";
		$query .= "id_client_table=".$id_client.", ";
		$query .= "f_name='".$new_domain.".".$zone."', ";
		$query .= "name='".$new_domain."', ";
		$query .= "type='".$new_type."', ";
		$query .= "prior=".$new_prior.", ";
		$query .= "record='".$new_record."' ";
		$query .= " WHERE id_table=".$id_subdomain."";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function xxx_client_remove_subdomain($id_client, $zone, $id_subdomain, $old_domain, $old_type, $old_prior) {

		// Делаем выборку данных
		$subdomain_data = $this->client_fetch_subdomain_by_id($id_client, $id_subdomain);
		if ($subdomain_data == FALSE) {
			$_SESSION["clientpanel_error"] = "Не могу выбрать данные: ".$_SESSION["clientpanel_error"];
			return FALSE;
		}

		$result = $this->HC->connect();
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			return FALSE;
		}

		$result = $this->HC->remove_dns_domain($zone, $old_domain, $old_type, $old_prior);
		if ($result["error"] == TRUE) {
			$_SESSION["clientpanel_error"] = "Remote control: ".$result["mess"];
			$this->HC->disconnect();
			return FALSE;
		}

		$this->HC->disconnect();

		$this->SQL->connect();

		$query = "DELETE FROM subdomain_table WHERE ";
		$query .= "id_client_table=".$id_client." ";
		$query .= "AND id_table=".$id_subdomain." ";

		$result = $this->SQL->exec_query($query);
		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();
		return TRUE;
	}

	function client_fetch_all_subdomains($id_zone) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			st.*
			FROM subdomain_table st
			WHERE st.id_zone_table = ".$id_zone."
			ORDER BY name, f_name");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$this->SQL->disconnect();

		return $result;
	}

	function xxx_client_fetch_subdomain_by_name($id_client, $name) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			st.id_table, st.name, st.f_name, st.homedir, st.type, st.prior, st.record
			FROM subdomain_table st
			WHERE st.id_client_table = ".$id_client."
			AND st.name='".$name."'
			ORDER BY name, f_name");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}

	function xxx_client_fetch_subdomain_by_id($id_client, $id) {

		$this->SQL->connect();

		$result = $this->SQL->exec_query("SELECT
			st.id_table, st.name, st.f_name, st.homedir, st.type, st.prior, st.record
			FROM subdomain_table st
			WHERE st.id_client_table = ".$id_client."
			AND st.id_table='".$id."'
			ORDER BY name, f_name");

		if ($result == FALSE) {
			$_SESSION["clientpanel_error"] = $this->SQL->get_error();
			$this->SQL->disconnect();
			return FALSE;
		}

		$data = $this->SQL->get_object($result);
		$this->SQL->disconnect();

		return $data;
	}
*/
}

?>